@php($selectedPeriod = old('periode', isset($actionPlan) ? $actionPlan->periode->value : \App\Enums\ActionPeriod::Monthly->value))

<div class="grid gap-6 lg:grid-cols-2">
    <div>
        <label for="tahun" class="form-label">Tahun <span class="text-red-700">*</span></label>
        <input id="tahun" name="tahun" type="number" min="2000" max="{{ now()->year + 10 }}" value="{{ old('tahun', $actionPlan->tahun ?? $defaultYear ?? now()->year) }}" class="form-input" required>
        <x-input-error name="tahun" />
    </div>
    <div>
        <label for="periode" class="form-label">Periode pemantauan <span class="text-red-700">*</span></label>
        <select id="periode" name="periode" class="form-input" required>
            @foreach ($periodOptions as $period)
                <option value="{{ $period->value }}" @selected($selectedPeriod === $period->value)>{{ $period->label() }}</option>
            @endforeach
        </select>
        <x-input-error name="periode" />
    </div>
    <div class="lg:col-span-2">
        <label for="program" class="form-label">Program <span class="text-red-700">*</span></label>
        <input id="program" name="program" value="{{ old('program', $actionPlan->program ?? '') }}" class="form-input" maxlength="255" required>
        <x-input-error name="program" />
    </div>
    <div class="lg:col-span-2">
        <label for="kegiatan" class="form-label">Kegiatan <span class="text-red-700">*</span></label>
        <input id="kegiatan" name="kegiatan" value="{{ old('kegiatan', $actionPlan->kegiatan ?? '') }}" class="form-input" maxlength="255" required>
        <x-input-error name="kegiatan" />
    </div>
    <div>
        <label for="target_nilai" class="form-label">Target numerik</label>
        <input id="target_nilai" name="target_nilai" type="number" min="0" step="0.01" value="{{ old('target_nilai', $actionPlan->target_nilai ?? '') }}" class="form-input" inputmode="decimal" placeholder="Contoh: 100">
        <x-input-error name="target_nilai" />
    </div>
    <div>
        <label for="target_satuan" class="form-label">Satuan target</label>
        <input id="target_satuan" name="target_satuan" value="{{ old('target_satuan', $actionPlan->target_satuan ?? '') }}" class="form-input" maxlength="100" placeholder="unit, peserta, rupiah">
        <x-input-error name="target_satuan" />
    </div>
    <div class="lg:col-span-2">
        <label for="target_deskripsi" class="form-label">Deskripsi target</label>
        <textarea id="target_deskripsi" name="target_deskripsi" rows="4" class="form-input" maxlength="5000" placeholder="Gunakan untuk target kualitatif atau keterangan tambahan.">{{ old('target_deskripsi', $actionPlan->target_deskripsi ?? '') }}</textarea>
        <x-input-error name="target_deskripsi" />
        <p class="mt-2 text-xs leading-5 text-stone-500">Target tanpa nilai numerik tidak menghasilkan persentase; progresnya ditentukan melalui status periode.</p>
    </div>
    <div>
        <label for="tanggal_mulai" class="form-label">Tanggal mulai</label>
        <input id="tanggal_mulai" name="tanggal_mulai" type="date" value="{{ old('tanggal_mulai', isset($actionPlan) ? $actionPlan->tanggal_mulai?->format('Y-m-d') : '') }}" class="form-input">
        <x-input-error name="tanggal_mulai" />
    </div>
    <div>
        <label for="tanggal_selesai" class="form-label">Tanggal selesai</label>
        <input id="tanggal_selesai" name="tanggal_selesai" type="date" value="{{ old('tanggal_selesai', isset($actionPlan) ? $actionPlan->tanggal_selesai?->format('Y-m-d') : '') }}" class="form-input">
        <x-input-error name="tanggal_selesai" />
    </div>
</div>
