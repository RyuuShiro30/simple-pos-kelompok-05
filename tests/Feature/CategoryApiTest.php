<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_category_ability_can_get_categories(): void
    {
        $user = User::factory()->create();

        Category::forceCreate(['name' => 'Elektronik']);
        Category::forceCreate(['name' => 'Makanan']);

        Sanctum::actingAs($user, ['categories:read']);

        $response = $this->getJson('/api/categories');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'Elektronik')
            ->assertJsonPath('data.1.name', 'Makanan');
    }

    public function test_guest_cannot_get_categories(): void
    {
        $response = $this->getJson('/api/categories');

        $response->assertUnauthorized();
    }

    public function test_user_without_category_ability_cannot_get_categories(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['products:read']);

        $response = $this->getJson('/api/categories');

        $response->assertForbidden();
    }
}
