<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AiAssistantPlaceholderTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_assistant_placeholder_requires_authentication(): void
    {
        $this->postJson('/api/ai/assistant', [
            'message' => 'Summarize my spending.',
        ])->assertUnauthorized();
    }

    public function test_ai_assistant_placeholder_returns_unavailable_message(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/ai/assistant', [
            'message' => 'Summarize my spending.',
        ])
            ->assertOk()
            ->assertJsonPath('message', 'AI Assistant is not available yet.');
    }

    public function test_ai_assistant_placeholder_is_rate_limited(): void
    {
        Sanctum::actingAs(User::factory()->create());

        for ($index = 1; $index <= 10; $index++) {
            $this->postJson('/api/ai/assistant', [
                'message' => "Request {$index}",
            ])->assertOk();
        }

        $this->postJson('/api/ai/assistant', [
            'message' => 'Request 11',
        ])
            ->assertStatus(429)
            ->assertJsonPath('message', 'Too many AI Assistant requests. Please wait before trying again.');
    }
}
