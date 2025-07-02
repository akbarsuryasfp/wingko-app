<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BahanController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\OrderBeliController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\ConsignorController;
use App\Http\Controllers\ConsigneeController;
use App\Http\Controllers\TerimabahanController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\PesananPenjualanController;
use App\Http\Controllers\PermintaanProduksiController;
use App\Http\Controllers\ResepController;
use App\Http\Controllers\JadwalProduksiController;
use App\Http\Controllers\ProduksiController;
use App\Http\Controllers\HppController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\ReturBeliController;
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\HutangController;
use App\Http\Controllers\KartuStokController;
use App\Http\Controllers\KaskeluarController;
use App\Http\Controllers\ReturJualController;
use App\Http\Controllers\PiutangController;


Route::get('/', function () {
    return view('welcome');
});

// Route bahan
Route::resource('bahan', BahanController::class);
Route::get('/bahan/sync-stok', [BahanController::class, 'updateSemuaStokBahan'])->name('bahan.syncStok');

// Route kategori
Route::resource('kategori', KategoriController::class);

// Route supplier
Route::resource('supplier', SupplierController::class);

// Route produk
Route::resource('produk', ProdukController::class);

// Route order beli
Route::resource('orderbeli', OrderBeliController::class);
Route::post('orderbeli/{no_order_beli}/setujui', [OrderBeliController::class, 'setujui'])->name('orderbeli.setujui');
Route::get('/orderbeli/{no_order_beli}/cetak', [OrderBeliController::class, 'cetak'])->name('orderbeli.cetak');
Route::post('orderbeli/{no_order_beli}/uangmuka', [OrderBeliController::class, 'simpanUangMuka'])->name('orderbeli.uangmuka');
Route::get('orderbeli/{no_order_beli}/detail', [OrderBeliController::class, 'getDetail'])->name('orderbeli.detail');

// Route pelanggan
Route::resource('pelanggan', PelangganController::class);

// Route consignor
Route::resource('consignor', ConsignorController::class);

// Route consignee
Route::resource('consignee', ConsigneeController::class);

// Route terima bahan
Route::resource('terimabahan', TerimabahanController::class);
Route::get('/terimabahan/sisa-order/{no_order_beli}', [TerimabahanController::class, 'getSisaOrder']);
Route::get('/terimabahan/{id}/edit', [TerimabahanController::class, 'edit'])->name('terimabahan.edit');
Route::get('/terimabahan/{no_terima_bahan}/detail', [PembelianController::class, 'detailTerimaBahan']);
Route::get('/terimabahan/{no_terima_bahan}/data', [PembelianController::class, 'getTerimaBahan']);

// Route penjualan
Route::resource('penjualan', PenjualanController::class);
Route::get('/penjualan/{no_jual}/cetak', [PenjualanController::class, 'cetak'])->name('penjualan.cetak');
Route::get('/penjualan/cetak-tagihan/{no_jual}', [PenjualanController::class, 'cetakTagihan'])->name('penjualan.cetak_tagihan');
Route::get('create-pesanan', [PenjualanController::class, 'createPesanan'])->name('penjualan.createPesanan');

// Route retur penjualan
Route::resource('returjual', ReturJualController::class);
Route::get('/returjual/{no_returjual}/cetak', [ReturJualController::class, 'cetak'])->name('returjual.cetak');
Route::get('/returjual/filter-penjualan', [\App\Http\Controllers\ReturJualController::class, 'filterPenjualan'])->name('returjual.filter-penjualan');
Route::get('/returjual/detail-penjualan/{no_jual}', [ReturJualController::class, 'getDetailPenjualan']);

// Route pesanan penjualan
Route::resource('pesananpenjualan', PesananPenjualanController::class);
Route::get('/pesananpenjualan/{no_pesanan}/cetak', [PesananPenjualanController::class, 'cetak'])->name('pesananpenjualan.cetak');

// Route pembelian khusus (AJAX dan form)
Route::get('/pembelian/create', [PembelianController::class, 'create'])->name('pembelian.create');
Route::post('/pembelian', [PembelianController::class, 'store'])->name('pembelian.store');

Route::get('/pembelian/langsung', [PembelianController::class, 'createLangsung'])->name('pembelian.langsung');
Route::post('/pembelian/langsung', [PembelianController::class, 'storeLangsung'])->name('pembelian.storeLangsung');

