<?php

namespace App\Http\Controllers;

use App\Enums\StatusUnitUsaha;
use App\Models\ProdukLayanan;
use App\Models\WebsiteBumdes;
use App\Services\BumdesImageService;
use App\Services\PublicPortalService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PublicBumdesWebsiteController extends Controller
{
    public function __construct(private BumdesImageService $images) {}

    public function home(Request $request, PublicPortalService $portal): View
    {
        $website = $request->attributes->get('bumdesWebsite');

        return $website instanceof WebsiteBumdes
            ? $this->show($request, $website->subdomain)
            : view('public.home', $portal->home());
    }

    public function show(Request $request, string $subdomain): View
    {
        $website = WebsiteBumdes::query()->active()->where('subdomain', $subdomain)->firstOrFail();
        $profile = $website->bumdes()->with([
            'desas' => fn ($query) => $query->with('kecamatan.kabupaten')->orderByPivot('is_desa_utama', 'desc')->orderBy('nama'),
            'managers' => fn ($query) => $query->select(['id', 'bumdes_id', 'nama', 'jabatan', 'urutan'])->where('is_active', true)->orderBy('urutan')->orderBy('id'),
        ])->firstOrFail();

        $products = ProdukLayanan::query()->where('is_active', true)
            ->whereHas('businessUnit', fn ($query) => $query->where('bumdes_id', $profile->id)->where('status', StatusUnitUsaha::Aktif))
            ->with('businessUnit:id,nama,bumdes_id')->orderBy('nama')->orderBy('id')
            ->paginate(12, ['*'], 'products_page')->withQueryString();
        $gallery = $profile->gallery()->orderBy('urutan')->orderBy('id')->paginate(8, ['*'], 'gallery_page')->withQueryString();
        $galleryImages = $gallery->getCollection()->filter(fn ($image): bool => $this->images->read($profile->id, 'gallery', $image->file_path) !== null);
        $productImages = $products->getCollection()->filter(fn ($product): bool => $this->images->read($profile->id, 'product', $product->foto_path) !== null)->modelKeys();
        $isHostWebsite = $request->attributes->get('bumdesWebsite') instanceof WebsiteBumdes;
        $mediaUrl = fn (string $kind, int $media): string => $isHostWebsite
            ? route('public.bumdes.host-media', compact('kind', 'media'), false)
            : route('public.bumdes.media', ['subdomain' => $website->subdomain, ...compact('kind', 'media')], false);

        return view('public.bumdes.show', compact('website', 'profile', 'products', 'gallery', 'galleryImages', 'productImages', 'mediaUrl'));
    }

    public function hostImage(Request $request, string $kind, int $media): Response
    {
        $website = $request->attributes->get('bumdesWebsite');
        abort_unless($website instanceof WebsiteBumdes, 404);

        return $this->image($request, $website->subdomain, $kind, $media);
    }

    public function image(Request $request, string $subdomain, string $kind, int $media): Response
    {
        $website = WebsiteBumdes::query()->active()->where('subdomain', $subdomain)->firstOrFail();
        $profile = $website->bumdes()->firstOrFail();
        $path = match ($kind) {
            'gallery' => $profile->gallery()->findOrFail($media)->file_path,
            'product' => ProdukLayanan::query()->where('is_active', true)->whereHas('businessUnit', fn ($query) => $query->where('bumdes_id', $profile->id)->where('status', StatusUnitUsaha::Aktif))->findOrFail($media)->foto_path,
            default => null,
        };
        $image = $this->images->read($profile->id, $kind, $path);
        abort_if($image === null, 404);

        return response($image['contents'], 200, [
            'Content-Type' => $image['mime'],
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
