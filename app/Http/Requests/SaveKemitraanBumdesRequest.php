<?php

namespace App\Http\Requests;

use App\Enums\StatusKemitraan;
use App\Models\Bumdes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveKemitraanBumdesRequest extends FormRequest
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
            'nama_mitra' => ['nullable', 'required_if:status,'.StatusKemitraan::Berjalan->value, 'string', 'max:255'],
            'jenis_kemitraan' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::enum(StatusKemitraan::class)],
            'kebutuhan_investasi' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nama_mitra' => filled($this->input('nama_mitra')) ? trim((string) $this->input('nama_mitra')) : null,
            'jenis_kemitraan' => trim((string) $this->input('jenis_kemitraan')),
            'deskripsi' => filled($this->input('deskripsi')) ? trim((string) $this->input('deskripsi')) : null,
            'kebutuhan_investasi' => filled($this->input('kebutuhan_investasi')) ? $this->input('kebutuhan_investasi') : null,
        ]);
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['nama_mitra.required_if' => 'Nama mitra wajib diisi untuk kemitraan yang sudah berjalan.', 'jenis_kemitraan.required' => 'Jenis kemitraan wajib diisi.', 'status.enum' => 'Status kemitraan tidak valid.', 'kebutuhan_investasi.decimal' => 'Kebutuhan investasi maksimal menggunakan dua angka desimal.'];
    }
}
