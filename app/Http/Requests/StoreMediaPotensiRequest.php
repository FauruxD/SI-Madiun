<?php

namespace App\Http\Requests;

use App\Enums\TipeMediaPotensi;
use App\Models\PotensiDesa;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StoreMediaPotensiRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if ($user === null) {
            return false;
        }

        $potential = PotensiDesa::query()->visibleTo($user)->findOrFail((int) $this->route('potential'));

        return $user->can('manageMedia', $potential);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $isImage = $this->input('tipe_media') === TipeMediaPotensi::Image->value;
        $isVideo = $this->input('tipe_media') === TipeMediaPotensi::Video->value;

        return [
            'tipe_media' => ['required', Rule::enum(TipeMediaPotensi::class)],
            'judul' => ['nullable', 'string', 'max:255'],
            'urutan' => ['required', 'integer', 'min:0', 'max:10000'],
            'file' => [
                Rule::requiredIf($isImage),
                Rule::prohibitedIf($isVideo),
                File::image()->types(config('potensi.images.allowed_types'))->max(config('potensi.images.max_size_kb').'kb'),
            ],
            'media_url' => [
                Rule::requiredIf($isVideo),
                Rule::prohibitedIf($isImage),
                'nullable',
                'url:http,https',
                'max:2048',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'judul' => filled($this->input('judul')) ? trim((string) $this->input('judul')) : null,
            'media_url' => filled($this->input('media_url')) ? trim((string) $this->input('media_url')) : null,
        ]);
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'tipe_media.required' => 'Tipe media wajib dipilih.',
            'tipe_media.enum' => 'Tipe media tidak valid.',
            'file.required' => 'Foto potensi wajib dipilih.',
            'file.image' => 'Format foto tidak didukung.',
            'file.mimes' => 'Format foto tidak didukung.',
            'file.max' => 'Ukuran foto melebihi batas yang dikonfigurasi.',
            'media_url.required' => 'URL video wajib diisi.',
            'media_url.url' => 'Video harus berupa URL HTTP atau HTTPS yang valid.',
            'urutan.required' => 'Urutan media wajib diisi.',
        ];
    }
}
