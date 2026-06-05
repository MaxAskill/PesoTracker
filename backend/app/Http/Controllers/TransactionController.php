<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Support\DateExpressions;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\Budget;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;

class TransactionController extends Controller
{
    private const DUPLICATE_WINDOW_SECONDS = 30;

    public function index(Request $request)
    {
        $transactions = Transaction::where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json($transactions);
    }

    public function store(Request $request)
    {
        $userId = $request->user()->id;
        $idempotencyKey = $request->header('Idempotency-Key');

        if ($idempotencyKey !== null) {
            $idempotencyKey = trim($idempotencyKey);

            if (strlen($idempotencyKey) > 255) {
                return response()->json([
                    'message' => 'The idempotency key must not be greater than 255 characters.',
                ], 422);
            }

            $existingTransaction = Transaction::where('user_id', $userId)
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existingTransaction) {
                return response()->json([
                    'message' => 'Transaction already processed.',
                    'transaction' => $existingTransaction,
                ]);
            }
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0.01',
            'category' => 'required|string|max:255',
            'transaction_date' => 'required|date',
            'note' => 'nullable|string',
        ]);

        $duplicate = $this->findRecentDuplicate($userId, $validated);

        if ($duplicate) {
            return response()->json([
                'message' => 'This transaction looks like a duplicate and was not saved again.',
                'transaction' => $duplicate,
            ], 409);
        }

        try {
            $transaction = Transaction::create([
                'user_id' => $userId,
                'idempotency_key' => $idempotencyKey ?: null,
                ...$validated,
            ]);
        } catch (QueryException $exception) {
            if (!$idempotencyKey) {
                throw $exception;
            }

            $existingTransaction = Transaction::where('user_id', $userId)
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if (!$existingTransaction) {
                throw $exception;
            }

            return response()->json([
                'message' => 'Transaction already processed.',
                'transaction' => $existingTransaction,
            ]);
        }

        Notification::create([
            'user_id' => $userId,
            'title' => 'Transaction Added',
            'message' => "Your {$transaction->type} transaction {$transaction->title} was recorded.",
            'type' => 'success',
        ]);

        if ($transaction->type === 'expense') {
            $month = date('Y-m', strtotime($transaction->transaction_date));
        
            $budget = Budget::where('user_id', $userId)
                ->where('category', $transaction->category)
                ->where('month', $month)
                ->first();
        
            if ($budget) {
                $spent = Transaction::where('user_id', $userId)
                    ->where('type', 'expense')
                    ->where('category', $transaction->category)
                    ->whereRaw(DateExpressions::yearMonth('transaction_date').' = ?', [$month])
                    ->sum('amount');
        
                $usage = round(($spent / $budget->amount) * 100);
        
                if ($usage >= 100) {
                    Notification::create([
                        'user_id' => $userId,
                        'title' => 'Budget Exceeded',
                        'message' => "You exceeded your {$transaction->category} budget for {$month}.",
                        'type' => 'danger',
                    ]);
                } elseif ($usage >= 80) {
                    Notification::create([
                        'user_id' => $userId,
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

    private function findRecentDuplicate(int $userId, array $validated): ?Transaction
    {
        $description = trim((string) ($validated['note'] ?? ''));

        if ($description === '') {
            $description = trim((string) $validated['title']);
        }

        return Transaction::where('user_id', $userId)
            ->where('amount', number_format((float) $validated['amount'], 2, '.', ''))
            ->where('type', $validated['type'])
            ->where('category', $validated['category'])
            ->whereDate('transaction_date', Carbon::parse($validated['transaction_date'])->toDateString())
            ->whereRaw("COALESCE(NULLIF(note, ''), title) = ?", [$description])
            ->where('created_at', '>=', now()->subSeconds(self::DUPLICATE_WINDOW_SECONDS))
            ->latest()
            ->first();
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
