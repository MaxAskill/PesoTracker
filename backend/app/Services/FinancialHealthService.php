<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\SavingsGoal;
use App\Models\Transaction;
use App\Support\DateExpressions;
use Carbon\Carbon;

class FinancialHealthService
{
    public function calculate(int $userId, ?string $month = null): array
    {
        $month = $this->normalizeMonth($month);

        $totalIncome = (float) Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->whereRaw(DateExpressions::yearMonth('transaction_date') . ' = ?', [$month])
            ->sum('amount');

        $totalExpenses = (float) Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereRaw(DateExpressions::yearMonth('transaction_date') . ' = ?', [$month])
            ->sum('amount');

        $balance = $totalIncome - $totalExpenses;

        $savingsRateScore = $this->savingsRateScore($totalIncome, $balance);
        $budgetDisciplineScore = $this->budgetDisciplineScore($userId, $month);
        $savingsGoalProgressScore = $this->savingsGoalProgressScore($userId);
        $positiveBalanceScore = $this->positiveBalanceScore($balance);

        $score = round(
            ($savingsRateScore * 0.40)
            + ($budgetDisciplineScore * 0.30)
            + ($savingsGoalProgressScore * 0.20)
            + ($positiveBalanceScore * 0.10)
        );

        $score = $this->clamp($score);
        $status = $this->statusForScore($score);

        return [
            'score' => $score,
            'status' => $status,
            'recommendation' => $this->recommendationForStatus($status),
            'savings_score' => $score,
            'savings_status' => $status,
            'score_breakdown' => [
                'savings_rate_score' => $savingsRateScore,
                'budget_discipline_score' => $budgetDisciplineScore,
                'savings_goal_progress_score' => $savingsGoalProgressScore,
                'positive_balance_score' => $positiveBalanceScore,
                'weighted' => [
                    'savings_rate' => round($savingsRateScore * 0.40, 1),
                    'budget_discipline' => round($budgetDisciplineScore * 0.30, 1),
                    'savings_goal_progress' => round($savingsGoalProgressScore * 0.20, 1),
                    'positive_balance' => round($positiveBalanceScore * 0.10, 1),
                ],
            ],
            'month' => $month,
            'income' => $totalIncome,
            'expenses' => $totalExpenses,
            'balance' => $balance,
        ];
    }

    private function normalizeMonth(?string $month): string
    {
        if ($month && preg_match('/^\d{4}-\d{2}$/', $month)) {
            return $month;
        }

        return Carbon::now()->format('Y-m');
    }

    private function savingsRateScore(float $income, float $balance): int
    {
        if ($income <= 0) {
            return 0;
        }

        $savingsRate = $balance / $income;

        if ($savingsRate >= 0.30) {
            return 100;
        }

        if ($savingsRate <= 0) {
            return 0;
        }

        return $this->clamp(round(($savingsRate / 0.30) * 100));
    }

    private function budgetDisciplineScore(int $userId, string $month): int
    {
        $budgets = Budget::where('user_id', $userId)
            ->where('month', $month)
            ->get();

        if ($budgets->isEmpty()) {
            return 50;
        }

        $withinBudgetCount = $budgets->filter(function (Budget $budget) use ($userId, $month) {
            $spent = (float) Transaction::where('user_id', $userId)
                ->where('type', 'expense')
                ->where('category', $budget->category)
                ->whereRaw(DateExpressions::yearMonth('transaction_date') . ' = ?', [$month])
                ->sum('amount');

            return $spent <= (float) $budget->amount;
        })->count();

        return $this->clamp(round(($withinBudgetCount / $budgets->count()) * 100));
    }

    private function savingsGoalProgressScore(int $userId): int
    {
        $goals = SavingsGoal::where('user_id', $userId)
            ->where('status', 'active')
            ->where('target_amount', '>', 0)
            ->get();

        if ($goals->isEmpty()) {
            return 50;
        }

        $averageProgress = $goals->avg(function (SavingsGoal $goal) {
            return min(100, ((float) $goal->saved_amount / (float) $goal->target_amount) * 100);
        });

        return $this->clamp(round($averageProgress));
    }

    private function positiveBalanceScore(float $balance): int
    {
        if ($balance > 0) {
            return 100;
        }

        if ($balance == 0.0) {
            return 50;
        }

        return 0;
    }

    private function statusForScore(int $score): string
    {
        if ($score >= 90) {
            return 'Excellent';
        }

        if ($score >= 75) {
            return 'Healthy';
        }

        if ($score >= 50) {
            return 'Needs Attention';
        }

        if ($score >= 25) {
            return 'At Risk';
        }

        return 'Critical';
    }

    private function recommendationForStatus(string $status): string
    {
        return match ($status) {
            'Excellent' => 'Your finances are in excellent shape. Keep maintaining your spending discipline.',
            'Healthy' => 'Your finances are healthy. Keep tracking your expenses and savings goals.',
            'Needs Attention' => 'Your finances need attention. Review spending and budget limits.',
            'At Risk' => 'You may be overspending. Try reducing expenses or adjusting your budget.',
            default => 'Your expenses are putting pressure on your finances. Review your spending immediately.',
        };
    }

    private function clamp(float|int $value): int
    {
        return (int) max(0, min(100, $value));
    }
}
