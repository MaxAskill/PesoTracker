<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function transactionsCsv(Request $request)
    {
        $transactions = Transaction::where('user_id', $request->user()->id)
            ->latest()
            ->get();

        $filename = 'pesotracker_transactions.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($transactions) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Title',
                'Type',
                'Category',
                'Amount',
                'Date',
                'Note',
            ]);

            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->title,
                    $transaction->type,
                    $transaction->category,
                    $transaction->amount,
                    $transaction->transaction_date,
                    $transaction->note,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}