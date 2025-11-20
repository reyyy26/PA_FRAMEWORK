<?php

namespace Tests\Feature\Analytics;

use App\Models\Branch;
use App\Models\PosSale;
use App\Models\PosSaleItem;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AnalyticsExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    public function test_sales_mix_export_streams_csv_with_product_data(): void
    {
        $branch = Branch::create([
            'code' => 'MAIN',
            'name' => 'Main Branch',
            'type' => 'main',
        ]);

        $user = User::create([
            'name' => 'Analytics User',
            'email' => 'analytics@example.com',
            'password' => 'secret-123',
            'is_super_admin' => true,
            'default_branch_id' => $branch->id,
        ]);

        $user->branches()->syncWithoutDetaching([$branch->id => ['role' => 'manager']]);

        $this->actingAs($user);

        $unit = ProductUnit::create([
            'name' => 'Unit',
            'symbol' => 'U',
        ]);

        $product = Product::create([
            'sku' => 'SKU-' . Str::random(5),
            'name' => 'Test Product',
            'product_unit_id' => $unit->id,
            'default_price' => 50000,
        ]);

        $sale = PosSale::create([
            'number' => 'POS-' . Str::random(8),
            'branch_id' => $branch->id,
            'cashier_id' => $user->id,
            'subtotal' => 150000,
            'discount_total' => 0,
            'tax_total' => 0,
            'grand_total' => 150000,
            'status' => 'completed',
            'sold_at' => now(),
        ]);

        PosSaleItem::create([
            'pos_sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'unit_price' => 50000,
            'discount' => 0,
            'total' => 150000,
        ]);

        $response = $this->get('/api/analytics/sales-mix/export?branch_id=' . $branch->id);

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $csv = $response->streamedContent();
        $this->assertStringContainsString('product_id', $csv);
        $this->assertStringContainsString((string) $product->id, $csv);
        $this->assertStringContainsString('mix_percentage', $csv);
    }

}
