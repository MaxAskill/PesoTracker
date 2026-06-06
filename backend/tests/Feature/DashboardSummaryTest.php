<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_summary_totals_are_scoped_and_case_insensitive(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        Sanctum::actingAs($user);

        DB::statement('PRAGMA ignore_check_constraints = ON');

        DB::table('user_transactions')->insert($this->transactionPayload($user->id, [
            'type' => 'Income',
            'amount' => 1000,
            'title' => 'Salary',
        ]));

        DB::table('user_transactions')->insert($this->transactionPayload($user->id, [
            'type' => 'expense',
            'amount' => 250,
            'title' => 'Food',
        ]));

        DB::table('user_transactions')->insert($this->transactionPayload($otherUser->id, [
            'type' => 'income',
            'amount' => 9999,
            'title' => 'Other user salary',
        ]));

        $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('total_income', 1000)
            ->assertJsonPath('income', 1000)
            ->assertJsonPath('total_expenses', 250)
            ->assertJsonPath('expenses', 250)
            ->assertJsonPath('balance', 750);
    }

    private function transactionPayload(int $userId, array $overrides = []): array
    {
        return [
            'user_id' => $userId,
            'title' => 'Transaction',
            'type' => 'expense',
            'amount' => 100,
            'category' => 'Food',
            'transaction_date' => now()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
            ...$overrides,
        ];
    }
}
