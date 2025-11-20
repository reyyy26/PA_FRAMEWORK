<?php

namespace Tests\Feature\Inventory;

use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\RestockTemplateItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RestockTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_add_product_to_template(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'secret-123',
            'is_super_admin' => true,
        ]);

        $product = $this->createProduct('SKU-100', 'Benih Uji');

        $response = $this->actingAs($admin)->postJsonWithCsrf('/api/restock/templates', [
            'product_id' => $product->id,
            'default_quantity' => 12,
        ]);

        $response->assertCreated()
            ->assertJsonPath('product.id', $product->id)
            ->assertJsonPath('default_quantity', 12);

        $this->assertDatabaseHas('restock_template_items', [
            'product_id' => $product->id,
            'default_quantity' => 12,
            'is_active' => true,
        ]);
    }

    public function test_non_admin_user_receives_active_template_options(): void
    {
        $user = User::create([
            'name' => 'Branch User',
            'email' => 'user@example.com',
            'password' => 'secret-123',
            'is_super_admin' => false,
        ]);

        $activeItem = RestockTemplateItem::create([
            'product_id' => $this->createProduct('SKU-101', 'Pupuk Uji')->id,
            'default_quantity' => 10,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $inactiveItem = RestockTemplateItem::create([
            'product_id' => $this->createProduct('SKU-102', 'Herbisida Uji')->id,
            'default_quantity' => 5,
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $inactiveItem->update(['is_active' => false]);

        $response = $this->actingAs($user)->getJson('/api/restock/options');

        $response->assertOk();

        $payload = $response->json();
        $this->assertIsArray($payload);
        $this->assertNotEmpty($payload);

        collect($payload)->each(function (array $item) {
            $this->assertArrayHasKey('product', $item);
            $this->assertArrayHasKey('default_quantity', $item);
            $this->assertTrue($item['is_active']);
        });

        $this->assertFalse(
            collect($payload)->contains(fn ($item) => $item['id'] === $inactiveItem->id)
        );
    }

    private function createProduct(string $sku, string $name): Product
    {
        $unit = ProductUnit::firstOrCreate(['name' => 'Unit Uji'], ['symbol' => 'u']);

        return Product::create([
            'sku' => $sku,
            'name' => $name,
            'product_unit_id' => $unit->id,
            'category' => 'Uji',
            'default_cost' => 10000,
            'default_price' => 15000,
            'track_batch' => true,
            'is_active' => true,
        ]);
    }
}
