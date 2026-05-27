<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavingsGoal extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'target_amount',
        'saved_amount',
        'deadline',
        'status',
    ];
}
