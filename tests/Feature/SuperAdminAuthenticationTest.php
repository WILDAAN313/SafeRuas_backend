<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class SuperAdminAuthenticationTest extends TestCase
{
    use LazilyRefreshDatabase;

    #[TestWith(['super_admin', 'superadmin.dashboard'])]
    #[TestWith(['admin_instansi', 'admin.dashboard'])]
    #[TestWith(['developer', 'admin.dashboard'])]
    public function test_login_redirects_each_allowed_role_to_its_dashboard(string $role, string $dashboard): void
    {
        $user = User::factory()->create(['role' => $role]);

        $response = $this->from(route('login'))->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirectToRoute($dashboard);
        $this->assertAuthenticatedAs($user);
    }

    #[TestWith(['super_admin', 'superadmin.dashboard'])]
    #[TestWith(['admin_instansi', 'admin.dashboard'])]
    #[TestWith(['developer', 'admin.dashboard'])]
    public function test_authenticated_users_are_redirected_from_login_to_their_dashboard(string $role, string $dashboard): void
    {
        $user = User::factory()->create(['role' => $role]);

        $response = $this->actingAs($user)->get(route('login'));

        $response->assertRedirectToRoute($dashboard);
    }

    #[TestWith(['masyarakat'])]
    #[TestWith(['petugas'])]
    public function test_login_rejects_roles_without_web_dashboard_access(string $role): void
    {
        $user = User::factory()->create(['role' => $role]);

        $response = $this->from(route('login'))->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirectToRoute('login')
            ->assertSessionHasErrors(['email' => 'Email atau password tidak benar.']);
        $this->assertGuest();
    }

    public function test_login_rejects_an_incorrect_superadmin_password(): void
    {
        $user = User::factory()->create(['role' => 'super_admin']);

        $response = $this->from(route('login'))->post('/login', [
            'email' => $user->email,
            'password' => 'incorrect-password',
        ]);

        $response->assertRedirectToRoute('login')
            ->assertSessionHasErrors(['email' => 'Email atau password tidak benar.']);
        $this->assertGuest();
    }

    public function test_superadmin_login_preserves_an_intended_report_page(): void
    {
        $user = User::factory()->create(['role' => 'super_admin']);

        $response = $this->withSession(['url.intended' => route('superadmin.laporan.index')])
            ->post('/login', ['email' => $user->email, 'password' => 'password']);

        $response->assertRedirectToRoute('superadmin.laporan.index');
        $this->assertAuthenticatedAs($user);
    }

    public function test_logout_removes_superadmin_access_to_map_data(): void
    {
        $user = User::factory()->create(['role' => 'super_admin']);

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirectToRoute('login');
        $this->assertGuest();
        $this->getJson(route('superadmin.laporan.peta'))->assertUnauthorized();
    }
}
