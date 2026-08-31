<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreKecamatanRequest extends FormRequest
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
            'kabupaten_id' => ['required', 'integer', 'exists:kabupaten,id'],
            'kode' => ['required', 'string', 'max:15', 'unique:kecamatan,kode'],
            'nama' => ['required', 'string', 'max:255'],
        ];
    }
}
