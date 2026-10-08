<?php

namespace App\Http\Requests;

use App\Enums\JenisDokumenBumdes;
use App\Models\Bumdes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StoreDokumenBumdesRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if ($user === null) {
            return false;
        }

        $bumdes = Bumdes::query()->visibleTo($user)->findOrFail((int) $this->route('bumdes'));

        return $user->can('manageDocuments', $bumdes);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'jenis_dokumen' => ['required', Rule::enum(JenisDokumenBumdes::class)],
            'nomor_dokumen' => ['nullable', 'string', 'max:255'],
            'tanggal_dokumen' => ['nullable', 'date_format:Y-m-d'],
            'ringkasan_maksud_tujuan' => ['nullable', 'string', 'max:5000'],
            'ringkasan_modal_awal' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'ringkasan_pembagian_shu' => ['nullable', 'string', 'max:5000'],
            'file' => [
                'required',
                File::types(config('bumdes.documents.allowed_types'))
                    ->max(config('bumdes.documents.max_size_kb').'kb'),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [];

        foreach (['nomor_dokumen', 'ringkasan_maksud_tujuan', 'ringkasan_pembagian_shu'] as $field) {
            $data[$field] = filled($this->input($field)) ? trim((string) $this->input($field)) : null;
        }

        $data['tanggal_dokumen'] = filled($this->input('tanggal_dokumen')) ? $this->input('tanggal_dokumen') : null;
        $data['ringkasan_modal_awal'] = filled($this->input('ringkasan_modal_awal')) ? $this->input('ringkasan_modal_awal') : null;
        $this->merge($data);
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'file.required' => 'File AD/ART atau legalitas wajib dipilih.',
            'file.mimes' => 'File AD/ART harus berupa PDF, DOC, atau DOCX yang valid.',
            'file.max' => 'Ukuran dokumen melebihi batas yang dikonfigurasi.',
            'ringkasan_modal_awal.decimal' => 'Modal awal maksimal menggunakan dua angka desimal.',
        ];
    }
}
