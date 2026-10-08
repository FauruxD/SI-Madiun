@php($layers = ['kawasan' => 'Kawasan', 'bumdes' => 'Kantor BUMDes', 'informasi' => 'Titik informasi', 'potensi' => 'Potensi desa'])
<section data-map-root data-tile-url="{{ config('map.tile_url') }}" data-tile-attribution="{{ config('map.tile_attribution') }}" class="min-w-0 border border-stone-200 bg-white" aria-label="Peta interaktif kawasan perdesaan">
    <script type="application/json" data-map-collection>{!! json_encode($collection, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) !!}</script>
    <fieldset class="flex flex-wrap gap-x-6 gap-y-1 border-b border-stone-200 px-4 py-3">
        <legend class="sr-only">Layer yang ditampilkan</legend>
        @foreach($layers as $key => $label)
            <label class="inline-flex min-h-11 cursor-pointer items-center gap-2 text-sm font-semibold"><input type="checkbox" data-map-layer="{{ $key }}" checked class="size-4 accent-emerald-800"><span>{{ $label }}</span><span class="text-xs font-normal text-stone-500">{{ $collection['meta']['counts'][$key] }}</span></label>
        @endforeach
    </fieldset>
    <div class="grid min-w-0 lg:grid-cols-[minmax(0,1fr)_19rem]">
        <div class="min-w-0">
            <div data-map-canvas class="geo-map relative isolate h-[380px] bg-stone-200 sm:h-[500px]" role="region" aria-label="Peta lokasi; gunakan tombol panah dan plus atau minus saat fokus"></div>
            <p data-map-status role="status" class="border-t border-stone-200 px-4 py-3 text-xs leading-5 text-stone-600">Memuat peta… Daftar lokasi tetap dapat dibaca di bawah.</p>
            <p data-map-tile-error hidden role="status" class="border-t border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-900">Peta dasar tidak tersedia. Layer lokasi dan daftar informasi tetap dapat digunakan.</p>
        </div>
        <div class="min-w-0 border-t border-stone-200 lg:border-t-0 lg:border-l">
            <div class="border-b border-stone-200 px-4 py-4"><h2 class="text-sm font-bold">Lokasi ditampilkan</h2><p class="mt-1 text-xs leading-5 text-stone-500">Pilih lokasi untuk melihatnya pada peta.</p></div>
            <ul class="max-h-[26rem] overflow-y-auto divide-y divide-stone-100">
                @foreach($collection['features'] as $feature)
                    <li data-map-item="{{ $feature['id'] }}" data-feature-layer="{{ $feature['properties']['layer'] }}" class="px-4 py-4">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-800">{{ $layers[$feature['properties']['layer']] }}</p>
                        <h3 class="mt-1 text-sm font-semibold break-words">{{ $feature['properties']['name'] }}</h3>
                        <p class="mt-1 text-xs leading-5 break-words text-stone-500">{{ $feature['properties']['summary'] }}</p>
                        @if(count($feature['properties']['categories']) > 1)<p class="mt-1 text-xs text-stone-500">Multi-kategori · seluruh kategori tercantum di atas.</p>@endif
                        <div class="mt-2 flex flex-wrap items-center gap-3">
                            <button type="button" data-map-focus="{{ $feature['id'] }}" disabled class="min-h-11 text-xs font-bold text-emerald-800 underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-emerald-800 disabled:text-stone-400">Lihat di peta</button>
                            @if($feature['properties']['url'])<a href="{{ $feature['properties']['url'] }}" class="inline-flex min-h-11 items-center text-xs font-bold text-emerald-800 underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-emerald-800">{{ $feature['properties']['layer'] === 'bumdes' ? 'Website BUMDes' : 'Detail potensi' }} →</a>@endif
                        </div>
                    </li>
                @endforeach
            </ul>
            <p data-map-empty @if(count($collection['features'])) hidden @endif class="px-4 py-8 text-sm leading-6 text-stone-500">Belum ada lokasi pada layer yang dipilih.</p>
        </div>
    </div>
    @if(count($collection['meta']['truncated']))
        <p class="border-t border-amber-200 bg-amber-50 px-4 py-3 text-xs leading-5 text-amber-900">Peta dibatasi {{ config('map.max_features_per_layer') }} lokasi per layer. Data lainnya belum ditampilkan.</p>
    @endif
    <div class="border-t border-stone-200 px-4 py-3">
        @php($mapCategories = collect($collection['features'])->flatMap(fn ($feature) => $feature['properties']['categories'])->unique('name')->sortBy('name'))
        @if($mapCategories->isNotEmpty())
            <ul aria-label="Legenda kategori kawasan" class="mb-2 flex flex-wrap gap-x-5 gap-y-2">
                @foreach($mapCategories as $category)<li class="flex items-center gap-2 text-xs text-stone-700"><span class="size-3 border border-black/10" style="background-color: {{ $category['color'] }}" aria-hidden="true"></span>{{ $category['name'] }}</li>@endforeach
            </ul>
        @endif
        <p class="text-xs leading-5 text-stone-500">B = BUMDes · I = informasi · P = potensi. Warna batas mengikuti kategori pertama secara alfabetis untuk tampilan, bukan prioritas kawasan; garis putus-putus menandai multi-kategori.</p>
    </div>
</section>
