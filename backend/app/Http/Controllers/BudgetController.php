<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        return Budget::where('user_id', $request->user()->id)
            ->latest()
            ->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'month' => 'required|string',
        ]);

        $budget = Budget::create([
            'user_id' => $request->user()->id,
            'category' => $request->category,
            'amount' => $request->amount,
            'month' => $request->month,
        ]);

        return response()->json($budget, 201);
    }

    public function show(string $id)
    {
        return Budget::findOrFail($id);
    }

    public function update(Request $request, string $id)
    {
        $budget = Budget::findOrFail($id);

        $budget->update($request->only([
            'category',
            'amount',
            'month'
        ]));

        return response()->json($budget);
    }

    public function destroy(string $id)
    {
        $budget = Budget::findOrFail($id);

        $budget->delete();

        return response()->json([
            'message' => 'Budget deleted successfully.'
        ]);
    }
}