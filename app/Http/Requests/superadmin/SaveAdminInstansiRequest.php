<?php

namespace App\Http\Requests\superadmin;

use App\Models\Instansi;
use App\Models\User;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SaveAdminInstansiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === 'super_admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        $admin = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique(User::class, 'email')->ignore($admin instanceof User ? $admin : null),
            ],
            'instansi_id' => [
                'required',
                'integer',
                Rule::exists('instansi', 'id')->where('nama_instansi', Instansi::NAMA_PEMDA_INDRAMAYU),
            ],
            'is_active' => ['required', 'boolean'],
            'password' => [
                'bail', $this->isMethod('POST') ? 'required' : 'nullable', 'string', 'min:8', 'confirmed',
                function (string $attribute, string $value, Closure $fail): void {
                    if (strlen($value) > 72) {
                        $fail('Password maksimal 72 byte.');
                    }

                    if (str_contains($value, "\0")) {
                        $fail('Password tidak boleh mengandung karakter null.');
                    }
                },
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('email'))) {
            $this->merge(['email' => Str::lower(trim($this->input('email')))]);
        }
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama admin wajib diisi.',
            'name.max' => 'Nama admin maksimal 255 karakter.',
            'email.required' => 'Email admin wajib diisi.',
            'email.email' => 'Format email admin tidak valid.',
            'email.unique' => 'Email tersebut sudah digunakan.',
            'instansi_id.required' => 'Pilih instansi untuk admin.',
            'instansi_id.exists' => 'Instansi yang dipilih tidak tersedia.',
            'is_active.required' => 'Status akun wajib dipilih.',
            'is_active.boolean' => 'Status akun tidak valid.',
            'password.required' => 'Password admin wajib diisi.',
            'password.min' => 'Password admin minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ];
    }
}
