<?php

namespace App\Http\Requests;

use App\Models\Bumdes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class SaveProdukLayananRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        $bumdes = $user ? Bumdes::query()->visibleTo($user)->findOrFail((int) $this->route('bumdes')) : null;

        return $bumdes !== null && $user->can('manageData', $bumdes);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'kategori' => ['nullable', 'string', 'max:255'],
            'deskripsi' => ['required', 'string', 'max:5000'],
            'harga' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'satuan' => ['nullable', 'string', 'max:50'],
            'is_active' => ['required', 'boolean'],
            'foto' => ['nullable', File::image()->types(config('bumdes.images.allowed_types'))->max(config('bumdes.images.max_size_kb').'kb')],
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = ['nama' => trim((string) $this->input('nama')), 'deskripsi' => trim((string) $this->input('deskripsi')), 'is_active' => $this->boolean('is_active')];

        foreach (['kategori', 'satuan'] as $field) {
            $data[$field] = filled($this->input($field)) ? trim((string) $this->input($field)) : null;
        }

        $data['harga'] = filled($this->input('harga')) ? $this->input('harga') : null;
        $this->merge($data);
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['nama.required' => 'Nama produk/layanan wajib diisi.', 'deskripsi.required' => 'Deskripsi produk/layanan wajib diisi.', 'harga.decimal' => 'Harga maksimal menggunakan dua angka desimal.', 'foto.image' => 'Foto produk harus berupa gambar yang didukung.'];
    }
}
