<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\Budget;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $transactions = Transaction::where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json($transactions);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0.01',
            'category' => 'required|string|max:255',
            'transaction_date' => 'required|date',
            'note' => 'nullable|string',
        ]);

        $transaction = Transaction::create([
            'user_id' => $request->user()->id,
            ...$validated,
        ]);

        Notification::create([
            'user_id' => $request->user()->id,
            'title' => 'Transaction Added',
            'message' => "Your {$transaction->type} transaction {$transaction->title} was recorded.",
            'type' => 'success',
        ]);

        if ($transaction->type === 'expense') {
            $month = date('Y-m', strtotime($transaction->transaction_date));
        
            $budget = Budget::where('user_id', $request->user()->id)
                ->where('category', $transaction->category)
                ->where('month', $month)
                ->first();
        
            if ($budget) {
                $spent = Transaction::where('user_id', $request->user()->id)
                    ->where('type', 'expense')
                    ->where('category', $transaction->category)
                    ->whereRaw("DATE_FORMAT(transaction_date, '%Y-%m') = ?", [$month])
                    ->sum('amount');
        
                $usage = round(($spent / $budget->amount) * 100);
        
                if ($usage >= 100) {
                    Notification::create([
                        'user_id' => $request->user()->id,
                        'title' => 'Budget Exceeded',
                        'message' => "You exceeded your {$transaction->category} budget for {$month}.",
                        'type' => 'danger',
                    ]);
                } elseif ($usage >= 80) {
                    Notification::create([
                        'user_id' => $request->user()->id,
                        'title' => 'Budget Warning',
                        'message' => "You have used {$usage}% of your {$transaction->category} budget for {$month}.",
                        'type' => 'warning',
                    ]);
                }
            }
        }
        
        return response()->json([
            'message' => 'Transaction added successfully',
            'transaction' => $transaction,
        ], 201);
    }

    public function show(Request $request, Transaction $transaction)
    {
        if ($transaction->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($transaction);
    }

    public function update(Request $request, Transaction $transaction)
    {
        if ($transaction->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|in:income,expense',
            'amount' => 'sometimes|required|numeric|min:0.01',
            'category' => 'sometimes|required|string|max:255',
            'transaction_date' => 'sometimes|required|date',
            'note' => 'nullable|string',
        ]);

        $transaction->update($validated);

        return response()->json([
            'message' => 'Transaction updated successfully',
            'transaction' => $transaction,
        ]);
    }

    public function destroy(Request $request, Transaction $transaction)
    {
        if ($transaction->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $transaction->delete();

        return response()->json([
            'message' => 'Transaction deleted successfully',
        ]);
    }
}