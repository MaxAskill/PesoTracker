<?php

namespace App\Services\Ai;

use App\Models\Budget;
use App\Models\SavingsGoal;
use App\Models\Transaction;
use App\Services\FinancialHealthService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class AiFinanceContextService
{
    public function __construct(private FinancialHealthService $financialHealth)
    {
    }

    public function forUser(int $userId): array
    {
        $now = Carbon::now();
        $month = $now->format('Y-m');
        $startOfMonth = $now->copy()->startOfMonth()->toDateString();
        $endOfMonth = $now->copy()->endOfMonth()->toDateString();

        $monthlyTransactions = Transaction::query()
            ->where('user_id', $userId)
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->select(['type', 'category', 'amount'])
            ->get();

        $totalIncome = (float) $monthlyTransactions
            ->where('type', 'income')
            ->sum('amount');

        $totalExpenses = (float) $monthlyTransactions
            ->where('type', 'expense')
            ->sum('amount');

        $categorySpending = $monthlyTransactions
            ->where('type', 'expense')
            ->groupBy('category')
            ->map(fn (Collection $transactions) => round((float) $transactions->sum('amount'), 2))
            ->sortDesc()
            ->take(8);

        $budgets = Budget::query()
            ->where('user_id', $userId)
            ->where('month', $month)
            ->select(['category', 'amount'])
            ->get()
            ->map(function (Budget $budget) use ($categorySpending) {
                $spent = (float) ($categorySpending[$budget->category] ?? 0);
                $amount = (float) $budget->amount;

                return [
                    'category' => $budget->category,
                    'budgeted' => $amount,
                    'spent' => $spent,
                    'remaining' => round($amount - $spent, 2),
                    'is_over_budget' => $spent > $amount,
                ];
            })
            ->values();

        $goals = $this->savingsGoalsSummary($userId);
        $summary = [
            'total_income' => round($totalIncome, 2),
            'total_expenses' => round($totalExpenses, 2),
            'balance' => round($totalIncome - $totalExpenses, 2),
            'transaction_count_this_month' => $monthlyTransactions->count(),
        ];
        $topExpenseCategories = $categorySpending
            ->map(fn (float $amount, string $category) => [
                'category' => $category,
                'amount' => $amount,
            ])
            ->values();

        return [
            'current_month' => $now->format('F Y'),
            'period' => $now->format('F Y'),
            'income' => $summary['total_income'],
            'expenses' => $summary['total_expenses'],
            'balance' => $summary['balance'],
            'summary' => $summary,
            'savings_score' => $this->savingsScore($userId, $month),
            'top_categories' => $topExpenseCategories,
            'top_expense_categories' => $topExpenseCategories,
            'budget_status' => $this->budgetStatus($budgets),
            'budgets' => $budgets,
            'savings_progress' => $this->savingsProgress($goals),
            'savings_goals' => $goals,
        ];
    }

    private function budgetStatus(Collection $budgets): array
    {
        $overBudget = $budgets
            ->filter(fn (array $budget) => $budget['is_over_budget'])
            ->values();

        return [
            'has_budgets' => $budgets->isNotEmpty(),
            'over_budget_count' => $overBudget->count(),
            'over_budget_categories' => $overBudget
                ->map(fn (array $budget) => [
                    'category' => $budget['category'],
                    'budgeted' => $budget['budgeted'],
                    'spent' => $budget['spent'],
                    'over_by' => round(abs($budget['remaining']), 2),
                ])
                ->values(),
        ];
    }

    private function savingsProgress(Collection $goals): array
    {
        return [
            'has_savings_goals' => $goals->isNotEmpty(),
            'goals' => $goals
                ->map(fn (array $goal) => [
                    'title' => $goal['title'],
                    'target_amount' => $goal['target_amount'],
                    'saved_amount' => $goal['saved_amount'],
                    'progress_percent' => $goal['progress_percent'],
                ])
                ->values(),
        ];
    }

    private function savingsGoalsSummary(int $userId): Collection
    {
        try {
            $table = (new SavingsGoal())->getTable();
            $nameColumns = $this->existingColumns($table, ['title', 'name', 'goal_name', 'goal_title', 'goal']);

            if (! Schema::hasColumn($table, 'target_amount') || ! Schema::hasColumn($table, 'saved_amount')) {
                Log::warning('AI savings goals summary skipped because expected columns are missing.', [
                    'table' => $table,
                    'name_columns' => $nameColumns,
                    'has_target_amount' => Schema::hasColumn($table, 'target_amount'),
                    'has_saved_amount' => Schema::hasColumn($table, 'saved_amount'),
                ]);

                return collect();
            }

            $columns = array_values(array_unique([...$nameColumns, 'target_amount', 'saved_amount']));

            if (Schema::hasColumn($table, 'created_at')) {
                $columns[] = 'created_at';
            }

            $query = SavingsGoal::query()
                ->where('user_id', $userId)
                ->select($columns);

            if (Schema::hasColumn($table, 'status')) {
                $query->where('status', 'active');
            }

            if (Schema::hasColumn($table, 'created_at')) {
                $query->latest();
            }

            return $query
                ->limit(5)
                ->get()
                ->map(function (SavingsGoal $goal, int $index) use ($nameColumns) {
                    $target = (float) $goal->target_amount;
                    $saved = (float) $goal->saved_amount;
                    $title = $this->goalTitle($goal, $nameColumns, $index);

                    return [
                        'title' => $title,
                        'target_amount' => $target,
                        'saved_amount' => $saved,
                        'progress_percent' => $target > 0 ? min(round(($saved / $target) * 100), 100) : 0,
                    ];
                })
                ->values();
        } catch (Throwable $error) {
            Log::warning('AI savings goals summary failed and was skipped.', [
                'message' => $error->getMessage(),
                'file' => $error->getFile(),
                'line' => $error->getLine(),
                'user_id' => $userId,
            ]);

            return collect();
        }
    }

    private function existingColumns(string $table, array $columns): array
    {
        return array_values(array_filter(
            $columns,
            fn (string $column) => Schema::hasColumn($table, $column)
        ));
    }

    private function goalTitle(SavingsGoal $goal, array $nameColumns, int $index): string
    {
        foreach ($nameColumns as $column) {
            if (filled($goal->{$column})) {
                return $goal->{$column};
            }
        }

        return 'Savings Goal '.($index + 1);
    }

    private function savingsScore(int $userId, string $month): array
    {
        $health = $this->financialHealth->calculate($userId, $month);

        return [
            'score' => $health['score'],
            'status' => $health['status'],
            'recommendation' => $health['recommendation'],
        ];
    }
}
