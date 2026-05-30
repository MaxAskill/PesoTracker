<?php

namespace App\Http\Controllers;

use App\Services\AssistantInsightService;
use Illuminate\Http\Request;

class AssistantController extends Controller
{
    public function insights(Request $request, AssistantInsightService $assistant)
    {
        return response()->json(
            $assistant->insights($request->user()->id)
        );
    }

    public function ask(Request $request, AssistantInsightService $assistant)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        return response()->json(
            $assistant->ask($request->user()->id, $validated['message'])
        );
    }
}
