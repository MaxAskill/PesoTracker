<?php

namespace App\Http\Controllers;

use App\Services\ReceiptScannerService;
use Illuminate\Http\Request;

class ReceiptScannerController extends Controller
{
    public function scan(Request $request, ReceiptScannerService $scanner)
    {
        $validated = $request->validate([
            'receipt' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        return response()->json(
            $scanner->createDraft(
                $request->user()->id,
                $validated['receipt']
            )
        );
    }
}
