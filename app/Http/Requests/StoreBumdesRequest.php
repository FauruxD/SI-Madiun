<?php

namespace App\Http\Requests;

use App\Enums\JenisBumdes;
use App\Models\Bumdes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBumdesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Bumdes::class) ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $villageRules = ['required', 'array', 'min:1'];

        if ($this->input('jenis') === JenisBumdes::Bumdes->value) {
            $villageRules[] = 'max:1';
        }

        return [
            'nama' => ['required', 'string', 'max:255'],
            'jenis' => ['required', Rule::enum(JenisBumdes::class)],
            'nomor_registrasi' => ['nullable', 'string', 'max:100', Rule::unique('bumdes', 'nomor_registrasi')],
            'tahun_berdiri' => ['nullable', 'integer', 'digits:4', 'max:'.date('Y')],
            'status_hukum' => ['nullable', 'string', 'max:255'],
            'alamat' => ['nullable', 'string', 'max:2000'],
            'telepon' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:5000'],
            'desa_ids' => $villageRules,
            'desa_ids.*' => ['required', 'integer', 'distinct', 'exists:desa,id'],
            'desa_utama_id' => ['required', 'integer', 'exists:desa,id', Rule::in($this->input('desa_ids', []))],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge($this->normalizedProfile());
    }

    /** @return array<string, mixed> */
    private function normalizedProfile(): array
    {
        $nullableStrings = ['nomor_registrasi', 'status_hukum', 'alamat', 'telepon', 'email', 'deskripsi'];
        $data = ['nama' => trim((string) $this->input('nama'))];

        foreach ($nullableStrings as $field) {
            $data[$field] = filled($this->input($field)) ? trim((string) $this->input($field)) : null;
        }

        $data['tahun_berdiri'] = filled($this->input('tahun_berdiri')) ? $this->input('tahun_berdiri') : null;
        $data['desa_ids'] = $this->input('desa_ids', []);

        return $data;
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'nama.required' => 'Nama BUMDes wajib diisi.',
            'jenis.required' => 'Jenis BUMDes wajib dipilih.',
            'jenis.enum' => 'Jenis BUMDes tidak valid.',
            'nomor_registrasi.unique' => 'Nomor registrasi Kemendesa sudah digunakan.',
            'tahun_berdiri.integer' => 'Tahun berdiri tidak valid.',
            'tahun_berdiri.digits' => 'Tahun berdiri harus terdiri dari empat angka.',
            'tahun_berdiri.max' => 'Tahun berdiri tidak boleh melewati tahun berjalan.',
            'email.email' => 'Alamat email BUMDes tidak valid.',
            'desa_ids.required' => 'Desa harus dipilih.',
            'desa_ids.min' => 'Desa harus dipilih.',
            'desa_ids.max' => 'BUMDes hanya dapat terkait dengan satu desa. Gunakan jenis BUMDesma untuk kelompok desa.',
            'desa_ids.*.distinct' => 'Desa yang sama tidak boleh dipilih dua kali.',
            'desa_utama_id.required' => 'Desa utama harus dipilih.',
            'desa_utama_id.in' => 'Desa utama harus termasuk dalam desa asal yang dipilih.',
        ];
    }
}
