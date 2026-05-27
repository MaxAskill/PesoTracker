<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Budget;
use App\Models\SavingsGoal;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinanceAssistantController extends Controller
{
    public function ask(Request $request)
    {
        $request->validate([
            'message' => 'required|string'
        ]);

        $message = strtolower($request->message);

        $userId = $request->user()->id;

        $reply = "I'm not sure how to answer that yet.";

        // FOOD SPENDING
        if (
            str_contains($message, 'food') &&
            str_contains($message, 'spend')
        ) {

            $amount = Transaction::where('user_id', $userId)
                ->where('type', 'expense')
                ->where('category', 'Food')
                ->whereMonth(
                    'transaction_date',
                    Carbon::now()->month
                )
                ->sum('amount');

            $reply = "You spent ₱" . number_format($amount, 2) . " on Food this month.";
        }

        // BIGGEST EXPENSE CATEGORY
        elseif (
            str_contains($message, 'biggest') ||
            str_contains($message, 'highest')
        ) {

            $category = Transaction::where('user_id', $userId)
                ->where('type', 'expense')
                ->select(
                    'category',
                    DB::raw('SUM(amount) as total')
                )
                ->groupBy('category')
                ->orderByDesc('total')
                ->first();

            if ($category) {

                $reply =
                    "Your biggest expense category is {$category->category} with ₱" .
                    number_format($category->total, 2) . ".";
            }
        }

        // SAVINGS
        elseif (
            str_contains($message, 'save') ||
            str_contains($message, 'savings')
        ) {

            $saved = SavingsGoal::where('user_id', $userId)
                ->sum('saved_amount');

            $reply =
                "You currently saved ₱" .
                number_format($saved, 2) .
                " across your savings goals.";
        }

        // BUDGET
        elseif (
            str_contains($message, 'budget')
        ) {

            $budgets = Budget::where('user_id', $userId)->count();

            $reply =
                "You currently have {$budgets} active budgets.";
        }

        // TOTAL EXPENSES
        elseif (
            str_contains($message, 'expenses') ||
            str_contains($message, 'spent')
        ) {

            $expenses = Transaction::where('user_id', $userId)
                ->where('type', 'expense')
                ->sum('amount');

            $reply =
                "Your total recorded expenses are ₱" .
                number_format($expenses, 2) . ".";
        }

        return response()->json([
            'reply' => $reply
        ]);
    }
}