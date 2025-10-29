<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationsControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userA = User::factory()->create(['balance' => 100.00]);
        $this->userB = User::factory()->create(['balance' => 50.00]);
    }

    /** @test */
    public function it_can_deposit_money()
    {
        $response = $this->postJson('/api/deposit', [
            'user_id' => $this->userA->id,
            'type' => 'deposit',
            'amount' => 25.50,
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'success']);

        $this->assertDatabaseHas('users', [
            'id' => $this->userA->id,
            'balance' => 25.50,
        ]);
    }

    /** @test */
    public function it_can_withdraw_money()
    {
        $response = $this->postJson('/api/withdraw', [
            'user_id' => $this->userA->id,
            'type' => 'withdraw',
            'amount' => 30.00,
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'success']);

        $this->assertDatabaseHas('users', [
            'id' => $this->userA->id,
            'balance' => 70.00, // 100 - 30
        ]);
    }

    /** @test */
    public function it_fails_with_insufficient_balance()
    {
        $response = $this->postJson('/api/withdraw', [
            'user_id' => $this->userB->id,
            'type' => 'withdraw',
            'amount' => 100.00,
        ]);

        $response->assertStatus(409)
            ->assertJson(['error' => 'Operation denied. Insufficient balance.']);
    }

    /** @test */
    public function it_can_transfer_money_between_users()
    {
        $response = $this->postJson('/api/transfer', [
            'from_user_id' => $this->userA->id,
            'to_user_id' => $this->userB->id,
            'amount' => 40.00,
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'success']);

        $this->assertDatabaseHas('users', [
            'id' => $this->userA->id,
            'balance' => 60.00,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->userB->id,
            'balance' => 40.00,
        ]);
    }

    /** @test */
    public function it_fails_transfer_if_sender_has_insufficient_balance()
    {
        $response = $this->postJson('api/transfer', [
            'from_user_id' => $this->userB->id,
            'to_user_id' => $this->userA->id,
            'amount' => 100.00,
        ]);

        $response->assertStatus(409)
            ->assertJson(['error' => 'Operation denied. Insufficient balance.']);

        $this->assertDatabaseHas('users', ['id' => $this->userB->id, 'balance' => 50.00]);
        $this->assertDatabaseHas('users', ['id' => $this->userA->id, 'balance' => 100.00]);
    }

    /** @test */
    public function it_returns_balance_of_user()
    {
        $response = $this->getJson("api/balance/{$this->userA->id}");
        $response->assertStatus(200)
            ->assertSee('100');
    }
}
