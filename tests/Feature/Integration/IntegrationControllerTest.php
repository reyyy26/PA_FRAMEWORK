<?php

namespace Tests\Feature\Integration;

use App\Models\IntegrationLog;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IntegrationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    public function test_sales_payload_is_queued_to_integration_logs(): void
    {
        $this->actingAsSuperAdmin();

        $response = $this->postJsonWithCsrf('/api/integrations/erp/sales', [
            'sales' => [
                ['number' => 'TX-001', 'total' => 120000],
            ],
        ]);

        $response->assertOk()
            ->assertJson([
                'status' => 'queued',
            ]);

        $log = IntegrationLog::where('channel', 'erp.sales')->latest('id')->first();

        $this->assertNotNull($log, 'Integration log not found');
        $this->assertSame('TX-001', data_get($log->payload, 'sales.0.number'));
    }

    public function test_logs_endpoint_masks_sensitive_api_keys(): void
    {
        $this->actingAsSuperAdmin();

        IntegrationLog::create([
            'channel' => 'erp.configure',
            'status' => 'queued',
            'payload' => [
                'endpoint' => 'https://erp.test/api',
                'api_key' => 'super-secret',
            ],
        ]);

        $response = $this->getJson('/api/integrations/logs');

        $response->assertOk()
            ->assertJsonPath('data.0.channel', 'erp.configure')
            ->assertJsonPath('data.0.payload.api_key', '********');
    }

    private function actingAsSuperAdmin(): User
    {
        $user = User::create([
            'name' => 'Integration Admin',
            'email' => 'integration-admin@example.com',
            'password' => 'secret-123',
            'is_super_admin' => true,
        ]);

        $this->actingAs($user);

        return $user;
    }
}