<?php

namespace App\Http\Requests;

use App\Models\Bumdes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class StoreGaleriBumdesRequest extends FormRequest
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
            'judul' => ['nullable', 'string', 'max:255'],
            'urutan' => ['required', 'integer', 'min:0', 'max:10000'],
            'file' => ['required', File::image()->types(config('bumdes.images.allowed_types'))->max(config('bumdes.images.max_size_kb').'kb')],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['judul' => filled($this->input('judul')) ? trim((string) $this->input('judul')) : null]);
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['file.required' => 'Foto galeri wajib dipilih.', 'file.image' => 'Galeri hanya mendukung gambar yang valid pada Phase 3.'];
    }
}
