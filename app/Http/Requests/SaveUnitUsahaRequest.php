<?php

namespace App\Http\Requests;

use App\Enums\StatusUnitUsaha;
use App\Models\Bumdes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveUnitUsahaRequest extends FormRequest
{
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
            'nama' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::enum(StatusUnitUsaha::class)],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['nama' => trim((string) $this->input('nama')), 'deskripsi' => filled($this->input('deskripsi')) ? trim((string) $this->input('deskripsi')) : null]);
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['nama.required' => 'Nama unit usaha wajib diisi.', 'status.enum' => 'Status unit usaha tidak valid.'];
    }
}
