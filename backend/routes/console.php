<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('transactions:process-recurring')
    ->daily();
