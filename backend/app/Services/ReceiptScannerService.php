<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ReceiptScannerService
{
    public function createDraft(int $userId, UploadedFile $receipt): array
    {
        $path = $receipt->store("receipts/{$userId}", 'public');

        return [
            'success' => true,
            'message' => 'Receipt image received. Please review the transaction details before saving.',
            'draft' => [
                'type' => 'expense',
                'amount' => null,
                'category' => null,
                'merchant' => null,
                'date' => Carbon::now()->toDateString(),
                'notes' => 'Created from receipt scanner',
                'receipt_image_url' => Storage::url($path),
            ],
        ];
    }

    // Future OCR can parse the stored image and merge extracted values into this draft.
    private function extractReceiptFields(string $storedPath): array
    {
        return [];
    }
}
