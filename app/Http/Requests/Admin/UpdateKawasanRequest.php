<?php

namespace App\Http\Requests\Admin;

use App\Enums\KawasanStatus;
use App\Models\KawasanPerdesaan;
use App\UserRole;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKawasanRequest extends FormRequest
{
    public function authorize(): bool
    {
        $kawasan = $this->route('kawasan');

        return $kawasan instanceof KawasanPerdesaan && ($this->user()?->can('update', $kawasan) ?? false);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $kawasan = $this->route('kawasan');
        $historicalCategoryIds = $kawasan instanceof KawasanPerdesaan
            ? $kawasan->categories()->pluck('kategori_kawasan.id')->all()
            : [];

        return [
            'nama' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:5000'],
            'tanggal_penetapan' => ['nullable', 'date_format:Y-m-d'],
            'status' => ['required', Rule::enum(KawasanStatus::class)],
            'desa_ids' => ['required', 'array', 'min:1'],
            'desa_ids.*' => ['required', 'integer', 'distinct', 'exists:desa,id'],
            'category_ids' => ['required', 'array', 'min:1'],
            'category_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('kategori_kawasan', 'id')->where(
                    fn (Builder $query): Builder => $query
                        ->where('is_active', true)
                        ->when($historicalCategoryIds !== [], fn (Builder $categories): Builder => $categories->orWhereIn('id', $historicalCategoryIds)),
                ),
            ],
            'admin_user_ids' => ['nullable', 'array'],
            'admin_user_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('users', 'id')->where('role', UserRole::AdminKawasan->value),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nama' => trim((string) $this->input('nama')),
            'deskripsi' => filled($this->input('deskripsi')) ? trim((string) $this->input('deskripsi')) : null,
            'tanggal_penetapan' => filled($this->input('tanggal_penetapan')) ? $this->input('tanggal_penetapan') : null,
            'admin_user_ids' => $this->input('admin_user_ids', []),
        ]);
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'nama.required' => 'Nama kawasan wajib diisi.',
            'desa_ids.required' => 'Pilih minimal satu desa anggota.',
            'desa_ids.min' => 'Pilih minimal satu desa anggota.',
            'category_ids.required' => 'Pilih minimal satu kategori kawasan.',
            'category_ids.min' => 'Pilih minimal satu kategori kawasan.',
            'category_ids.*.exists' => 'Kategori yang dipilih tidak aktif atau tidak tersedia.',
            'admin_user_ids.*.exists' => 'Akun yang dipilih bukan Admin Kawasan.',
        ];
    }
}
