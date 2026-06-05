<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TransactionSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_transaction_payload_is_rejected(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $payload = $this->transactionPayload();

        $this->postJson('/api/transactions', $payload)
            ->assertCreated();

        $this->postJson('/api/transactions', $payload)
            ->assertStatus(409)
            ->assertJsonPath('message', 'This transaction looks like a duplicate and was not saved again.');

        $this->assertSame(1, Transaction::where('user_id', $user->id)->count());
    }

    public function test_idempotency_key_replay_returns_existing_transaction(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $key = 'test-idempotency-key';

        $firstResponse = $this->postJson('/api/transactions', $this->transactionPayload(), [
            'Idempotency-Key' => $key,
        ])->assertCreated();

        $transactionId = $firstResponse->json('transaction.id');

        $this->postJson('/api/transactions', $this->transactionPayload(['amount' => 999]), [
            'Idempotency-Key' => $key,
        ])
            ->assertOk()
            ->assertJsonPath('transaction.id', $transactionId)
            ->assertJsonPath('message', 'Transaction already processed.');

        $this->assertSame(1, Transaction::where('user_id', $user->id)->count());
    }

    public function test_transaction_creation_is_rate_limited(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        for ($index = 1; $index <= 10; $index++) {
            $this->postJson('/api/transactions', $this->transactionPayload([
                'title' => "Coffee {$index}",
                'amount' => 100 + $index,
                'note' => "Rate limit test {$index}",
            ]))->assertCreated();
        }

        $this->postJson('/api/transactions', $this->transactionPayload([
            'title' => 'Coffee 11',
            'amount' => 111,
            'note' => 'Rate limit test 11',
        ]))
            ->assertStatus(429)
            ->assertJsonPath('message', 'Too many transaction requests. Please wait before adding more transactions.');
    }

    private function transactionPayload(array $overrides = []): array
    {
        return [
            'title' => 'Coffee',
            'type' => 'expense',
            'amount' => 120.50,
            'category' => 'Food',
            'transaction_date' => '2026-06-06',
            'note' => 'Morning coffee',
            ...$overrides,
        ];
    }
}
