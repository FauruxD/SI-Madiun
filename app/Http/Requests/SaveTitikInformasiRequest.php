<?php

namespace App\Http\Requests;

use App\Models\KawasanPerdesaan;
use App\Models\TitikInformasi;
use App\Rules\GeoJsonGeometry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class SaveTitikInformasiRequest extends FormRequest
{
    private ?TitikInformasi $point = null;

    public function authorize(): bool
    {
        $kawasan = KawasanPerdesaan::query()->visibleTo($this->user())->findOrFail((int) $this->route('kawasan'));
        Gate::authorize('manageMap', $kawasan);
        if ($this->route('point') !== null) {
            $this->point = $kawasan->informationPoints()->findOrFail((int) $this->route('point'));
        }

        return true;
    }

    public function rules(): array
    {
        $unique = Rule::unique('titik_informasi', 'nama')->where('kawasan_id', (int) $this->route('kawasan'))
            ->where('jenis', is_string($this->input('jenis')) ? $this->input('jenis') : '');
        if ($this->point !== null) {
            $unique->ignore($this->point);
        }

        return [
            'nama' => ['bail', 'required', 'string', 'max:120', $unique],
            'jenis' => ['bail', 'required', 'string', Rule::in(array_keys(TitikInformasi::TYPES))],
            'deskripsi' => ['nullable', 'string', 'max:500'],
            'geometri' => ['required', new GeoJsonGeometry],
            'is_active' => ['required', 'boolean'],
            'kawasan_id' => ['prohibited'],
            'status' => ['prohibited'],
        ];
    }
}
