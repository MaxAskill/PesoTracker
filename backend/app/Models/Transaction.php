<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'user_transactions';

    protected $fillable = [
    'user_id',
    'title',
    'type',
    'amount',
    'category',
    'transaction_date',
    'note',
];
}
