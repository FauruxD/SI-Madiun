<?php

namespace App\Http\Requests\Admin;

use App\Models\KategoriPotensi;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreKategoriPotensiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', KategoriPotensi::class) ?? false;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'kode' => ['required', 'string', 'max:50', 'regex:/^[A-Z0-9_]+$/', 'unique:kategori_potensi,kode'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nama' => trim((string) $this->input('nama')),
            'kode' => Str::of((string) $this->input('kode'))->trim()->upper()->replaceMatches('/[^A-Z0-9]+/', '_')->trim('_')->value(),
            'deskripsi' => filled($this->input('deskripsi')) ? trim((string) $this->input('deskripsi')) : null,
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'nama.required' => 'Nama kategori potensi wajib diisi.',
            'kode.required' => 'Kode kategori potensi wajib diisi.',
            'kode.unique' => 'Kode kategori potensi sudah digunakan.',
            'kode.regex' => 'Kode hanya boleh berisi huruf kapital, angka, dan garis bawah.',
        ];
    }
}
