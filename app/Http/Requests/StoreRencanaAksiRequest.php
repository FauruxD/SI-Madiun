<?php

namespace App\Http\Requests;

use App\Enums\ActionPeriod;
use App\Models\Bumdes;
use App\Models\RencanaAksi;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRencanaAksiRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if ($user === null) {
            return false;
        }

        $bumdes = Bumdes::query()->visibleTo($user)->findOrFail((int) $this->route('bumdes'));

        return $user->can('create', [RencanaAksi::class, $bumdes]);
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

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'tahun.required' => 'Tahun rencana wajib diisi.',
            'tahun.digits' => 'Tahun rencana harus terdiri dari empat angka.',
            'tahun.min' => 'Tahun rencana paling awal adalah 2000.',
            'tahun.max' => 'Tahun rencana paling jauh sepuluh tahun dari tahun berjalan.',
            'program.required' => 'Program wajib diisi.',
            'kegiatan.required' => 'Kegiatan wajib diisi.',
            'target_nilai.numeric' => 'Target nilai harus berupa angka.',
            'target_nilai.min' => 'Target nilai tidak boleh negatif.',
            'target_nilai.decimal' => 'Target nilai maksimal menggunakan dua angka desimal.',
            'periode.required' => 'Periode pemantauan wajib dipilih.',
            'periode.enum' => 'Periode pemantauan tidak valid.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh mendahului tanggal mulai.',
        ];
    }
}
