<?php

namespace Tests\Feature\Automation;

use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\ProductBranchSetting;
use App\Models\ProductUnit;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AutomationReorderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    public function test_reorder_endpoint_returns_suggestions_and_logs_side_effects(): void
    {
        $branch = Branch::create([
            'code' => 'BR-' . Str::upper(Str::random(3)),
            'name' => 'Warehouse',
            'type' => 'main',
        ]);

        $user = User::create([
            'name' => 'Automation User',
            'email' => 'automation@example.com',
            'password' => 'secret-123',
            'is_super_admin' => true,
            'default_branch_id' => $branch->id,
        ]);

        $user->branches()->syncWithoutDetaching([$branch->id => ['role' => 'manager']]);

        $this->actingAs($user);

        $unit = ProductUnit::create([
            'name' => 'Piece',
            'symbol' => 'pc',
        ]);

        $product = Product::create([
            'sku' => 'SKU-' . Str::upper(Str::random(5)),
            'name' => 'Critical Stock Item',
            'product_unit_id' => $unit->id,
        ]);

        ProductBranchSetting::create([
            'product_id' => $product->id,
            'branch_id' => $branch->id,
            'reorder_point' => 10,
            'reorder_qty' => 12,
        ]);

        ProductBatch::create([
            'product_id' => $product->id,
            'branch_id' => $branch->id,
            'batch_number' => 'BATCH-1',
            'quantity' => 2,
        ]);

        $response = $this->postJsonWithCsrf('/api/automation/reorder', [
            'branch_id' => $branch->id,
        ]);

        $response->assertOk()
            ->assertJsonCount(1, 'suggestions')
            ->assertJsonFragment([
                'product_id' => $product->id,
                'recommended_quantity' => 12,
            ]);

        $this->assertDatabaseHas('integration_logs', [
            'channel' => 'email',
            'payload->type' => 'inventory.auto_reorder_suggestions',
        ]);

        $this->assertDatabaseHas('integration_logs', [
            'channel' => 'slack',
            'payload->type' => 'inventory.auto_reorder_suggestions',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'automation.reorder_evaluated',
            'changes->suggestions' => 1,
        ]);
    }

}
