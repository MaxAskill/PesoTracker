<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Budget;
use App\Models\SavingsGoal;
use App\Support\DateExpressions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InsightController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $currentMonth = Carbon::now()->format('Y-m');

        $insights = [];

        $highestCategory = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->first();

        if ($highestCategory) {
            $insights[] = [
                'title' => 'Highest Spending Category',
                'message' => "Your highest spending category is {$highestCategory->category} with ₱{$highestCategory->total}.",
                'type' => 'warning',
            ];
        }

        $totalIncome = Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->sum('amount');

        $totalExpenses = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->sum('amount');

        if ($totalIncome > 0) {
            $expenseRate = round(($totalExpenses / $totalIncome) * 100);

            $insights[] = [
                'title' => 'Spending Rate',
                'message' => "You have spent {$expenseRate}% of your total recorded income.",
                'type' => $expenseRate >= 80 ? 'danger' : 'success',
            ];
        }

        $budgets = Budget::where('user_id', $userId)
            ->where('month', $currentMonth)
            ->get();

        foreach ($budgets as $budget) {
            $spent = Transaction::where('user_id', $userId)
                ->where('type', 'expense')
                ->where('category', $budget->category)
                ->whereRaw(DateExpressions::yearMonth('transaction_date').' = ?', [$budget->month])
                ->sum('amount');

            if ($budget->amount > 0) {
                $usage = round(($spent / $budget->amount) * 100);

                if ($usage >= 80) {
                    $insights[] = [
                        'title' => 'Budget Warning',
                        'message' => "You have used {$usage}% of your {$budget->category} budget for {$budget->month}.",
                        'type' => $usage >= 100 ? 'danger' : 'warning',
                    ];
                }
            }
        }

        $goals = SavingsGoal::where('user_id', $userId)->get();

        foreach ($goals as $goal) {
            if ($goal->target_amount > 0) {
                $progress = round(($goal->saved_amount / $goal->target_amount) * 100);

                if ($progress >= 100) {
                    $insights[] = [
                        'title' => 'Goal Completed',
                        'message' => "Congratulations! You completed your {$goal->title} savings goal.",
                        'type' => 'success',
                    ];
                } elseif ($progress >= 50) {
                    $insights[] = [
                        'title' => 'Savings Progress',
                        'message' => "You are {$progress}% done with your {$goal->title} goal.",
                        'type' => 'success',
                    ];
                }
            }
        }

        if (empty($insights)) {
            $insights[] = [
                'title' => 'No Insights Yet',
                'message' => 'Add more transactions, budgets, and savings goals to generate insights.',
                'type' => 'neutral',
            ];
        }

        return response()->json($insights);
    }
}
