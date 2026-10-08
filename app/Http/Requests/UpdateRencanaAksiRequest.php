<?php

namespace App\Http\Requests;

use App\Enums\ActionPeriod;
use App\Models\Bumdes;
use App\Models\RencanaAksi;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateRencanaAksiRequest extends FormRequest
{
    private ?RencanaAksi $authorizedPlan = null;

    public function authorize(): bool
    {
        $user = $this->user();

        if ($user === null) {
            return false;
        }

        $bumdes = Bumdes::query()->visibleTo($user)->findOrFail((int) $this->route('bumdes'));
        $this->authorizedPlan = $bumdes->actionPlans()->findOrFail((int) $this->route('rencanaAksi'));

        return $user->can('update', $this->authorizedPlan);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'tahun' => ['required', 'integer', 'digits:4', 'min:2000', 'max:'.((int) date('Y') + 10)],
            'program' => ['required', 'string', 'max:255'],
            'kegiatan' => ['required', 'string', 'max:255'],
            'target_deskripsi' => ['nullable', 'string', 'max:5000'],
            'target_nilai' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'target_satuan' => ['nullable', 'string', 'max:100'],
            'periode' => ['required', Rule::enum(ActionPeriod::class)],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [
            'program' => trim((string) $this->input('program')),
            'kegiatan' => trim((string) $this->input('kegiatan')),
        ];

        foreach (['target_deskripsi', 'target_nilai', 'target_satuan', 'tanggal_mulai', 'tanggal_selesai'] as $field) {
            $data[$field] = filled($this->input($field)) ? $this->input($field) : null;
        }

        $this->merge($data);
    }

    /** @return array<int, callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $period = ActionPeriod::tryFrom((string) $this->input('periode'));

            if ($period === null || $this->authorizedPlan === null) {
                return;
            }

            if ($this->authorizedPlan->realizations()->where('periode_ke', '>', $period->maximumPeriods())->exists()) {
                $validator->errors()->add(
                    'periode',
                    'Periode tidak dapat diubah karena ada realisasi yang tidak sesuai dengan frekuensi baru.',
                );
            }
        }];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return (new StoreRencanaAksiRequest)->messages();
    }
}
