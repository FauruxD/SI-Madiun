<?php

use App\Http\Controllers\Admin\DesaController;
use App\Http\Controllers\Admin\DokumenKawasanController as AdminDokumenKawasanController;
use App\Http\Controllers\Admin\KabupatenController;
use App\Http\Controllers\Admin\KategoriKawasanController;
use App\Http\Controllers\Admin\KategoriPotensiController;
use App\Http\Controllers\Admin\KawasanController as AdminKawasanController;
use App\Http\Controllers\Admin\KawasanOptionController;
use App\Http\Controllers\Admin\KecamatanController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AsetBumdesController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\BumdesController;
use App\Http\Controllers\BumdesMediaController;
use App\Http\Controllers\BumdesWebsiteController;
use App\Http\Controllers\CompanyProfileDownloadController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DokumenBumdesController;
use App\Http\Controllers\DokumenKawasanController;
use App\Http\Controllers\EProposalController;
use App\Http\Controllers\EProposalDownloadController;
use App\Http\Controllers\EProposalNotificationController;
use App\Http\Controllers\EProposalReviewController;
use App\Http\Controllers\EProposalWorkflowController;
use App\Http\Controllers\GaleriBumdesController;
use App\Http\Controllers\KawasanController;
use App\Http\Controllers\KemitraanBumdesController;
use App\Http\Controllers\KeuanganBumdesController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\MapGeometryController;
use App\Http\Controllers\PengurusBumdesController;
use App\Http\Controllers\PotensiDesaController;
use App\Http\Controllers\PotensiMediaController;
use App\Http\Controllers\ProdukLayananController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicAboutController;
use App\Http\Controllers\PublicBumdesDirectoryController;
use App\Http\Controllers\PublicBumdesWebsiteController;
use App\Http\Controllers\PublicKawasanController;
use App\Http\Controllers\PublicPotensiCatalogController;
use App\Http\Controllers\PublicPotensiMediaController;
use App\Http\Controllers\RealisasiAksiController;
use App\Http\Controllers\RencanaAksiController;
use App\Http\Controllers\TitikInformasiController;
use App\Http\Controllers\UnitUsahaController;
use App\UserRole;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicBumdesWebsiteController::class, 'home'])->name('public.home');
Route::get('/media/{kind}/{media}', [PublicBumdesWebsiteController::class, 'hostImage'])
    ->whereIn('kind', ['gallery', 'product'])->whereNumber('media')->name('public.bumdes.host-media');
Route::get('/website-bumdes/{subdomain}', [PublicBumdesWebsiteController::class, 'show'])
    ->where('subdomain', '[a-z0-9-]+')->name('public.bumdes.show');
Route::get('/website-bumdes/{subdomain}/media/{kind}/{media}', [PublicBumdesWebsiteController::class, 'image'])
    ->where('subdomain', '[a-z0-9-]+')->whereIn('kind', ['gallery', 'product'])->whereNumber('media')->name('public.bumdes.media');
Route::get('/website-bumdes/{subdomain}/company-profile', [CompanyProfileDownloadController::class, 'public'])
    ->where('subdomain', '[a-z0-9-]+')->name('public.bumdes.company-profile');

Route::get('/katalog-potensi', [PublicPotensiCatalogController::class, 'index'])->name('katalog-potensi.index');
Route::get('/kawasan-perdesaan', [PublicKawasanController::class, 'index'])->name('public.kawasan.index');
Route::get('/kawasan-perdesaan/{kawasan}', [PublicKawasanController::class, 'show'])->whereNumber('kawasan')->name('public.kawasan.show');
Route::get('/direktori-bumdes', [PublicBumdesDirectoryController::class, 'index'])->name('public.bumdes.index');
Route::get('/peta', [MapController::class, 'index'])->name('peta.index');
Route::get('/peta/data', [MapController::class, 'data'])->name('peta.data');
Route::get('/katalog-potensi/{potential}', [PublicPotensiCatalogController::class, 'show'])->whereNumber('potential')->name('katalog-potensi.show');
Route::get('/katalog-potensi/{potential}/media/{media}', PublicPotensiMediaController::class)
    ->whereNumber(['potential', 'media'])
    ->name('katalog-potensi.media');
Route::get('/proposal/{proposal}/download', [EProposalDownloadController::class, 'public'])
    ->whereNumber('proposal')
    ->middleware('throttle:20,1')
    ->name('public.proposal.download');
