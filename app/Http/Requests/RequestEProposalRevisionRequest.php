<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RequestEProposalRevisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return ['catatan' => ['required', 'string', 'min:3', 'max:5000']];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['catatan' => trim((string) $this->input('catatan'))]);
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'catatan.required' => 'Catatan revisi wajib diisi.',
            'catatan.min' => 'Catatan revisi harus menjelaskan perbaikan yang diperlukan.',
        ];
    }
}
