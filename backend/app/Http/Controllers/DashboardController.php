<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\FinancialHealthService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, FinancialHealthService $financialHealthService)
    {
        $userId = $request->user()->id;
        $financialHealth = $financialHealthService->calculate($userId, $request->query('month'));

        $totalIncome = Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->sum('amount');

        $totalExpenses = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->sum('amount');

        $balance = $totalIncome - $totalExpenses;

        $recentTransactions = Transaction::where('user_id', $userId)
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'total_income' => $totalIncome,
            'total_expenses' => $totalExpenses,
            'balance' => $balance,
            'savings_score' => $financialHealth['savings_score'],
            'savings_status' => $financialHealth['savings_status'],
            'score_breakdown' => $financialHealth['score_breakdown'],
            'financial_health' => $financialHealth,
            'recent_transactions' => $recentTransactions,
        ]);
    }
}
