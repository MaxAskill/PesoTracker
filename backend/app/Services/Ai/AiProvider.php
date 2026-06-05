<?php

namespace App\Services\Ai;

interface AiProvider
{
    public function reply(string $message, array $financeContext): string;
}
