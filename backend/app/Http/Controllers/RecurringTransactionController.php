<?php

namespace App\Http\Controllers;

use App\Models\RecurringTransaction;
use Illuminate\Http\Request;

class RecurringTransactionController extends Controller
{
    public function index(Request $request)
    {
        return RecurringTransaction::where('user_id', $request->user()->id)
            ->latest()
            ->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'type' => 'required|in:income,expense',
            'category' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'frequency' => 'required|in:daily,weekly,monthly,yearly',
            'next_due_date' => 'required|date',
        ]);

        $transaction = RecurringTransaction::create([
            'user_id' => $request->user()->id,
            'title' => $request->title,
            'type' => $request->type,
            'category' => $request->category,
            'amount' => $request->amount,
            'frequency' => $request->frequency,
            'next_due_date' => $request->next_due_date,
            'auto_create' => true,
            'note' => $request->note,
        ]);

        return response()->json($transaction, 201);
    }

    public function show(string $id)
    {
        return RecurringTransaction::findOrFail($id);
    }

    public function update(Request $request, string $id)
    {
        $transaction = RecurringTransaction::findOrFail($id);

        $transaction->update($request->only([
            'title',
            'type',
            'category',
            'amount',
            'frequency',
            'next_due_date',
            'auto_create',
            'note',
        ]));

        return response()->json($transaction);
    }

    public function destroy(string $id)
    {
        $transaction = RecurringTransaction::findOrFail($id);

        $transaction->delete();

        return response()->json([
            'message' => 'Recurring transaction deleted successfully.'
        ]);
    }
}