Route::get('/tentang', PublicAboutController::class)->name('public.about');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login');

    Route::get('/lupa-kata-sandi', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/lupa-kata-sandi', [PasswordResetLinkController::class, 'store'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/atur-ulang-kata-sandi/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/atur-ulang-kata-sandi', [NewPasswordController::class, 'store'])->name('password.update');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/peta/kelola', [MapGeometryController::class, 'index'])->name('peta.manage');
    Route::get('/peta/kelola/data', [MapGeometryController::class, 'data'])->name('peta.manage.data');
    Route::get('/peta/kelola/{kind}/{id}', [MapGeometryController::class, 'edit'])->whereIn('kind', ['kawasan', 'bumdes', 'potensi'])->whereNumber('id')->name('peta.geometry.edit');
    Route::put('/peta/kelola/{kind}/{id}', [MapGeometryController::class, 'update'])->whereIn('kind', ['kawasan', 'bumdes', 'potensi'])->whereNumber('id')->name('peta.geometry.update');
    Route::get('/peta/kawasan/{kawasan}/titik-informasi/create', [TitikInformasiController::class, 'create'])->whereNumber('kawasan')->name('peta.information.create');
    Route::post('/peta/kawasan/{kawasan}/titik-informasi', [TitikInformasiController::class, 'store'])->whereNumber('kawasan')->name('peta.information.store');
    Route::get('/peta/kawasan/{kawasan}/titik-informasi/{point}/edit', [TitikInformasiController::class, 'edit'])->whereNumber(['kawasan', 'point'])->name('peta.information.edit');
    Route::put('/peta/kawasan/{kawasan}/titik-informasi/{point}', [TitikInformasiController::class, 'update'])->whereNumber(['kawasan', 'point'])->name('peta.information.update');
    Route::get('/bumdes/{bumdes}/website', [BumdesWebsiteController::class, 'edit'])->whereNumber('bumdes')->name('bumdes.website.edit');
    Route::put('/bumdes/{bumdes}/website', [BumdesWebsiteController::class, 'update'])->whereNumber('bumdes')->name('bumdes.website.update');
    Route::get('/bumdes/{bumdes}/company-profile', [CompanyProfileDownloadController::class, 'admin'])
        ->whereNumber('bumdes')->middleware('throttle:20,1')->name('bumdes.company-profile.download');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::resource('potensi-desa', PotensiDesaController::class)
        ->parameters(['potensi-desa' => 'potential'])
        ->where(['potential' => '[0-9]+']);

    Route::get('/admin/eproposal', [EProposalController::class, 'index'])->name('admin.eproposal.index');
    Route::get('/admin/eproposal/create', [EProposalController::class, 'create'])->name('admin.eproposal.create');
    Route::post('/admin/eproposal', [EProposalController::class, 'store'])->name('admin.eproposal.store');
    Route::patch('/admin/eproposal-notifications/{notification}', [EProposalNotificationController::class, 'update'])->whereUuid('notification')->name('admin.eproposal.notifications.update');
    Route::get('/admin/eproposal/{proposal}', [EProposalController::class, 'show'])->whereNumber('proposal')->name('admin.eproposal.show');
    Route::post('/admin/eproposal/{proposal}/resubmit', [EProposalController::class, 'resubmit'])->whereNumber('proposal')->name('admin.eproposal.resubmit');
    Route::get('/admin/eproposal-review', [EProposalReviewController::class, 'index'])->name('admin.eproposal.review.index');
    Route::get('/admin/eproposal-review/{proposal}', [EProposalController::class, 'show'])->whereNumber('proposal')->name('admin.eproposal.review.show');
    Route::post('/admin/eproposal/{proposal}/request-revision', [EProposalWorkflowController::class, 'requestRevision'])->whereNumber('proposal')->name('admin.eproposal.request-revision');
    Route::post('/admin/eproposal/{proposal}/verify', [EProposalWorkflowController::class, 'verify'])->whereNumber('proposal')->name('admin.eproposal.verify');
    Route::post('/admin/eproposal/{proposal}/publish', [EProposalWorkflowController::class, 'publish'])->whereNumber('proposal')->name('admin.eproposal.publish');
    Route::get('/admin/eproposal/{proposal}/download', [EProposalDownloadController::class, 'admin'])->whereNumber('proposal')->name('admin.eproposal.download');
    Route::post('/potensi-desa/{potential}/media', [PotensiMediaController::class, 'store'])
        ->whereNumber('potential')
        ->name('potensi-desa.media.store');
    Route::get('/potensi-desa/{potential}/media/{media}', [PotensiMediaController::class, 'image'])
        ->whereNumber(['potential', 'media'])
        ->name('potensi-desa.media.image');
    Route::delete('/potensi-desa/{potential}/media/{media}', [PotensiMediaController::class, 'destroy'])
        ->whereNumber(['potential', 'media'])
        ->name('potensi-desa.media.destroy');

    Route::get('/kinerja/rencana-aksi', [RencanaAksiController::class, 'index'])->name('rencana-aksi.index');
    Route::get('/bumdes/{bumdes}/rencana-aksi/create', [RencanaAksiController::class, 'create'])->whereNumber('bumdes')->name('bumdes.rencana-aksi.create');
    Route::post('/bumdes/{bumdes}/rencana-aksi', [RencanaAksiController::class, 'store'])->whereNumber('bumdes')->name('bumdes.rencana-aksi.store');
    Route::get('/bumdes/{bumdes}/rencana-aksi/{rencanaAksi}', [RencanaAksiController::class, 'show'])->whereNumber(['bumdes', 'rencanaAksi'])->name('bumdes.rencana-aksi.show');
    Route::get('/bumdes/{bumdes}/rencana-aksi/{rencanaAksi}/edit', [RencanaAksiController::class, 'edit'])->whereNumber(['bumdes', 'rencanaAksi'])->name('bumdes.rencana-aksi.edit');
    Route::put('/bumdes/{bumdes}/rencana-aksi/{rencanaAksi}', [RencanaAksiController::class, 'update'])->whereNumber(['bumdes', 'rencanaAksi'])->name('bumdes.rencana-aksi.update');
    Route::delete('/bumdes/{bumdes}/rencana-aksi/{rencanaAksi}', [RencanaAksiController::class, 'destroy'])->whereNumber(['bumdes', 'rencanaAksi'])->name('bumdes.rencana-aksi.destroy');
    Route::post('/bumdes/{bumdes}/rencana-aksi/{rencanaAksi}/realisasi', [RealisasiAksiController::class, 'store'])->whereNumber(['bumdes', 'rencanaAksi'])->name('bumdes.rencana-aksi.realisasi.store');
    Route::put('/bumdes/{bumdes}/rencana-aksi/{rencanaAksi}/realisasi/{realization}', [RealisasiAksiController::class, 'update'])->whereNumber(['bumdes', 'rencanaAksi', 'realization'])->name('bumdes.rencana-aksi.realisasi.update');

    Route::get('/wilayah/desa/{desa}', [DesaController::class, 'show'])
        ->whereNumber('desa')
        ->name('desa.show');
    Route::get('/kawasan/{kawasan}', [KawasanController::class, 'show'])
        ->whereNumber('kawasan')
        ->name('kawasan.show');
    Route::get('/kawasan/{kawasan}/dokumen/{document}/download', [DokumenKawasanController::class, 'download'])
        ->whereNumber(['kawasan', 'document'])
        ->name('kawasan.dokumen.download');
    Route::resource('bumdes', BumdesController::class)
        ->parameters(['bumdes' => 'bumdes'])
        ->where(['bumdes' => '[0-9]+']);
    Route::post('/bumdes/{bumdes}/dokumen', [DokumenBumdesController::class, 'store'])
        ->whereNumber('bumdes')
        ->name('bumdes.dokumen.store');
    Route::get('/bumdes/{bumdes}/dokumen/{document}/download', [DokumenBumdesController::class, 'download'])
        ->whereNumber(['bumdes', 'document'])
        ->name('bumdes.dokumen.download');
    Route::delete('/bumdes/{bumdes}/dokumen/{document}', [DokumenBumdesController::class, 'destroy'])
        ->whereNumber(['bumdes', 'document'])
        ->name('bumdes.dokumen.destroy');
    Route::post('/bumdes/{bumdes}/pengurus', [PengurusBumdesController::class, 'store'])->whereNumber('bumdes')->name('bumdes.pengurus.store');
    Route::put('/bumdes/{bumdes}/pengurus/{manager}', [PengurusBumdesController::class, 'update'])->whereNumber(['bumdes', 'manager'])->name('bumdes.pengurus.update');
    Route::delete('/bumdes/{bumdes}/pengurus/{manager}', [PengurusBumdesController::class, 'destroy'])->whereNumber(['bumdes', 'manager'])->name('bumdes.pengurus.destroy');
    Route::get('/bumdes/{bumdes}/media/pengurus/{manager}', [BumdesMediaController::class, 'manager'])->whereNumber(['bumdes', 'manager'])->name('bumdes.media.pengurus');

    Route::post('/bumdes/{bumdes}/unit-usaha', [UnitUsahaController::class, 'store'])->whereNumber('bumdes')->name('bumdes.unit-usaha.store');
    Route::put('/bumdes/{bumdes}/unit-usaha/{unitUsaha}', [UnitUsahaController::class, 'update'])->whereNumber(['bumdes', 'unitUsaha'])->name('bumdes.unit-usaha.update');
    Route::delete('/bumdes/{bumdes}/unit-usaha/{unitUsaha}', [UnitUsahaController::class, 'destroy'])->whereNumber(['bumdes', 'unitUsaha'])->name('bumdes.unit-usaha.destroy');
    Route::post('/bumdes/{bumdes}/unit-usaha/{unitUsaha}/produk', [ProdukLayananController::class, 'store'])->whereNumber(['bumdes', 'unitUsaha'])->name('bumdes.produk.store');
    Route::put('/bumdes/{bumdes}/unit-usaha/{unitUsaha}/produk/{product}', [ProdukLayananController::class, 'update'])->whereNumber(['bumdes', 'unitUsaha', 'product'])->name('bumdes.produk.update');
    Route::delete('/bumdes/{bumdes}/unit-usaha/{unitUsaha}/produk/{product}', [ProdukLayananController::class, 'destroy'])->whereNumber(['bumdes', 'unitUsaha', 'product'])->name('bumdes.produk.destroy');
    Route::get('/bumdes/{bumdes}/media/unit-usaha/{unitUsaha}/produk/{product}', [BumdesMediaController::class, 'product'])->whereNumber(['bumdes', 'unitUsaha', 'product'])->name('bumdes.media.produk');

    Route::post('/bumdes/{bumdes}/aset', [AsetBumdesController::class, 'store'])->whereNumber('bumdes')->name('bumdes.aset.store');
    Route::put('/bumdes/{bumdes}/aset/{asset}', [AsetBumdesController::class, 'update'])->whereNumber(['bumdes', 'asset'])->name('bumdes.aset.update');
    Route::delete('/bumdes/{bumdes}/aset/{asset}', [AsetBumdesController::class, 'destroy'])->whereNumber(['bumdes', 'asset'])->name('bumdes.aset.destroy');
    Route::post('/bumdes/{bumdes}/keuangan', [KeuanganBumdesController::class, 'store'])->whereNumber('bumdes')->name('bumdes.keuangan.store');
    Route::put('/bumdes/{bumdes}/keuangan/{keuangan}', [KeuanganBumdesController::class, 'update'])->whereNumber(['bumdes', 'keuangan'])->name('bumdes.keuangan.update');
    Route::delete('/bumdes/{bumdes}/keuangan/{keuangan}', [KeuanganBumdesController::class, 'destroy'])->whereNumber(['bumdes', 'keuangan'])->name('bumdes.keuangan.destroy');
    Route::post('/bumdes/{bumdes}/kemitraan', [KemitraanBumdesController::class, 'store'])->whereNumber('bumdes')->name('bumdes.kemitraan.store');
    Route::put('/bumdes/{bumdes}/kemitraan/{partnership}', [KemitraanBumdesController::class, 'update'])->whereNumber(['bumdes', 'partnership'])->name('bumdes.kemitraan.update');
    Route::delete('/bumdes/{bumdes}/kemitraan/{partnership}', [KemitraanBumdesController::class, 'destroy'])->whereNumber(['bumdes', 'partnership'])->name('bumdes.kemitraan.destroy');
    Route::post('/bumdes/{bumdes}/galeri', [GaleriBumdesController::class, 'store'])->whereNumber('bumdes')->name('bumdes.galeri.store');
    Route::delete('/bumdes/{bumdes}/galeri/{gallery}', [GaleriBumdesController::class, 'destroy'])->whereNumber(['bumdes', 'gallery'])->name('bumdes.galeri.destroy');
    Route::get('/bumdes/{bumdes}/media/galeri/{gallery}', [BumdesMediaController::class, 'gallery'])->whereNumber(['bumdes', 'gallery'])->name('bumdes.media.galeri');

    Route::prefix('admin')
        ->name('admin.')
        ->middleware('role:'.UserRole::SuperAdmin->value)
        ->group(function (): void {
            Route::resource('users', UserController::class)->except(['show', 'destroy']);
            Route::resource('kabupaten', KabupatenController::class)->except('show');
            Route::resource('kecamatan', KecamatanController::class)->except('show');
            Route::resource('desa', DesaController::class)->except('show');
            Route::post('kawasan/{kawasan}/dokumen', [AdminDokumenKawasanController::class, 'store'])
                ->name('kawasan.dokumen.store');
            Route::delete('kawasan/{kawasan}/dokumen/{document}', [AdminDokumenKawasanController::class, 'destroy'])
                ->name('kawasan.dokumen.destroy');
            Route::get('kawasan/options/desa', [KawasanOptionController::class, 'desas'])
                ->name('kawasan.options.desa');
            Route::resource('kawasan', AdminKawasanController::class);
            Route::resource('kategori-kawasan', KategoriKawasanController::class)
                ->parameters(['kategori-kawasan' => 'kategori_kawasan'])
                ->except(['show', 'destroy']);
            Route::resource('kategori-potensi', KategoriPotensiController::class)
                ->parameters(['kategori-potensi' => 'kategori_potensi'])
                ->except(['show', 'destroy']);
        });
});
