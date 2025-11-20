<?php

namespace Tests\Feature\Auth;

use App\Models\Branch;
use App\Models\IntegrationLog;
use App\Models\User;
use Illuminate\Support\Facades\Session;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    public function test_registers_first_user_successfully(): void
    {
        $response = $this->postJsonWithCsrf('/api/auth/register', [
            'name' => 'First Admin',
            'email' => 'admin@example.com',
            'password' => 'password-123',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
            ]);

        $this->assertDatabaseHas('users', ['email' => 'admin@example.com']);
        $this->assertTrue(
            IntegrationLog::where('channel', 'auth.registered')->exists(),
            'Expected register notification to be logged.'
        );
    }

    public function test_login_returns_user_payload_and_logs_event(): void
    {
        $user = User::create([
            'name' => 'Login User',
            'email' => 'login@example.com',
            'password' => 'secret-123',
        ]);

        $response = $this->postJsonWithCsrf('/api/auth/login', [
            'email' => 'login@example.com',
            'password' => 'secret-123',
        ]);

        $response->assertOk()
            ->assertJsonPath('user.email', 'login@example.com');

        $this->assertAuthenticatedAs($user);
        $this->assertTrue(
            IntegrationLog::where('channel', 'auth.login')->exists(),
            'Expected login notification to be logged.'
        );
    }

    public function test_logout_invalidates_session_and_logs_event(): void
    {
        $user = User::create([
            'name' => 'Logout User',
            'email' => 'logout@example.com',
            'password' => 'secret-123',
        ]);

        $this->actingAs($user);

        $response = $this->postJsonWithCsrf('/api/auth/logout');

        $response->assertOk()
            ->assertJson(['message' => 'Logged out']);

        $this->assertGuest();
        $this->assertTrue(
            IntegrationLog::where('channel', 'auth.logout')->exists(),
            'Expected logout notification to be logged.'
        );
    }

    public function test_non_super_admin_cannot_register_additional_users(): void
    {
        User::create([
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'password' => 'secret-123',
        ]);

        $response = $this->postJsonWithCsrf('/api/auth/register', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password-123',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('users', ['email' => 'new@example.com']);
    }

    public function test_super_admin_can_register_additional_users(): void
    {
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'super@example.com',
            'password' => 'secret-123',
            'is_super_admin' => true,
        ]);

        $this->actingAs($superAdmin);

        $response = $this->postJsonWithCsrf('/api/auth/register', [
            'name' => 'Second User',
            'email' => 'second@example.com',
            'password' => 'password-456',
        ]);

        $response->assertCreated()
            ->assertJsonPath('user.email', 'second@example.com');

        $this->assertDatabaseHas('users', ['email' => 'second@example.com']);
    }

    public function test_login_assigns_default_branch_to_session(): void
    {
        $branch = Branch::create([
            'code' => 'BR-001',
            'name' => 'Cabang Utama',
            'type' => 'branch',
        ]);

        $user = User::create([
            'name' => 'Branch User',
            'email' => 'branch@example.com',
            'password' => 'secret-123',
            'default_branch_id' => $branch->id,
        ]);

        $user->branches()->syncWithoutDetaching([$branch->id => ['role' => 'staff']]);

        Session::start();

        $response = $this->post('/login', [
            'email' => 'branch@example.com',
            'password' => 'secret-123',
            '_token' => Session::token(),
        ]);

        $response->assertRedirect('/dashboard/branch');
        $this->assertAuthenticatedAs($user);
        $this->assertSame($branch->id, session('branch_id'));
    }
}