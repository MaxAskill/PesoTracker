<?php

namespace App\Http\Controllers;

use App\Models\SavingsGoal;
use Illuminate\Http\Request;

class SavingsGoalController extends Controller
{
    public function index(Request $request)
    {
        return SavingsGoal::where('user_id', $request->user()->id)
            ->latest()
            ->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'target_amount' => 'required|numeric|min:1',
            'deadline' => 'required|date',
        ]);

        $goal = SavingsGoal::create([
            'user_id' => $request->user()->id,
            'title' => $request->title,
            'description' => $request->description,
            'target_amount' => $request->target_amount,
            'saved_amount' => 0,
            'deadline' => $request->deadline,
            'status' => 'active',
        ]);

        return response()->json($goal, 201);
    }

    public function show(string $id)
    {
        return SavingsGoal::findOrFail($id);
    }

    public function update(Request $request, string $id)
    {
        $goal = SavingsGoal::findOrFail($id);

        $goal->update($request->only([
            'title',
            'description',
            'target_amount',
            'saved_amount',
            'deadline',
            'status'
        ]));

        return response()->json($goal);
    }

    public function destroy(string $id)
    {
        $goal = SavingsGoal::findOrFail($id);

        $goal->delete();

        return response()->json([
            'message' => 'Savings goal deleted successfully.'
        ]);
    }
}