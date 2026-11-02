<?php

namespace App\Console\Commands;

use App\Models\User;
use Closure;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

#[Signature('superadmin:create
    {--name= : Nama lengkap superadmin}
    {--email= : Email akun superadmin}')]
#[Description('Membuat akun superadmin melalui terminal dengan password tersembunyi')]
class CreateSuperAdminCommand extends Command
{
    private const string EMAIL_ALREADY_USED = 'Email tersebut sudah digunakan. Akun yang ada tidak diubah.';

    public function handle(): int
    {
        if (! $this->input->isInteractive()) {
            $this->error('Jalankan perintah ini tanpa --no-interaction agar password dapat dimasukkan secara tersembunyi.');

            return self::INVALID;
        }

        try {
            return $this->createSuperAdmin();
        } catch (ValidationException $exception) {
            foreach ($exception->validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::INVALID;
        } catch (UniqueConstraintViolationException) {
            $this->error(self::EMAIL_ALREADY_USED);

            return self::FAILURE;
        } catch (QueryException) {
            $this->error('Akun superadmin gagal dibuat. Periksa konfigurasi database dan pastikan migrasi sudah dijalankan.');

            return self::FAILURE;
        }
    }

    private function createSuperAdmin(): int
    {
        $identity = Validator::make([
            'name' => trim((string) ($this->option('name') ?? $this->ask('Nama superadmin'))),
            'email' => Str::lower(trim((string) ($this->option('email') ?? $this->ask('Email superadmin')))),
        ], [
            'name' => ['bail', 'required', 'string', 'max:255'],
            'email' => ['bail', 'required', 'email', 'max:255'],
        ], [
            'name.required' => 'Nama superadmin wajib diisi.',
            'name.max' => 'Nama superadmin maksimal 255 karakter.',
            'email.required' => 'Email superadmin wajib diisi.',
            'email.email' => 'Format email superadmin tidak valid.',
            'email.max' => 'Email superadmin maksimal 255 karakter.',
        ])->validate();

        if (User::query()->whereRaw('LOWER(email) = ?', [$identity['email']])->exists()) {
            $this->error(self::EMAIL_ALREADY_USED);

            return self::INVALID;
        }

        $credentials = Validator::make([
            'password' => $this->secret('Password superadmin (minimal 12 karakter)', false),
            'password_confirmation' => $this->secret('Konfirmasi password', false),
        ], [
            'password' => [
                'bail', 'required', 'string', 'min:12', 'confirmed',
                function (string $attribute, string $value, Closure $fail): void {
                    if (strlen($value) > 72) {
                        $fail('Password maksimal 72 byte.');
                    }

                    if (str_contains($value, "\0")) {
                        $fail('Password tidak boleh mengandung karakter null.');
                    }
                },
            ],
        ], [
            'password.required' => 'Password superadmin wajib diisi.',
            'password.min' => 'Password superadmin minimal 12 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ])->validate();

        User::query()->create([
            'name' => $identity['name'],
            'email' => $identity['email'],
            'password' => $credentials['password'],
            'role' => 'super_admin',
            'instansi_id' => null,
        ]);

        $this->info('Akun superadmin berhasil dibuat. Silakan masuk menggunakan email yang didaftarkan.');

        return self::SUCCESS;
    }
}
