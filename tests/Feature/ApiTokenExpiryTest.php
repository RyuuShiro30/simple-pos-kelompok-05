<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ApiTokenExpiryTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_token_that_has_not_expired_can_access_api(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('mobile-kasir', ['products:read'], now()->addDay())->plainTextToken;

        Carbon::setTestNow(now()->addHours(12));

        $this->withToken($token)
            ->getJson('/api/products')
            ->assertOk();
    }

    public function test_expired_token_is_rejected_with_401(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('mobile-kasir', ['products:read'], now()->addDay())->plainTextToken;

        Carbon::setTestNow(now()->addDays(2));

        $this->withToken($token)
            ->getJson('/api/products')
            ->assertUnauthorized();
    }
}