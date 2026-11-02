<?php

namespace Tests\Feature;

use App\Models\Instansi;
use App\Models\Laporan;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class SuperAdminAdminInstansiTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_guest_is_redirected_to_login_before_viewing_accounts(): void
    {
        $this->get(route('superadmin.admin_instansi.index'))->assertRedirectToRoute('login');
    }

    public function test_guest_cannot_create_an_account(): void
    {
        $this->postJson(route('superadmin.admin_instansi.store'), [])->assertUnauthorized();
        $this->assertDatabaseCount('users', 0);
    }

    /** @return iterable<string, array{string, string, string}> */
    public static function forbiddenRequests(): iterable
    {
        foreach (['admin_instansi', 'developer', 'masyarakat', 'petugas'] as $role) {
            foreach (['GET' => 'index', 'POST' => 'store', 'PUT' => 'update', 'DELETE' => 'destroy'] as $method => $action) {
                yield "{$role} {$action}" => [$role, $method, $action];
            }
        }
    }

    #[DataProvider('forbiddenRequests')]
    public function test_other_roles_cannot_manage_admin_accounts(string $role, string $method, string $action): void
    {
        $actor = User::factory()->create(['role' => $role]);
        $target = User::factory()->create(['role' => 'admin_instansi']);

        $this->actingAs($actor)->json($method, route('superadmin.admin_instansi.'.$action, $target), [])
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $target->id, 'role' => 'admin_instansi']);
        $this->assertDatabaseCount('users', 2);
    }

    #[TestWith(['Nur Admin'])]
    #[TestWith(['nur.admin@saferuas.test'])]
    public function test_list_searches_admin_name_and_email_without_exposing_other_roles(string $search): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);
        $admin = User::factory()->for(Instansi::factory(), 'instansi')->create([
            'role' => 'admin_instansi', 'name' => 'Nur Admin', 'email' => 'nur.admin@saferuas.test',
        ]);
        User::factory()->create(['role' => 'masyarakat', 'name' => 'Nur Admin Warga']);
        User::factory()->create(['role' => 'admin_instansi', 'name' => 'Admin Lain']);

        $this->actingAs($superadmin)->get(route('superadmin.admin_instansi.index', ['cari' => $search]))
            ->assertOk()->assertSee($admin->instansi->nama_instansi)
            ->assertDontSee('Nur Admin Warga')->assertDontSee('Admin Lain')
            ->assertViewHas('adminInstansi', fn ($accounts): bool => $accounts->total() === 1 && $accounts->first()->is($admin));
    }

    public function test_list_paginates_and_preserves_search(): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);
        User::factory()->count(16)->create(['role' => 'admin_instansi', 'name' => 'Admin Pencarian']);

        $this->actingAs($superadmin)->get(route('superadmin.admin_instansi.index', ['cari' => 'Pencarian']))
            ->assertOk()->assertViewHas('adminInstansi', fn ($accounts): bool => $accounts->total() === 16 && $accounts->count() === 15)
            ->assertSee('cari=Pencarian&amp;page=2', false);
    }

    public function test_create_form_offers_all_instansi(): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);
        Instansi::factory()->create(['nama_instansi' => 'Dinas Jalan']);
        Instansi::factory()->create(['nama_instansi' => 'Dinas Penerangan']);

        $this->actingAs($superadmin)->get(route('superadmin.admin_instansi.create'))
            ->assertOk()->assertSee('Dinas Jalan')->assertSee('Dinas Penerangan');
    }

    public function test_create_form_explains_when_no_instansi_is_available(): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($superadmin)->get(route('superadmin.admin_instansi.create'))
            ->assertOk()->assertSee('Belum ada data instansi.');
    }

    public function test_superadmin_creates_an_account_for_the_selected_instansi_and_cannot_inject_a_role(): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);
        Instansi::factory()->create(['nama_instansi' => 'Instansi Pertama']);
        $data = $this->validAccountData();
        $data['email'] = '  ADMIN.BARU@SAFERUAS.TEST  ';
        $data['role'] = 'super_admin';

        $response = $this->actingAs($superadmin)->post(route('superadmin.admin_instansi.store'), $data);

        $admin = User::query()->where('email', 'admin.baru@saferuas.test')->firstOrFail();
        $response->assertRedirectToRoute('superadmin.admin_instansi.show', $admin)->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'id' => $admin->id, 'name' => 'Admin Baru', 'instansi_id' => $data['instansi_id'],
            'role' => 'admin_instansi', 'is_active' => true,
        ]);
        $this->assertTrue(Hash::check('PasswordBaru123!', $admin->password));
    }

    public function test_missing_required_data_does_not_create_an_account(): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($superadmin)->post(route('superadmin.admin_instansi.store'), [])
            ->assertSessionHasErrors([
                'name' => 'Nama admin wajib diisi.', 'email' => 'Email admin wajib diisi.',
                'instansi_id' => 'Pilih instansi untuk admin.', 'is_active' => 'Status akun wajib dipilih.',
                'password' => 'Password admin wajib diisi.',
            ]);

        $this->assertDatabaseCount('users', 1);
    }

    #[TestWith(['email', 'bukan-email', 'Format email admin tidak valid.'])]
    #[TestWith(['instansi_id', 999999, 'Instansi yang dipilih tidak tersedia.'])]
    #[TestWith(['is_active', 'nonaktif', 'Status akun tidak valid.'])]
    #[TestWith(['password', 'pendek', 'Password admin minimal 8 karakter.'])]
    #[TestWith(['password_confirmation', 'berbeda', 'Konfirmasi password tidak cocok.'])]
    public function test_invalid_account_data_is_rejected(string $field, mixed $value, string $message): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);
        $data = $this->validAccountData();
        $data[$field] = $value;

        $this->actingAs($superadmin)->post(route('superadmin.admin_instansi.store'), $data)
            ->assertSessionHasErrors([$field === 'password_confirmation' ? 'password' : $field => $message]);

        $this->assertDatabaseCount('users', 1);
    }

    #[TestWith(["ValidPass\0word", 'Password tidak boleh mengandung karakter null.'])]
    #[TestWith(['aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'Password maksimal 72 byte.'])]
    public function test_passwords_unsupported_by_bcrypt_are_rejected(string $password, string $message): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);
        $data = [...$this->validAccountData(), 'password' => $password, 'password_confirmation' => $password];

        $this->actingAs($superadmin)->post(route('superadmin.admin_instansi.store'), $data)
            ->assertSessionHasErrors(['password' => $message]);

        $this->assertDatabaseCount('users', 1);
    }

    public function test_email_already_used_by_another_role_is_rejected(): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin', 'email' => 'terpakai@saferuas.test']);
        $data = [...$this->validAccountData(), 'email' => $superadmin->email];

        $this->actingAs($superadmin)->post(route('superadmin.admin_instansi.store'), $data)
            ->assertSessionHasErrors(['email' => 'Email tersebut sudah digunakan.']);

        $this->assertDatabaseCount('users', 1);
    }

    public function test_superadmin_edits_an_account_without_replacing_an_empty_password(): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);
        $admin = User::factory()->create(['role' => 'admin_instansi']);
        $originalPassword = $admin->password;
        $data = [...$this->validAccountData(), 'email' => $admin->email, 'password' => '', 'password_confirmation' => '', 'is_active' => '0'];

        $this->actingAs($superadmin)->put(route('superadmin.admin_instansi.update', $admin), $data)
            ->assertRedirectToRoute('superadmin.admin_instansi.show', $admin)->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $admin->id, 'name' => 'Admin Baru', 'instansi_id' => $data['instansi_id'],
            'password' => $originalPassword, 'is_active' => false, 'role' => 'admin_instansi',
        ]);
    }

    public function test_superadmin_can_reactivate_an_account_and_reset_its_password(): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);
        $admin = User::factory()->inactive()->create(['role' => 'admin_instansi']);
        $data = [...$this->validAccountData(), 'role' => 'super_admin'];

        $this->actingAs($superadmin)->put(route('superadmin.admin_instansi.update', $admin), $data)
            ->assertRedirectToRoute('superadmin.admin_instansi.show', $admin);

        $admin->refresh();
        $this->assertTrue($admin->is_active);
        $this->assertSame('admin_instansi', $admin->role);
        $this->assertTrue(Hash::check('PasswordBaru123!', $admin->password));
    }

    #[TestWith(['show'])]
    #[TestWith(['edit'])]
    public function test_account_pages_render_existing_data_and_escape_names(string $action): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);
        $admin = User::factory()->for(Instansi::factory(), 'instansi')->create([
            'role' => 'admin_instansi', 'name' => '<script>alert("admin")</script>',
        ]);

        $this->actingAs($superadmin)->get(route('superadmin.admin_instansi.'.$action, $admin))
            ->assertOk()->assertSee($admin->name)->assertDontSee($admin->name, false)
            ->assertSee($admin->email)->assertSee($admin->instansi->nama_instansi);
    }

    #[TestWith(['show'])]
    #[TestWith(['edit'])]
    public function test_missing_admin_returns_404(string $action): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($superadmin)->get(route('superadmin.admin_instansi.'.$action, 999999))->assertNotFound();
    }

    #[TestWith(['GET', 'show'])]
    #[TestWith(['GET', 'edit'])]
    #[TestWith(['PUT', 'update'])]
    #[TestWith(['DELETE', 'destroy'])]
    public function test_admin_management_cannot_target_a_different_role(string $method, string $action): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);
        $citizen = User::factory()->create(['role' => 'masyarakat']);
        $data = $method === 'PUT' ? $this->validAccountData() : [];

        $this->actingAs($superadmin)->json($method, route('superadmin.admin_instansi.'.$action, $citizen), $data)
            ->assertNotFound();

        $this->assertDatabaseHas('users', ['id' => $citizen->id, 'role' => 'masyarakat', 'email' => $citizen->email]);
    }

    public function test_admin_without_related_history_can_be_deleted(): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);
        $admin = User::factory()->create(['role' => 'admin_instansi']);

        $this->actingAs($superadmin)->delete(route('superadmin.admin_instansi.destroy', $admin))
            ->assertRedirectToRoute('superadmin.admin_instansi.index')->assertSessionHas('success');

        $this->assertModelMissing($admin);
        $this->assertModelExists($superadmin);
    }

    public function test_admin_with_status_history_cannot_be_deleted(): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);
        $admin = User::factory()->create(['role' => 'admin_instansi']);
        $report = Laporan::factory()->create();
        $history = $report->riwayatStatus()->create(['user_id' => $admin->id, 'status_baru' => 'diproses']);

        $this->actingAs($superadmin)->deleteJson(route('superadmin.admin_instansi.destroy', $admin))
            ->assertUnprocessable()->assertJsonValidationErrors([
                'akun' => 'Akun memiliki riwayat terkait dan tidak dapat dihapus. Nonaktifkan akun untuk menjaga riwayat.',
            ]);

        $this->assertModelExists($admin);
        $this->assertModelExists($history);
        $this->assertModelExists($report);
    }

    #[TestWith(['admin_id'])]
    #[TestWith(['petugas_id'])]
    public function test_admin_linked_to_an_assignment_in_either_role_cannot_be_deleted(string $foreignKey): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);
        $admin = User::factory()->create(['role' => 'admin_instansi']);
        $other = User::factory()->create(['role' => 'petugas']);
        $report = Laporan::factory()->create();
        $assignment = $report->penugasan()->create([
            'admin_id' => $other->id, 'petugas_id' => $other->id, $foreignKey => $admin->id,
        ]);

        $this->actingAs($superadmin)->deleteJson(route('superadmin.admin_instansi.destroy', $admin))
            ->assertUnprocessable()->assertJsonValidationErrors(['akun']);

        $this->assertModelExists($admin);
        $this->assertModelExists($assignment);
    }

    public function test_admin_who_owns_a_report_cannot_be_deleted(): void
    {
        $superadmin = User::factory()->create(['role' => 'super_admin']);
        $admin = User::factory()->create(['role' => 'admin_instansi']);
        $report = Laporan::factory()->for($admin, 'pelapor')->create();

        $this->actingAs($superadmin)->deleteJson(route('superadmin.admin_instansi.destroy', $admin))
            ->assertUnprocessable()->assertJsonValidationErrors(['akun']);

        $this->assertModelExists($admin);
        $this->assertModelExists($report);
    }

    /** @return array<string, mixed> */
    private function validAccountData(): array
    {
        return [
            'name' => 'Admin Baru', 'email' => 'admin.baru@saferuas.test',
            'instansi_id' => Instansi::factory()->create()->id, 'is_active' => '1',
            'password' => 'PasswordBaru123!', 'password_confirmation' => 'PasswordBaru123!',
        ];
    }
}