Route::get('/pembelian/detail-terima-bahan/{no_terima_bahan}', [PembelianController::class, 'detailTerimaBahan']);

Route::get('/pembelian/{no_pembelian}', [PembelianController::class, 'show'])->name('pembelian.show');
Route::get('/pembelian/{no_pembelian}/detail-json', [\App\Http\Controllers\PembelianController::class, 'getDetailPembelian']);

Route::resource('pembelian', PembelianController::class);

// Route retur pembelian
Route::resource('returbeli', ReturBeliController::class);
Route::get('/returbeli/create', [ReturBeliController::class, 'create'])->name('returbeli.create');
Route::post('/returbeli/store', [ReturBeliController::class, 'store'])->name('returbeli.store');
Route::get('/returbeli/detail-pembelian/{no_pembelian}', [ReturBeliController::class, 'getDetailPembelian']);
Route::get('/returbeli/cetak/{no_retur_beli}', [ReturBeliController::class, 'cetak'])->name('returbeli.cetak');
   
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

// Route HPP
Route::get('/hpp', [HppController::class, 'index'])->name('hpp.index');
Route::get('/hpp/input/{no_detail}', [HppController::class, 'create'])->name('hpp.input');
Route::post('/hpp/simpan', [HppController::class, 'store'])->name('hpp.store');
Route::get('/hpp/edit/{no_detail}', [HppController::class, 'edit'])->name('hpp.edit');
Route::put('/hpp/update/{no_detail}', [HppController::class, 'update'])->name('hpp.update');

// Route karyawan
Route::resource('karyawan', KaryawanController::class);

// Route hutang
Route::get('/hutang', [HutangController::class, 'index'])->name('hutang.index');
Route::resource('hutang', HutangController::class);
Route::get('/hutang/{no_utang}/detail', [HutangController::class, 'detail'])->name('hutang.detail');
Route::get('/hutang/{no_utang}/bayar', [HutangController::class, 'bayar'])->name('hutang.bayar');
Route::post('/utang/{no_utang}/bayar', [HutangController::class, 'bayarStore'])->name('utang.bayar.store');

// Route kartu stok
Route::get('/kartustok/bahan', [KartuStokController::class, 'bahan'])->name('kartustok.bahan');
Route::get('/kartustok/api/{kode_bahan}', [KartuStokController::class, 'getKartuPersBahan']);
Route::get('/kartustok/produk', [KartuStokController::class, 'produk'])->name('kartustok.produk');
Route::get('/kartustok/api-produk/{kode_produk}', [KartuStokController::class, 'getKartuPersProduk']);

// Route kas keluar
Route::resource('kaskeluar', KaskeluarController::class);

// Route piutang custom (letakkan sebelum resource)
Route::get('/piutang/{no_piutang}/bayar', [PiutangController::class, 'bayar'])->name('piutang.bayar');
Route::post('/piutang/{no_piutang}/bayar', [PiutangController::class, 'bayarStore'])->name('piutang.bayar.store');
Route::get('/piutang/{no_piutang}/detail', [PiutangController::class, 'show'])->name('piutang.detail');

// Route resource piutang
Route::resource('piutang', PiutangController::class);

// Route konsinyasi masuk
Route::get('/konsinyasimasuk', [App\Http\Controllers\KonsinyasiMasukController::class, 'index'])->name('konsinyasimasuk.index');
Route::resource('konsinyasimasuk', \App\Http\Controllers\KonsinyasiMasukController::class);

// Route pembayaran ke consignor (untuk sidebar konsinyasi)
Route::get('/bayarconsignor', [App\Http\Controllers\BayarConsignorController::class, 'index'])->name('bayarconsignor.index');

// Route komisi penjualan konsinyasi
Route::get('/komisijual', [App\Http\Controllers\KomisiJualController::class, 'index'])->name('komisijual.index');

// Route retur konsinyasi ke consignor
Route::get('/returconsignor', [App\Http\Controllers\ReturConsignorController::class, 'index'])->name('returconsignor.index');

// Route produk konsinyasi
Route::get('/produk-konsinyasi/create', [\App\Http\Controllers\ProdukKonsinyasiController::class, 'create'])->name('produk-konsinyasi.create');
Route::resource('produk-konsinyasi', \App\Http\Controllers\ProdukKonsinyasiController::class);
Route::get('/produk-konsinyasi/by-consignor/{kode_consignor}', [ProdukKonsinyasiController::class, 'getByConsignor']);