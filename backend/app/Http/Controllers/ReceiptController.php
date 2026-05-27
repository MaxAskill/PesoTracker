<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use thiagoalessio\TesseractOCR\TesseractOCR;

class ReceiptController extends Controller
{
    public function scan(Request $request)
    {
        $request->validate([
            'receipt' => 'required|image|max:5120'
        ]);
    
        $image = $request->file('receipt');
        $path = $image->store('receipts', 'public');
        $fullPath = storage_path('app/public/' . $path);
    
        $text = (new TesseractOCR($fullPath))
            ->executable(env('TESSERACT_PATH'))
            ->run();
    
        $lines = array_values(array_filter(
            array_map('trim', explode("\n", $text))
        ));
    
        $storeName = $lines[0] ?? null;
    
        $category = 'Shopping';
        
        $foodKeywords = [
            'JOLLIBEE',
            'MCDO',
            'MCDONALD',
            'KFC',
            'CHOWKING',
            'GREENWICH',
            'MANG INASAL',
            'STARBUCKS',
            'YUMBURGER',
            'BURGER',
            'SPAG',
            'DINE-IN',
        ];
        
        $upperText = strtoupper($text);
        
        foreach ($foodKeywords as $keyword) {
            if (str_contains($upperText, $keyword)) {
                $category = 'Food';
                break;
            }
        }

        $amount = null;
        
        // PRIORITY: TOTAL DUE
        if (preg_match('/TOTAL DUE\s+(\d+\.\d{2})/i', $text, $matches)) {
        
            $amount = $matches[1];
        
        }
        // FALLBACK: TOTAL
        elseif (preg_match('/TOTAL\s+(\d+\.\d{2})/i', $text, $matches)) {
        
            $amount = $matches[1];
        
        }
        // FALLBACK: largest reasonable amount
        else {
        
            preg_match_all('/\d+\.\d{2}/', $text, $amountMatches);
        
            $amounts = array_map('floatval', $amountMatches[0] ?? []);
        
            $filtered = array_filter($amounts, function ($value) {
                return $value < 10000;
            });
        
            if (!empty($filtered)) {
                $amount = max($filtered);
            }
        }
    
        preg_match('/\d{2}[\/\-]\d{2}[\/\-]\d{4}/', $text, $dateMatch);
        $date = $dateMatch[0] ?? null;
    
        return response()->json([
            'text' => $text,
            'store_name' => $storeName,
            'amount' => $amount,
            'date' => $date,
            'category' => $category,
            'image_path' => asset('storage/' . $path)
        ]);
    }
}