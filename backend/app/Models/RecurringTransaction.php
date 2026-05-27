<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecurringTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'type',
        'category',
        'amount',
        'frequency',
        'next_due_date',
        'auto_create',
        'note',
    ];
}
