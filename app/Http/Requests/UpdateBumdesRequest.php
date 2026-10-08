<?php

namespace App\Http\Requests;

use App\Enums\JenisBumdes;
use App\Models\Bumdes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBumdesRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if ($user === null) {
            return false;
        }

        $bumdes = Bumdes::query()->visibleTo($user)->findOrFail((int) $this->route('bumdes'));

        return $user->can('update', $bumdes);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $bumdesId = (int) $this->route('bumdes');
        $rules = [
            'nama' => ['required', 'string', 'max:255'],
            'nomor_registrasi' => ['nullable', 'string', 'max:100', Rule::unique('bumdes', 'nomor_registrasi')->ignore($bumdesId)],
            'tahun_berdiri' => ['nullable', 'integer', 'digits:4', 'max:'.date('Y')],
            'status_hukum' => ['nullable', 'string', 'max:255'],
            'alamat' => ['nullable', 'string', 'max:2000'],
            'telepon' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:5000'],
        ];

        if ($this->user()?->isSuperAdmin()) {
            $villageRules = ['required', 'array', 'min:1'];

            if ($this->input('jenis') === JenisBumdes::Bumdes->value) {
                $villageRules[] = 'max:1';
            }

            $rules += [
                'jenis' => ['required', Rule::enum(JenisBumdes::class)],
                'desa_ids' => $villageRules,
                'desa_ids.*' => ['required', 'integer', 'distinct', 'exists:desa,id'],
                'desa_utama_id' => ['required', 'integer', 'exists:desa,id', Rule::in($this->input('desa_ids', []))],
            ];
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        $data = ['nama' => trim((string) $this->input('nama'))];

        foreach (['nomor_registrasi', 'status_hukum', 'alamat', 'telepon', 'email', 'deskripsi'] as $field) {
            $data[$field] = filled($this->input($field)) ? trim((string) $this->input($field)) : null;
        }

        $data['tahun_berdiri'] = filled($this->input('tahun_berdiri')) ? $this->input('tahun_berdiri') : null;

        if ($this->user()?->isSuperAdmin()) {
            $data['desa_ids'] = $this->input('desa_ids', []);
        }

        $this->merge($data);
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return (new StoreBumdesRequest)->messages();
    }
}
