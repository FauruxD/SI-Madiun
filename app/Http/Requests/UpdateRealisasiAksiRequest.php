<?php

namespace App\Http\Requests;

use App\Enums\AchievementStatus;
use App\Models\Bumdes;
use App\Models\RencanaAksi;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRealisasiAksiRequest extends FormRequest
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
        $this->authorizedPlan->realizations()->findOrFail((int) $this->route('realization'));

        return $user->can('manageRealizations', $this->authorizedPlan);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $actionPlan = $this->authorizedPlan ?? RencanaAksi::query()->findOrFail((int) $this->route('rencanaAksi'));

        return [
            'periode_ke' => [
                'required',
                'integer',
                Rule::in(range(1, $actionPlan->periode->maximumPeriods())),
                Rule::unique('realisasi_aksi', 'periode_ke')
                    ->where('rencana_aksi_id', $actionPlan->id)
                    ->ignore((int) $this->route('realization')),
            ],
            'realisasi_nilai' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'catatan' => ['nullable', 'string', 'max:5000'],
            'tanggal_realisasi' => ['nullable', 'date'],
            'status' => [
                'required',
                Rule::enum(AchievementStatus::class)->except([AchievementStatus::NotStarted]),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'realisasi_nilai' => filled($this->input('realisasi_nilai')) ? $this->input('realisasi_nilai') : null,
            'catatan' => filled($this->input('catatan')) ? trim((string) $this->input('catatan')) : null,
            'tanggal_realisasi' => filled($this->input('tanggal_realisasi')) ? $this->input('tanggal_realisasi') : null,
        ]);
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return (new StoreRealisasiAksiRequest)->messages();
    }
}
