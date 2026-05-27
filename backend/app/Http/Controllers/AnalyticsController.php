<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function summary(Request $request)
    {
        $userId = $request->user()->id;

        $expenseByCategory = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        $monthlySummary = Transaction::where('user_id', $userId)
            ->select(
                DB::raw("DATE_FORMAT(transaction_date, '%Y-%m') as month"),
                'type',
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('month', 'type')
            ->orderBy('month')
            ->get();

        $highestExpenseCategory = $expenseByCategory->first();

        return response()->json([
            'expense_by_category' => $expenseByCategory,
            'monthly_summary' => $monthlySummary,
            'highest_expense_category' => $highestExpenseCategory,
        ]);
    }
}