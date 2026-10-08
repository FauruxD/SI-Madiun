<?php

namespace App\Http\Requests;

use App\Models\Bumdes;
use Illuminate\Foundation\Http\FormRequest;

class SaveAsetBumdesRequest extends FormRequest
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
            'nama' => ['required', 'string', 'max:255'],
            'jenis' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:5000'],
            'nilai_perkiraan' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'status_kepemilikan' => ['required', 'string', 'max:255'],
            'kondisi' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [];

        foreach (['nama', 'jenis', 'status_kepemilikan'] as $field) {
            $data[$field] = trim((string) $this->input($field));
        }

        foreach (['deskripsi', 'kondisi'] as $field) {
            $data[$field] = filled($this->input($field)) ? trim((string) $this->input($field)) : null;
        }

        $data['nilai_perkiraan'] = filled($this->input('nilai_perkiraan')) ? $this->input('nilai_perkiraan') : null;
        $this->merge($data);
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['nama.required' => 'Nama aset wajib diisi.', 'jenis.required' => 'Jenis aset wajib diisi.', 'status_kepemilikan.required' => 'Status kepemilikan aset wajib diisi.', 'nilai_perkiraan.decimal' => 'Nilai perkiraan maksimal menggunakan dua angka desimal.'];
    }
}
