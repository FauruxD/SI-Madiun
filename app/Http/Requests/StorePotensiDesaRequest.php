<?php

namespace App\Http\Requests;

use App\Enums\PotensiStatus;
use App\Models\Bumdes;
use App\Models\Desa;
use App\Models\PotensiDesa;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StorePotensiDesaRequest extends FormRequest
{
    private ?Desa $authorizedVillage = null;

    public function authorize(): bool
    {
        $user = $this->user();

        if ($user === null || ! $user->can('create', PotensiDesa::class)) {
            return false;
        }

        if ($this->integer('desa_id') > 0) {
            $this->authorizedVillage = Desa::query()->visibleTo($user)->findOrFail($this->integer('desa_id'));
        }

        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'desa_id' => ['required', 'integer', 'exists:desa,id'],
            'kategori_potensi_id' => [
                'required',
                'integer',
                Rule::exists('kategori_potensi', 'id')->where('is_active', true),
            ],
            'bumdes_id' => [
                'nullable',
                'integer',
                Rule::exists('bumdes', 'id')->whereNull('deleted_at'),
            ],
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['required', 'string', 'max:10000'],
            'data_pendukung' => ['nullable', 'string', 'max:10000'],
            'kebutuhan_investasi' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'status' => $this->user()?->isSuperAdmin()
                ? ['required', Rule::enum(PotensiStatus::class)]
                : ['prohibited'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [
            'judul' => trim((string) $this->input('judul')),
            'deskripsi' => trim((string) $this->input('deskripsi')),
        ];

        foreach (['bumdes_id', 'data_pendukung', 'kebutuhan_investasi'] as $field) {
            $data[$field] = filled($this->input($field)) ? $this->input($field) : null;
        }

        if ($this->user()?->isSuperAdmin() && ! filled($this->input('status'))) {
            $data['status'] = PotensiStatus::Draft->value;
        }

        $this->merge($data);
    }

    /** @return array<int, callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $bumdesId = (int) $this->input('bumdes_id');

            if ($bumdesId === 0 || $this->authorizedVillage === null || $validator->errors()->has('bumdes_id')) {
                return;
            }

            $isRelated = Bumdes::query()
                ->whereKey($bumdesId)
                ->whereHas('desas', fn ($villages) => $villages->whereKey($this->authorizedVillage?->getKey()))
                ->exists();

            if (! $isRelated) {
                $validator->errors()->add('bumdes_id', 'BUMDes yang dipilih tidak terkait dengan desa ini.');
            }
        }];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'desa_id.required' => 'Desa wajib dipilih.',
            'kategori_potensi_id.required' => 'Kategori potensi wajib dipilih.',
            'kategori_potensi_id.exists' => 'Kategori potensi tidak aktif atau tidak tersedia.',
            'bumdes_id.exists' => 'BUMDes yang dipilih tidak tersedia.',
            'judul.required' => 'Judul potensi wajib diisi.',
            'deskripsi.required' => 'Deskripsi potensi wajib diisi.',
            'kebutuhan_investasi.numeric' => 'Kebutuhan investasi harus berupa angka.',
            'kebutuhan_investasi.min' => 'Kebutuhan investasi tidak boleh negatif.',
            'kebutuhan_investasi.decimal' => 'Kebutuhan investasi maksimal menggunakan dua angka desimal.',
            'status.enum' => 'Status publikasi tidak valid.',
            'status.prohibited' => 'Status publikasi hanya dapat diubah oleh Super Admin pada Phase 5.',
        ];
    }
}
