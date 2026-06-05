<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('transactions:process-recurring')
    ->daily();

Schedule::command('users:delete-expired-unverified')
    ->daily();
