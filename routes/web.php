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
use App\Http\Controllers\StokOpnameController;
use App\Http\Controllers\PenyesuaianBarangController;
use App\Http\Controllers\TransferProdukController;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Route login
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Route bahan
Route::resource('bahan', BahanController::class);
Route::get('/bahan/sync-stok', [BahanController::class, 'updateSemuaStokBahan'])->name('bahan.syncStok');
Route::get('/bahan/reminder', [BahanController::class, 'reminderKadaluarsa'])->name('bahan.reminder');

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
Route::get('/returbeli/laporan', [ReturBeliController::class, 'laporan'])->name('returbeli.laporan');
Route::get('/returbeli/laporan/pdf', [ReturBeliController::class, 'laporanPdf'])->name('returbeli.laporan.pdf');
   
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
Route::get('/produksi/{no_produksi}', [ProduksiController::class, 'show'])->name('produksi.show');
Route::delete('/produksi/{no_produksi}', [ProduksiController::class, 'destroy'])->name('produksi.destroy');
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
Route::get('/hutang/create', [HutangController::class, 'create'])->name('hutang.create');
Route::post('/hutang', [HutangController::class, 'store'])->name('hutang.store');
Route::get('/hutang/{no_utang}/detail', [HutangController::class, 'detail'])->name('hutang.detail');
Route::get('/hutang/{no_utang}/bayar', [HutangController::class, 'bayar'])->name('hutang.bayar');
Route::post('/hutang/{no_utang}/bayar', [HutangController::class, 'bayarStore'])->name('hutang.bayar.store');

// Route kartu stok
Route::get('/kartustok/bahan', [KartuStokController::class, 'bahan'])->name('kartustok.bahan');
Route::get('/kartustok/api/{kode_bahan}', [KartuStokController::class, 'getKartuPersBahan']);
Route::get('/kartustok/produk', [KartuStokController::class, 'produk'])->name('kartustok.produk');
Route::get('/kartustok/api-produk/{kode_produk}', [KartuStokController::class, 'getKartuPersProduk']);

// Route kas keluar
Route::resource('kaskeluar', KaskeluarController::class);

// Route stok opname
Route::get('/stokopname/bahan', [StokOpnameController::class, 'create'])->name('stokopname.create');
Route::post('/stokopname/bahan', [StokOpnameController::class, 'store'])->name('stokopname.store');

Route::get('/stokopname/produk', [StokOpnameController::class, 'produk'])->name('stokopname.produk');
Route::post('/stokopname/store-produk', [StokOpnameController::class, 'storeProduk'])->name('stokopname.storeProduk');

// Route laporan pembelian
Route::get('/pembelian/laporan/pdf', [PembelianController::class, 'laporanPdf'])->name('pembelian.laporan.pdf');

// Route penyesuaian barang
Route::get('/penyesuaian/exp', [PenyesuaianBarangController::class, 'index'])->name('penyesuaian.exp');
Route::post('/penyesuaian/exp', [PenyesuaianBarangController::class, 'store'])->name('penyesuaian.store');

// Route overhead
Route::get('/overhead', [OverheadController::class, 'index'])->name('overhead.index');
Route::get('/overhead/create', [OverheadController::class, 'create'])->name('overhead.create');
Route::post('/overhead/store', [OverheadController::class, 'store'])->name('overhead.store');

// Route Aset Tetap
Route::resource('aset-tetap', AsetTetapController::class)->only(['index', 'create', 'store']);

// Route transfer produk
Route::get('/transferproduk/create', [TransferProdukController::class, 'create'])->name('transferproduk.create');
Route::post('/transferproduk/store', [TransferProdukController::class, 'store'])->name('transferproduk.store');