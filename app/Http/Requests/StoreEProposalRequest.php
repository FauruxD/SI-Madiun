<?php

namespace App\Http\Requests;

use App\Models\Desa;
use App\Models\EProposal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreEProposalRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->can('create', EProposal::class);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'potensi_desa_id' => [
                'required',
                'integer',
                Rule::exists('potensi_desa', 'id')->whereNull('deleted_at'),
                Rule::unique('eproposal', 'potensi_desa_id'),
            ],
            'catatan_pengaju' => ['nullable', 'string', 'max:5000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['catatan_pengaju' => filled($this->input('catatan_pengaju')) ? trim((string) $this->input('catatan_pengaju')) : null]);
    }

    /** @return array<int, callable> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->has('potensi_desa_id') || $this->user() === null) {
                return;
            }

            $village = Desa::query()->visibleTo($this->user())->whereHas('potentials', fn ($query) => $query->whereKey((int) $this->input('potensi_desa_id')))->first();
            if ($village === null) {
                $validator->errors()->add('potensi_desa_id', 'Potensi tersebut tidak berada dalam kewenangan Anda.');
            }
        }];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'potensi_desa_id.required' => 'Potensi desa wajib dipilih.',
            'potensi_desa_id.exists' => 'Potensi desa tidak tersedia.',
            'potensi_desa_id.unique' => 'Potensi ini sudah memiliki E-Proposal.',
            'catatan_pengaju.max' => 'Catatan pengaju maksimal 5.000 karakter.',
        ];
    }
}
