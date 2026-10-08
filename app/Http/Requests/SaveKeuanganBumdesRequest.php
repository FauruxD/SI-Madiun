<?php

namespace App\Http\Requests;

use App\Models\Bumdes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveKeuanganBumdesRequest extends FormRequest
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
            'tahun' => [
                'required', 'integer', 'digits:4',
                Rule::unique('keuangan_bumdes', 'tahun')
                    ->where('bumdes_id', (int) $this->route('bumdes'))
                    ->ignore((int) $this->route('keuangan')),
            ],
            'modal_awal' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'omzet' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'shu' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'kontribusi_pades' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [];

        foreach (['modal_awal', 'omzet', 'shu', 'kontribusi_pades'] as $field) {
            $data[$field] = filled($this->input($field)) ? $this->input($field) : null;
        }

        $this->merge($data);
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'tahun.required' => 'Tahun keuangan wajib diisi.',
            'tahun.unique' => 'Data keuangan untuk tahun tersebut sudah tersedia.',
            'modal_awal.decimal' => 'Modal awal maksimal menggunakan dua angka desimal.',
            'omzet.decimal' => 'Omzet maksimal menggunakan dua angka desimal.',
            'shu.decimal' => 'SHU maksimal menggunakan dua angka desimal.',
            'kontribusi_pades.decimal' => 'Kontribusi PADes maksimal menggunakan dua angka desimal.',
        ];
    }
}
