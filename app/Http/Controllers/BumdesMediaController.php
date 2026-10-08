<?php

namespace App\Http\Controllers;

use App\Models\Bumdes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BumdesMediaController extends Controller
{
    public function manager(Request $request, int $bumdes, int $manager): StreamedResponse
    {
        $profile = $this->visibleBumdes($request, $bumdes);
        $record = $profile->managers()->whereNotNull('foto_path')->findOrFail($manager);

        return Storage::disk(config('bumdes.images.disk'))->response($record->foto_path);
    }

    public function product(Request $request, int $bumdes, int $unitUsaha, int $product): StreamedResponse
    {
        $profile = $this->visibleBumdes($request, $bumdes);
        $unit = $profile->businessUnits()->findOrFail($unitUsaha);
        $record = $unit->products()->whereNotNull('foto_path')->findOrFail($product);

        return Storage::disk(config('bumdes.images.disk'))->response($record->foto_path);
    }

    public function gallery(Request $request, int $bumdes, int $gallery): StreamedResponse
    {
        $profile = $this->visibleBumdes($request, $bumdes);
        $record = $profile->gallery()->findOrFail($gallery);

        return Storage::disk(config('bumdes.images.disk'))->response($record->file_path, $record->nama_file, ['Content-Type' => $record->mime_type]);
    }

    private function visibleBumdes(Request $request, int $id): Bumdes
    {
        $user = $request->user();
        abort_if($user === null, 401);
        $bumdes = Bumdes::query()->visibleTo($user)->findOrFail($id);
        Gate::authorize('view', $bumdes);

        return $bumdes;
    }
}
