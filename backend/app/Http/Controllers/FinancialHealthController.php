<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FinancialHealthService;

class FinancialHealthController extends Controller
{
    public function score(Request $request, FinancialHealthService $financialHealthService)
    {
        return response()->json(
            $financialHealthService->calculate(
                $request->user()->id,
                $request->query('month')
            )
        );
    }
}
