<?php

namespace App\Http\Requests;

use App\Models\Bumdes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class SavePengurusBumdesRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $bumdes = $user ? Bumdes::query()->visibleTo($user)->findOrFail((int) $this->route('bumdes')) : null;

        return $bumdes !== null && $user->can('manageData', $bumdes);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'jabatan' => ['required', 'string', 'max:255'],
            'nomor_telepon' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'urutan' => ['required', 'integer', 'min:0', 'max:10000'],
            'is_active' => ['required', 'boolean'],
            'foto' => ['nullable', File::image()->types(config('bumdes.images.allowed_types'))->max(config('bumdes.images.max_size_kb').'kb')],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nama' => trim((string) $this->input('nama')),
            'jabatan' => trim((string) $this->input('jabatan')),
            'nomor_telepon' => filled($this->input('nomor_telepon')) ? trim((string) $this->input('nomor_telepon')) : null,
            'email' => filled($this->input('email')) ? trim((string) $this->input('email')) : null,
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['nama.required' => 'Nama pengurus wajib diisi.', 'jabatan.required' => 'Jabatan pengurus wajib diisi.', 'email.email' => 'Email pengurus tidak valid.', 'foto.image' => 'Foto pengurus harus berupa gambar yang didukung.'];
    }
}
