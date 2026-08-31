@if (session('status'))
    <div class="mb-6 flex items-start gap-3 border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900" role="status">
        <span class="mt-0.5 size-2 shrink-0 rounded-full bg-emerald-600"></span>
        <p>{{ session('status') }}</p>
    </div>
@endif

@if ($errors->any())
    <div class="mb-6 border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900" role="alert">
        <p class="font-semibold">Periksa kembali data yang Anda masukkan.</p>
        <ul class="mt-2 list-disc space-y-1 pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
