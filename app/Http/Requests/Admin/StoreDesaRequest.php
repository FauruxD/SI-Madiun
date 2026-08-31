<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreDesaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'kecamatan_id' => ['required', 'integer', 'exists:kecamatan,id'],
            'kode' => ['required', 'string', 'max:20', 'unique:desa,kode'],
            'nama' => ['required', 'string', 'max:255'],
        ];
    }
}
