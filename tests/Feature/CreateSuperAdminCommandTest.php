<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class CreateSuperAdminCommandTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creates_a_superadmin_with_normalized_identity_and_a_hashed_password(): void
    {
        $password = 'SafeRuas!Admin2026';

        $this->artisan('superadmin:create')
            ->expectsQuestion('Nama superadmin', ' Super Admin ')
            ->expectsQuestion('Email superadmin', ' ADMIN@EXAMPLE.TEST ')
            ->expectsQuestion('Password superadmin (minimal 12 karakter)', $password)
            ->expectsQuestion('Konfirmasi password', $password)
            ->expectsOutput('Akun superadmin berhasil dibuat. Silakan masuk menggunakan email yang didaftarkan.')
            ->doesntExpectOutputToContain($password)
            ->assertSuccessful();

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', [
            'name' => 'Super Admin',
            'email' => 'admin@example.test',
            'role' => 'super_admin',
            'instansi_id' => null,
        ]);
        $this->assertTrue(Hash::check($password, User::query()->sole()->password));
    }

    public function test_accepts_name_and_email_options_and_the_created_account_can_log_in_and_out(): void
    {
        $password = 'SafeRuas!Admin2026';

        $this->artisan('superadmin:create', ['--name' => 'Super Admin', '--email' => 'admin@example.test'])
            ->expectsQuestion('Password superadmin (minimal 12 karakter)', $password)
            ->expectsQuestion('Konfirmasi password', $password)
            ->assertSuccessful();

        $this->post('/login', ['email' => 'admin@example.test', 'password' => $password])
            ->assertRedirectToRoute('superadmin.dashboard');
        $this->assertAuthenticatedAs(User::query()->sole());

        $this->post(route('logout'))->assertRedirectToRoute('login');
        $this->assertGuest();
    }

    #[TestWith(['', 'admin@example.test', 'Nama superadmin wajib diisi.'])]
    #[TestWith(['Super Admin', '', 'Email superadmin wajib diisi.'])]
    #[TestWith(['Super Admin', 'invalid-email', 'Format email superadmin tidak valid.'])]
    public function test_rejects_invalid_identity_without_creating_an_account(string $name, string $email, string $message): void
    {
        $this->artisan('superadmin:create', ['--name' => $name, '--email' => $email])
            ->expectsOutput($message)
            ->assertExitCode(Command::INVALID);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_rejects_a_name_that_exceeds_the_database_column_length(): void
    {
        $this->artisan('superadmin:create', ['--name' => str_repeat('a', 256), '--email' => 'admin@example.test'])
            ->expectsOutput('Nama superadmin maksimal 255 karakter.')
            ->assertExitCode(Command::INVALID);

        $this->assertDatabaseCount('users', 0);
    }

    #[DataProvider('invalidPasswords')]
    public function test_rejects_invalid_passwords_without_creating_an_account(string $password, string $confirmation, string $message): void
    {
        $this->artisan('superadmin:create', ['--name' => 'Super Admin', '--email' => 'admin@example.test'])
            ->expectsQuestion('Password superadmin (minimal 12 karakter)', $password)
            ->expectsQuestion('Konfirmasi password', $confirmation)
            ->expectsOutput($message)
            ->assertExitCode(Command::INVALID);

        $this->assertDatabaseCount('users', 0);
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function invalidPasswords(): array
    {
        return [
            'empty password' => ['', '', 'Password superadmin wajib diisi.'],
            'short password' => ['short', 'short', 'Password superadmin minimal 12 karakter.'],
            'different confirmation' => ['SafeRuas!Admin2026', 'Different!Admin2026', 'Konfirmasi password tidak cocok.'],
            'missing confirmation' => ['SafeRuas!Admin2026', '', 'Konfirmasi password tidak cocok.'],
            'too many bytes' => [str_repeat('a', 73), str_repeat('a', 73), 'Password maksimal 72 byte.'],
            'multibyte password exceeds bcrypt limit' => [str_repeat('é', 37), str_repeat('é', 37), 'Password maksimal 72 byte.'],
            'null byte' => ["SafeRuas!\0Admin2026", "SafeRuas!\0Admin2026", 'Password tidak boleh mengandung karakter null.'],
        ];
    }

    public function test_accepts_a_multibyte_password_at_the_bcrypt_byte_limit(): void
    {
        $password = str_repeat('é', 36);

        $this->artisan('superadmin:create', ['--name' => 'Super Admin', '--email' => 'admin@example.test'])
            ->expectsQuestion('Password superadmin (minimal 12 karakter)', $password)
            ->expectsQuestion('Konfirmasi password', $password)
            ->assertSuccessful();

        $this->assertDatabaseCount('users', 1);
        $this->assertTrue(Hash::check($password, User::query()->sole()->password));
    }

    #[TestWith(['masyarakat'])]
    #[TestWith(['admin_instansi'])]
    #[TestWith(['super_admin'])]
    #[TestWith(['developer'])]
    public function test_existing_accounts_are_never_promoted_or_overwritten(string $role): void
    {
        $user = User::factory()->create(['email' => 'admin@example.test', 'role' => $role]);
        $originalName = $user->name;
        $originalPassword = $user->password;

        $this->artisan('superadmin:create', ['--name' => 'Replacement Admin', '--email' => $user->email])
            ->expectsOutput('Email tersebut sudah digunakan. Akun yang ada tidak diubah.')
            ->assertExitCode(Command::INVALID);

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => $originalName,
            'role' => $role,
            'password' => $originalPassword,
        ]);
    }

    public function test_duplicate_email_detection_ignores_letter_case(): void
    {
        $user = User::factory()->create(['email' => 'Existing@Example.test', 'role' => 'masyarakat']);

        $this->artisan('superadmin:create', ['--name' => 'Super Admin', '--email' => 'EXISTING@EXAMPLE.TEST'])
            ->expectsOutput('Email tersebut sudah digunakan. Akun yang ada tidak diubah.')
            ->assertExitCode(Command::INVALID);

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'email' => 'Existing@Example.test', 'role' => 'masyarakat']);
    }

    public function test_non_interactive_execution_is_rejected_without_creating_an_account(): void
    {
        $this->artisan('superadmin:create', ['--no-interaction' => true])
            ->expectsOutput('Jalankan perintah ini tanpa --no-interaction agar password dapat dimasukkan secara tersembunyi.')
            ->assertExitCode(Command::INVALID);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_unmigrated_database_returns_an_actionable_error(): void
    {
        $user = User::factory()->create();
        config(['database.connections.superadmin_setup_unmigrated' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        config(['database.default' => 'superadmin_setup_unmigrated']);

        try {
            $this->artisan('superadmin:create', ['--name' => 'Super Admin', '--email' => 'admin@example.test'])
                ->expectsOutput('Akun superadmin gagal dibuat. Periksa konfigurasi database dan pastikan migrasi sudah dijalankan.')
                ->assertFailed();
        } finally {
            config(['database.default' => 'sqlite']);
        }

        $this->assertDatabaseCount('users', 1);
        $this->assertModelExists($user);
    }
}
