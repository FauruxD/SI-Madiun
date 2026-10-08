<?php

namespace App\Http\Requests;

use App\Enums\PotensiStatus;
use App\Models\Bumdes;
use App\Models\Desa;
use App\Models\PotensiDesa;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdatePotensiDesaRequest extends FormRequest
{
    private ?Desa $authorizedVillage = null;

    private ?PotensiDesa $authorizedPotential = null;

    public function authorize(): bool
    {
        $user = $this->user();

        if ($user === null) {
            return false;
        }

        $this->authorizedPotential = PotensiDesa::query()
            ->visibleTo($user)
            ->findOrFail((int) $this->route('potential'));

        if (! $user->can('update', $this->authorizedPotential)) {
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
        $currentCategoryId = $this->authorizedPotential?->kategori_potensi_id;

        return [
            'desa_id' => ['required', 'integer', 'exists:desa,id'],
            'kategori_potensi_id' => [
                'required',
                'integer',
                Rule::exists('kategori_potensi', 'id')->where(
                    fn ($query) => $query->where('is_active', true)->when(
                        $currentCategoryId !== null,
                        fn ($categories) => $categories->orWhere('id', $currentCategoryId),
                    ),
                ),
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
            'status' => $this->user()?->isSuperAdmin() && ! $this->authorizedPotential?->eproposal()->exists()
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
        return (new StorePotensiDesaRequest)->messages();
    }
}
