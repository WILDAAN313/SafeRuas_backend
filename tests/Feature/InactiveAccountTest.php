<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class InactiveAccountTest extends TestCase
{
    use LazilyRefreshDatabase;

    #[TestWith(['super_admin'])]
    #[TestWith(['admin_instansi'])]
    #[TestWith(['developer'])]
    public function test_inactive_accounts_cannot_log_in_even_with_the_correct_password(string $role): void
    {
        $user = User::factory()->inactive()->create(['role' => $role]);

        $this->from(route('login'))->post('/login', ['email' => $user->email, 'password' => 'password', 'remember' => '1'])
            ->assertRedirectToRoute('login')
            ->assertSessionHasErrors(['email' => 'Email atau password tidak benar.']);

        $this->assertGuest();
    }

    #[TestWith(['super_admin', 'superadmin.dashboard'])]
    #[TestWith(['admin_instansi', 'admin.dashboard'])]
    #[TestWith(['developer', 'admin.dashboard'])]
    public function test_deactivating_an_account_invalidates_its_existing_session(string $role, string $dashboard): void
    {
        $user = User::factory()->create(['role' => $role]);
        $this->withSession(['private_marker' => 'must be removed'])
            ->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirectToRoute($dashboard);

        $user->update(['is_active' => false]);
        Auth::forgetGuards();

        $this->get(route($dashboard))->assertRedirectToRoute('login')
            ->assertSessionHasErrors(['email' => 'Akun Anda dinonaktifkan. Hubungi Superadmin.'])
            ->assertSessionMissing('private_marker');

        $this->assertGuest();
    }

    public function test_inactive_session_receives_401_for_json_requests(): void
    {
        $user = User::factory()->inactive()->create(['role' => 'super_admin']);

        $this->actingAs($user)->getJson(route('superadmin.laporan.peta'))
            ->assertUnauthorized()->assertJsonPath('message', 'Akun Anda dinonaktifkan.');

        $this->assertGuest();
    }

    public function test_deactivated_superadmin_cannot_create_accounts_through_an_old_session(): void
    {
        $user = User::factory()->inactive()->create(['role' => 'super_admin']);

        $this->actingAs($user)->postJson(route('superadmin.admin_instansi.store'), [])
            ->assertUnauthorized();

        $this->assertDatabaseCount('users', 1);
        $this->assertGuest();
    }

    public function test_reactivated_account_can_log_in_again(): void
    {
        $user = User::factory()->inactive()->create(['role' => 'admin_instansi']);
        $user->update(['is_active' => true]);

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirectToRoute('admin.dashboard');

        $this->assertAuthenticatedAs($user);
    }
}
