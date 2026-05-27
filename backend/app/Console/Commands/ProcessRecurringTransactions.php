<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use App\Models\Notification;
use Carbon\Carbon;

class ProcessRecurringTransactions extends Command
{
    protected $signature = 'transactions:process-recurring';

    protected $description = 'Process recurring transactions automatically';

    public function handle()
    {
        $today = Carbon::today();

        $transactions = RecurringTransaction::where('auto_create', true)
            ->whereDate('next_due_date', '<=', $today)
            ->get();

        foreach ($transactions as $recurring) {

            Transaction::create([
                'user_id' => $recurring->user_id,
                'title' => $recurring->title,
                'type' => $recurring->type,
                'category' => $recurring->category,
                'amount' => $recurring->amount,
                'transaction_date' => $today,
                'note' => $recurring->note,
            ]);

            Notification::create([
                'user_id' => $recurring->user_id,
                'title' => 'Recurring Transaction Processed',
                'message' => "{$recurring->title} was automatically added.",
                'type' => 'info',
            ]);

            $nextDate = Carbon::parse($recurring->next_due_date);

            switch ($recurring->frequency) {

                case 'daily':
                    $nextDate->addDay();
                    break;

                case 'weekly':
                    $nextDate->addWeek();
                    break;

                case 'monthly':
                    $nextDate->addMonth();
                    break;

                case 'yearly':
                    $nextDate->addYear();
                    break;
            }

            $recurring->update([
                'next_due_date' => $nextDate
            ]);
        }

        $this->info('Recurring transactions processed successfully.');
    }
}