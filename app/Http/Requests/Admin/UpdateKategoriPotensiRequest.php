<?php

namespace App\Http\Requests\Admin;

use App\Models\KategoriPotensi;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateKategoriPotensiRequest extends FormRequest
{
    public function authorize(): bool
    {
        $category = $this->route('kategori_potensi');

        return $category instanceof KategoriPotensi && ($this->user()?->can('update', $category) ?? false);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'kode' => ['required', 'string', 'max:50', 'regex:/^[A-Z0-9_]+$/', Rule::unique('kategori_potensi')->ignore($this->route('kategori_potensi'))],
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
        return (new StoreKategoriPotensiRequest)->messages();
    }
}
