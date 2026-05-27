<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Budget;
use App\Models\SavingsGoal;

class FinancialHealthController extends Controller
{
    public function score(Request $request)
    {
        $userId = $request->user()->id;

        $income = Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->sum('amount');

        $expenses = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->sum('amount');

        $score = 100;

        // Expense ratio
        if ($income > 0) {
            $expenseRatio = ($expenses / $income) * 100;

            if ($expenseRatio >= 90) {
                $score -= 40;
            } elseif ($expenseRatio >= 75) {
                $score -= 25;
            } elseif ($expenseRatio >= 60) {
                $score -= 10;
            }
        }

        // Savings goals
        $goals = SavingsGoal::where('user_id', $userId)->get();

        foreach ($goals as $goal) {

            if ($goal->target_amount > 0) {

                $progress = ($goal->saved_amount / $goal->target_amount) * 100;

                if ($progress >= 100) {
                    $score += 10;
                } elseif ($progress >= 50) {
                    $score += 5;
                }
            }
        }

        // Budgets
        $budgets = Budget::where('user_id', $userId)->get();

        foreach ($budgets as $budget) {

            $spent = Transaction::where('user_id', $userId)
                ->where('type', 'expense')
                ->where('category', $budget->category)
                ->sum('amount');

            if ($budget->amount > 0) {

                $usage = ($spent / $budget->amount) * 100;

                if ($usage >= 100) {
                    $score -= 15;
                } elseif ($usage >= 80) {
                    $score -= 8;
                }
            }
        }

        // Clamp
        $score = max(0, min(100, round($score)));

        // Status label
        $status = 'Poor';

        if ($score >= 80) {
            $status = 'Excellent';
        } elseif ($score >= 60) {
            $status = 'Good';
        } elseif ($score >= 40) {
            $status = 'Fair';
        }

        // Recommendation
        $recommendation = match ($status) {
            'Excellent' => 'Your finances are very healthy. Keep maintaining your spending discipline.',
            'Good' => 'You are doing well financially. Continue improving your savings habits.',
            'Fair' => 'Your finances are stable but there is room for improvement.',
            default => 'Your expenses are too high compared to your income. Focus on budgeting and savings.',
        };

        return response()->json([
            'score' => $score,
            'status' => $status,
            'recommendation' => $recommendation,
            'income' => $income,
            'expenses' => $expenses,
        ]);
    }
}