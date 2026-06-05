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

        return [
            'period' => $now->format('F Y'),
            'summary' => [
                'total_income' => round($totalIncome, 2),
                'total_expenses' => round($totalExpenses, 2),
                'balance' => round($totalIncome - $totalExpenses, 2),
                'transaction_count_this_month' => $monthlyTransactions->count(),
            ],
            'savings_score' => $this->savingsScore($userId, $month),
            'top_expense_categories' => $categorySpending
                ->map(fn (float $amount, string $category) => [
                    'category' => $category,
                    'amount' => $amount,
                ])
                ->values(),
            'budgets' => $budgets,
            'savings_goals' => $goals,
        ];
    }

    private function savingsGoalsSummary(int $userId): Collection
    {
        try {
            $table = (new SavingsGoal())->getTable();
            $nameColumn = $this->firstExistingColumn($table, ['title', 'name', 'goal_name']);

            if (! $nameColumn || ! Schema::hasColumn($table, 'target_amount') || ! Schema::hasColumn($table, 'saved_amount')) {
                Log::warning('AI savings goals summary skipped because expected columns are missing.', [
                    'table' => $table,
                    'name_column' => $nameColumn,
                    'has_target_amount' => Schema::hasColumn($table, 'target_amount'),
                    'has_saved_amount' => Schema::hasColumn($table, 'saved_amount'),
                ]);

                return collect();
            }

            $columns = [$nameColumn, 'target_amount', 'saved_amount'];

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
                ->map(function (SavingsGoal $goal) use ($nameColumn) {
                    $target = (float) $goal->target_amount;
                    $saved = (float) $goal->saved_amount;

                    return [
                        'title' => $goal->{$nameColumn},
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

    private function firstExistingColumn(string $table, array $columns): ?string
    {
        foreach ($columns as $column) {
            if (Schema::hasColumn($table, $column)) {
                return $column;
            }
        }

        return null;
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
