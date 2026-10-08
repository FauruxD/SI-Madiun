<?php

namespace App\Http\Requests\Admin;

use App\Enums\JenisDokumenKawasan;
use App\Models\KawasanPerdesaan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StoreDokumenKawasanRequest extends FormRequest
{
    public function authorize(): bool
    {
        $kawasan = $this->route('kawasan');

        return $kawasan instanceof KawasanPerdesaan && ($this->user()?->can('manageDocuments', $kawasan) ?? false);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'jenis_dokumen' => ['required', Rule::enum(JenisDokumenKawasan::class)],
            'nomor_dokumen' => ['nullable', 'string', 'max:255'],
            'tanggal_dokumen' => ['nullable', 'date_format:Y-m-d'],
            'file' => [
                'required',
                File::types(config('kawasan.documents.allowed_types'))
                    ->max(config('kawasan.documents.max_size_kb').'kb'),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nomor_dokumen' => filled($this->input('nomor_dokumen')) ? trim((string) $this->input('nomor_dokumen')) : null,
            'tanggal_dokumen' => filled($this->input('tanggal_dokumen')) ? $this->input('tanggal_dokumen') : null,
        ]);
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'file.required' => 'File dokumen wajib dipilih.',
            'file.mimes' => 'Dokumen harus berupa PDF, DOC, atau DOCX yang valid.',
            'file.max' => 'Ukuran dokumen melebihi batas yang dikonfigurasi.',
        ];
    }
}
