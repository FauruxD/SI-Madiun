<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResubmitEProposalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return ['catatan_pengaju' => ['nullable', 'string', 'max:5000']];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['catatan_pengaju' => filled($this->input('catatan_pengaju')) ? trim((string) $this->input('catatan_pengaju')) : null]);
    }
}
