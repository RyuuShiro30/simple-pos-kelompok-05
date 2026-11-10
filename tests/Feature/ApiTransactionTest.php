<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiTransactionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Membuat user kasir untuk pengujian.
     */
    private function createCashier(): User
    {
        return User::factory()->create([
            'role' => 'kasir',
            'is_active' => true,
        ]);
    }

    /**
     * Membuat produk untuk pengujian.
     */
    private function createProduct(int $price = 15000, int $stock = 10): int
    {
        $now = now();

        $categoryId = DB::table('categories')->insertGetId([
            'name' => 'Kategori Test',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return DB::table('products')->insertGetId([
            'category_id' => $categoryId,
            'name' => 'Produk Test',
            'price' => $price,
            'stock' => $stock,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /**
     * 1. Guest tidak boleh membuat transaksi.
     */
    public function test_guest_cannot_create_transaction(): void
    {
        $response = $this->postJson('/api/transactions', [
            'items' => [],
        ], [
            'Idempotency-Key' => 'guest-test-key',
        ]);

        $response->assertUnauthorized();
    }

    /**
     * 2. Token tanpa ability transactions:create ditolak.
     */
    public function test_token_without_create_ability_cannot_create_transaction(): void
    {
        $user = $this->createCashier();

        Sanctum::actingAs($user, ['transactions:read']);

        $response = $this->postJson('/api/transactions', [
            'items' => [],
        ], [
            'Idempotency-Key' => 'wrong-ability-key',
        ]);

        $response->assertForbidden();
    }

    /**
     * 3. Product ID yang tidak valid menghasilkan 422.
     */
    public function test_invalid_product_id_returns_validation_error(): void
    {
        $user = $this->createCashier();

        Sanctum::actingAs($user, ['transactions:create']);

        $response = $this->postJson('/api/transactions', [
            'items' => [
                [
                    'product_id' => 999999,
                    'qty' => 2,
                ],
            ],
        ], [
            'Idempotency-Key' => 'invalid-product-key',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'items.0.product_id',
            ]);

        $this->assertDatabaseCount('transactions', 0);
    }

    /**
     * 4. Idempotency mencegah transaksi dibuat dua kali.
     */
    public function test_same_idempotency_key_only_creates_one_transaction(): void
    {
        $user = $this->createCashier();
        $productId = $this->createProduct();

        Sanctum::actingAs($user, ['transactions:create']);

        $payload = [
            'items' => [
                [
                    'product_id' => $productId,
                    'qty' => 2,
                ],
            ],
        ];

        $headers = [
            'Idempotency-Key' => 'transaction-test-key',
        ];

        $firstResponse = $this->postJson(
            '/api/transactions',
            $payload,
            $headers
        );

        $firstResponse->assertCreated()
            ->assertJsonPath('data.total', 30000);

        $secondResponse = $this->postJson(
            '/api/transactions',
            $payload,
            $headers
        );

        $secondResponse->assertCreated()
            ->assertHeader('Idempotent-Replayed', 'true')
            ->assertJsonPath('data.total', 30000);

        $this->assertDatabaseCount('transactions', 1);
        $this->assertDatabaseCount('transaction_details', 1);
    }

    /**
     * 5. Kasir hanya boleh melihat transaksi miliknya sendiri.
     */
    public function test_cashier_cannot_view_another_cashiers_transaction(): void
    {
        $owner = $this->createCashier();
        $otherCashier = $this->createCashier();

        $now = now();

        $transactionId = DB::table('transactions')->insertGetId([
            'user_id' => $owner->id,
            'total' => 30000,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        Sanctum::actingAs($otherCashier, ['transactions:read']);

        $response = $this->getJson(
            "/api/transactions/{$transactionId}"
        );

        $response->assertForbidden();

        Sanctum::actingAs($owner, ['transactions:read']);

        $ownerResponse = $this->getJson(
            "/api/transactions/{$transactionId}"
        );

        $ownerResponse->assertOk();
    }
}
