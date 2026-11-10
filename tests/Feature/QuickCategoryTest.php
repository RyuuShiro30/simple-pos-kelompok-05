<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuickCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_quick_categories_page(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)
            ->get('/quick-categories');

        $response->assertOk()
            ->assertViewIs('categories.quick');
    }

    public function test_admin_can_create_category_using_ajax(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)
            ->postJson('/quick-categories', [
                'name' => 'Minuman',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Minuman');

        $this->assertDatabaseHas('categories', [
            'name' => 'Minuman',
        ]);
    }

    public function test_empty_category_name_is_rejected(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)
            ->postJson('/quick-categories', [
                'name' => '',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('name');
    }

    public function test_duplicate_category_name_is_rejected(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        Category::create([
            'name' => 'Makanan',
        ]);

        $response = $this->actingAs($admin)
            ->postJson('/quick-categories', [
                'name' => 'Makanan',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('name');
    }

    public function test_guest_cannot_create_category(): void
    {
        $response = $this->postJson('/quick-categories', [
            'name' => 'Makanan',
        ]);

        $response->assertUnauthorized();
    }

    public function test_non_admin_cannot_access_quick_categories(): void
    {
        $cashier = User::factory()->create([
            'role' => 'kasir',
            'is_active' => true,
        ]);

        $response = $this->actingAs($cashier)
            ->get('/quick-categories');

        $response->assertForbidden();
    }
}
