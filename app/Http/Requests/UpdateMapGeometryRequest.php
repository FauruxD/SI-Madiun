<?php

namespace App\Http\Requests;

use App\Rules\GeoJsonGeometry;
use App\Services\MapEditorScope;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMapGeometryRequest extends FormRequest
{
    public function authorize(): bool
    {
        app(MapEditorScope::class)->find((string) $this->route('kind'), (int) $this->route('id'), $this->user());

        return true;
    }

    public function rules(): array
    {
        return [
            'geometri' => ['present', 'nullable', new GeoJsonGeometry($this->route('kind') === 'kawasan' ? 'Polygon' : 'Point')],
            'status' => ['prohibited'],
            'published_at' => ['prohibited'],
            'desa_id' => ['prohibited'],
            'bumdes_id' => ['prohibited'],
            'is_active' => ['prohibited'],
            'kawasan_id' => ['prohibited'],
        ];
    }
}
