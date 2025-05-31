<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BahanController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\OrderBeliController;
use App\Http\Controllers\PermintaanProduksiController;
use App\Http\Controllers\ResepController;
use App\Http\Controllers\JadwalProduksiController;
use App\Http\Controllers\ProduksiController;

Route::get('/', function () {
    return view('welcome');
});


// Route bahan
Route::resource('bahan', BahanController::class);

// Route kategori
Route::resource('kategori', KategoriController::class);

// Route supplier
Route::resource('supplier', SupplierController::class);

// Route produk
Route::resource('produk', ProdukController::class);

// Route order beli
Route::resource('orderbeli', OrderbeliController::class);
Route::post('orderbeli/{no_order_beli}/setujui', [OrderbeliController::class, 'setujui'])->name('orderbeli.setujui');
Route::get('/orderbeli/{no_order_beli}/cetak', [OrderBeliController::class, 'cetak'])->name('orderbeli.cetak');

// Route permintaan produksi
Route::get('/permintaan-produksi', [PermintaanProduksiController::class, 'index'])->name('permintaan_produksi.index');
Route::get('/permintaan-produksi/create', [PermintaanProduksiController::class, 'create'])->name('permintaan_produksi.create');
Route::post('/permintaan-produksi', [PermintaanProduksiController::class, 'store'])->name('permintaan.store');

// Route detail Resep
Route::get('/resep', [ResepController::class, 'index'])->name('resep.index');
Route::get('/resep/create', [ResepController::class, 'create'])->name('resep.create');
Route::post('/resep', [ResepController::class, 'store'])->name('resep.store');

// Route jadwal produksi
Route::get('/jadwal-produksi/create', [JadwalProduksiController::class, 'create'])->name('jadwal_.create');
Route::post('/jadwal-produksi', [JadwalProduksiController::class, 'store'])->name('jadwal.store');
Route::get('/jadwal-produksi', [JadwalProduksiController::class, 'index'])->name('jadwal.index');
Route::get('/jadwal-produksi/{kode}', [JadwalProduksiController::class, 'show'])->name('jadwal.show');
Route::delete('/jadwal/{kode_jadwal}', [JadwalProduksiController::class, 'destroy'])->name('jadwal.destroy');

// Route produksi

Route::get('/produksi/create', [ProduksiController::class, 'create'])->name('produksi.create');
Route::post('/produksi', [ProduksiController::class, 'store'])->name('produksi.store');
Route::get('/produksi', [ProduksiController::class, 'index'])->name('produksi.index');
