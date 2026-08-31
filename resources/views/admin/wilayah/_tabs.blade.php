<nav class="mt-7 flex gap-1 overflow-x-auto border-b border-stone-300" aria-label="Master wilayah">
    <a href="{{ route('admin.kabupaten.index') }}" @class(['tab-link', 'tab-link-active' => request()->routeIs('admin.kabupaten.*')])>Kabupaten</a>
    <a href="{{ route('admin.kecamatan.index') }}" @class(['tab-link', 'tab-link-active' => request()->routeIs('admin.kecamatan.*')])>Kecamatan</a>
    <a href="{{ route('admin.desa.index') }}" @class(['tab-link', 'tab-link-active' => request()->routeIs('admin.desa.*')])>Desa</a>
</nav>
