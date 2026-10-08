<?php

namespace App\Http\Requests\Admin;

use App\Models\KategoriKawasan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreKategoriKawasanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', KategoriKawasan::class) ?? false;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'kode' => ['required', 'string', 'max:50', 'regex:/^[A-Z0-9_]+$/', 'unique:kategori_kawasan,kode'],
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
}
