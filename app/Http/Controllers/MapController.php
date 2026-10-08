<?php

namespace App\Http\Controllers;

use App\Services\MapFeatureService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class MapController extends Controller
{
    public function index(MapFeatureService $features): View
    {
        return view('maps.public', ['collection' => $features->collection()]);
    }

    public function data(MapFeatureService $features): JsonResponse
    {
        return response()->json($features->collection())->header('Cache-Control', 'no-store');
    }
}
