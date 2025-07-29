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
use App\Http\Controllers\StokOpnameController;
use App\Http\Controllers\PenyesuaianBarangController;
use App\Http\Controllers\TransferProdukController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BarcodeBatchController;
use App\Http\Controllers\KonsinyasiKeluarController;
use App\Http\Controllers\BayarConsignorController;
use App\Http\Controllers\PenerimaanKonsinyasiController;
use App\Http\Controllers\ReturConsigneeController;
use App\Http\Controllers\KonsinyasiMasukController;
use App\Http\Controllers\JurnalController;

Route::get('/', function () {
    $reminder = \App\Http\Controllers\BahanController::getReminderKadaluarsa();
    return view('welcome', compact('reminder'));
})->middleware('auth')->name('welcome');

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
Route::post('orderbeli/{no_order_beli}/update-pembayaran', [OrderBeliController::class, 'updatePembayaran'])->name('orderbeli.updatePembayaran');

// Route pelanggan
Route::resource('pelanggan', PelangganController::class);

// Route consignor
Route::resource('consignor', ConsignorController::class);

// Route consignee
Route::resource('consignee', ConsigneeController::class);
Route::get('/consignee/{kode_consignee}/setor', [ConsigneeController::class, 'setor'])->name('consignee.setor');
Route::post('/consignee/{kode_consignee}/setor', [ConsigneeController::class, 'storeSetor'])->name('consignee.storeSetor');

// Route terima bahan
Route::get('/terimabahan/laporan', [TerimabahanController::class, 'laporan'])->name('terimabahan.laporan');
Route::resource('terimabahan', TerimabahanController::class);
Route::get('/terimabahan/sisa-order/{no_order_beli}', [TerimaBahanController::class, 'getSisaOrder']);
Route::get('/terimabahan/{id}/edit', [TerimabahanController::class, 'edit'])->name('terimabahan.edit');
Route::get('/terimabahan/{no_terima_bahan}/detail', [PembelianController::class, 'detailTerimaBahan']);
Route::get('/terimabahan/{no_terima_bahan}/data', [PembelianController::class, 'getTerimaBahan']);
Route::get('/terimabahan/sisa-order/{no_order}', [TerimaBahanController::class, 'getSisaOrder']);
Route::get('/get-order-detail/{no_order_beli}', [TerimaBahanController::class, 'getOrderDetail']);

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

// Route bayar consignor
Route::resource('bayarconsignor', App\Http\Controllers\BayarConsignorController::class);
Route::get('/bayarconsignor/{no_bayarconsignor}/cetak', [App\Http\Controllers\BayarConsignorController::class, 'cetak'])->name('bayarconsignor.cetak');

// Route retur pembelian
Route::get('/returbeli/laporan/pdf', [ReturBeliController::class, 'laporanPdf'])->name('returbeli.laporan.pdf');
Route::resource('returbeli', ReturBeliController::class);
Route::get('/returbeli/create', [ReturBeliController::class, 'create'])->name('returbeli.create');
Route::post('/returbeli/store', [ReturBeliController::class, 'store'])->name('returbeli.store');
Route::get('/returbeli/detail-pembelian/{no_pembelian}', [ReturBeliController::class, 'getDetailPembelian']);
Route::get('/returbeli/cetak/{no_retur_beli}', [ReturBeliController::class, 'cetak'])->name('returbeli.cetak');
Route::get('/returbeli/terimabarang/{no_retur_beli}', [ReturBeliController::class, 'formTerimaBarang'])->name('returbeli.terimabarang');
Route::post('/returbeli/terimabarang/{no_retur_beli}', [ReturBeliController::class, 'terimaBarang'])->name('returbeli.terimaBarang');
Route::post('/returbeli/kasretur/{no_retur_beli}', [ReturBeliController::class, 'kasRetur'])->name('returbeli.kasretur');
Route::get('/returbeli/laporan', [ReturBeliController::class, 'laporan'])->name('returbeli.laporan');

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
Route::get('/hpp/laporan', [App\Http\Controllers\HppController::class, 'laporan'])->name('hpp.laporan');
Route::get('/hpp/laporan/pdf', [App\Http\Controllers\HppController::class, 'laporanPdf'])->name('hpp.laporan.pdf');

// Route karyawan
Route::resource('karyawan', KaryawanController::class);

// Route hutang
Route::get('/hutang', [HutangController::class, 'index'])->name('hutang.index');
Route::get('/hutang/create', [HutangController::class, 'create'])->name('hutang.create');
Route::post('/hutang', [HutangController::class, 'store'])->name('hutang.store');
Route::get('/hutang/{no_utang}/detail', [HutangController::class, 'detail'])->name('hutang.detail');
Route::get('/hutang/{no_utang}/bayar', [HutangController::class, 'bayar'])->name('hutang.bayar');
Route::post('/hutang/{no_utang}/bayar', [HutangController::class, 'bayarStore'])->name('hutang.bayar.store');
Route::get('hutang/{no_utang}/pembayaran/{no_jurnal}/edit', [HutangController::class, 'editPembayaran'])->name('hutang.editPembayaran');Route::delete('hutang/{no_utang}/pembayaran/{no_jurnal}', [HutangController::class, 'hapusPembayaran'])->name('hutang.hapusPembayaran');
Route::put('hutang/{no_utang}/pembayaran/{no_jurnal}', [HutangController::class, 'updatePembayaran'])->name('hutang.bayar.update');
Route::get('/cetak/{no_utang}', [HutangController::class, 'cetak'])->name('hutang.cetak');

// Route kartu stok
Route::get('/kartustok/bahan', [KartuStokController::class, 'bahan'])->name('kartustok.bahan');
Route::get('/kartustok/api/{kode_bahan}', [KartuStokController::class, 'getKartuPersBahan']);
Route::get('/kartustok/produk', [KartuStokController::class, 'produk'])->name('kartustok.produk');
Route::get('/kartustok/api-produk/{kode_produk}', [KartuStokController::class, 'getKartuPersProduk']);
Route::get('/kartustok/laporan-bahan', [KartuStokController::class, 'laporanBahan'])->name('kartustok.laporan_bahan');
Route::get('/kartustok/laporan-produk', [KartuStokController::class, 'laporanProduk'])->name('kartustok.laporan_produk');

// Route kas keluar
Route::resource('kaskeluar', KaskeluarController::class);

// Route piutang custom (letakkan sebelum resource)
Route::get('/piutang/{no_piutang}/bayar', [PiutangController::class, 'bayar'])->name('piutang.bayar');
Route::post('/piutang/{no_piutang}/bayar', [PiutangController::class, 'bayarStore'])->name('piutang.bayar.store');
Route::get('/piutang/{no_piutang}/detail', [PiutangController::class, 'show'])->name('piutang.detail');

// Route resource piutang
Route::resource('piutang', PiutangController::class);

// Route index dan cetak
Route::get('/konsinyasimasuk', [App\Http\Controllers\KonsinyasiMasukController::class, 'index'])->name('konsinyasimasuk.index');
Route::get('/konsinyasimasuk/{no_konsinyasimasuk}/cetak', [App\Http\Controllers\KonsinyasiMasukController::class, 'cetak'])->name('konsinyasimasuk.cetak');

// Konsinyasi Masuk: harga jual produk (AJAX)
Route::get('konsinyasimasuk/{no_konsinyasimasuk}/detail-json', [KonsinyasiMasukController::class, 'detailJson'])
     ->name('konsinyasimasuk.detail-json')
     ->middleware('auth');
Route::post('konsinyasimasuk/update-harga-jual', [KonsinyasiMasukController::class, 'updateHargaJual'])
    ->name('konsinyasimasuk.update-harga-jual');

// **Resource route HARUS PALING BAWAH**
Route::resource('konsinyasimasuk', \App\Http\Controllers\KonsinyasiMasukController::class);Route::resource('konsinyasimasuk', \App\Http\Controllers\KonsinyasiMasukController::class);

// Route pembayaran ke consignor (untuk sidebar konsinyasi)
Route::get('/bayarconsignor', [App\Http\Controllers\BayarConsignorController::class, 'index'])->name('bayarconsignor.index');

// Route komisi penjualan konsinyasi
Route::get('/komisijual', [App\Http\Controllers\KomisiJualController::class, 'index'])->name('komisijual.index');

// Route retur konsinyasi ke consignor
Route::get('/returconsignor', [App\Http\Controllers\ReturConsignorController::class, 'index'])->name('returconsignor.index');
Route::get('/returconsignor/create', [App\Http\Controllers\ReturConsignorController::class, 'create'])->name('returconsignor.create');

// Route produk konsinyasi
Route::resource('produk-konsinyasi', \App\Http\Controllers\ProdukKonsinyasiController::class);
Route::get('/produk-konsinyasi/by-consignor/{kode_consignor}', [ProdukKonsinyasiController::class, 'getByConsignor']);
Route::get('/produk-konsinyasi/{kode_consignor}', [KonsinyasiMasukController::class, 'getProdukByConsignor']);

// Route stok opname
Route::get('/stokopname/bahan', [StokOpnameController::class, 'create'])->name('stokopname.create');
Route::post('/stokopname/bahan', [StokOpnameController::class, 'store'])->name('stokopname.store');

Route::get('/stokopname/produk', [StokOpnameController::class, 'produk'])->name('stokopname.produk');
Route::post('/stokopname/produk/store', [StokOpnameController::class, 'storeProduk'])->name('stokopname.storeProduk');
// Route laporan pembelian
Route::get('/pembelian/laporan/pdf', [PembelianController::class, 'laporanPdf'])->name('pembelian.laporan.pdf');

// Route penyesuaian barang
Route::get('/penyesuaian/exp/{tipe?}', [PenyesuaianBarangController::class, 'index'])->name('penyesuaian.exp');
Route::post('/penyesuaian/exp', [PenyesuaianBarangController::class, 'store'])->name('penyesuaian.store');

// Route overhead
Route::get('/overhead', [OverheadController::class, 'index'])->name('overhead.index');
Route::get('/overhead/create', [OverheadController::class, 'create'])->name('overhead.create');
Route::post('/overhead/store', [OverheadController::class, 'store'])->name('overhead.store');

// Route Aset Tetap
Route::resource('aset-tetap', AsetTetapController::class)->only(['index', 'create', 'store']);

// Route transfer produk
Route::resource('transferproduk', TransferProdukController::class);
Route::get('/transferproduk/create', [TransferProdukController::class, 'create'])->name('transferproduk.create');
Route::post('/transferproduk/store', [TransferProdukController::class, 'store'])->name('transferproduk.store');
Route::get('/{no_transaksi}/edit', [TransferProdukController::class, 'edit'])->name('transferproduk.edit');
Route::put('/{no_transaksi}', [TransferProdukController::class, 'update'])->name('transferproduk.update');
Route::delete('/{no_transaksi}', [TransferProdukController::class, 'destroy'])->name('transferproduk.destroy');
Route::get('/transferproduk/get-products', [TransferProdukController::class, 'getProductsByLocation']);
Route::get('/transfer/fifo-hpp', [TransferProdukController::class, 'calculateFifoHpp']);

// Route transaksi penjualan produk konsinyasi masuk
Route::resource('transaksikonsinyasimasuk', App\Http\Controllers\TransaksiKonsinyasiMasukController::class);

// Route jual konsinyasi masuk
Route::resource('jualkonsinyasimasuk', \App\Http\Controllers\JualKonsinyasiMasukController::class);
Route::get('/jualkonsinyasimasuk/{no_jualkonsinyasimasuk}/cetak', [\App\Http\Controllers\JualKonsinyasiMasukController::class, 'cetak'])->name('jualkonsinyasimasuk.cetak');

// Route konsinyasi keluar
Route::resource('konsinyasikeluar', KonsinyasiKeluarController::class);
Route::get('/konsinyasikeluar/{no_konsinyasikeluar}/cetak', [KonsinyasiKeluarController::class, 'cetak'])->name('konsinyasikeluar.cetak');
Route::get('/konsinyasikeluar/{id}/cetak', [App\Http\Controllers\KonsinyasiKeluarController::class, 'cetak'])->name('konsinyasikeluar.cetak');

// Route penerimaan konsinyasi
Route::resource('penerimaankonsinyasi', App\Http\Controllers\PenerimaanKonsinyasiController::class);

// Route AJAX produk konsinyasi keluar untuk returconsignee (HARUS DI ATAS resource)
Route::get('/returconsignee/produk-keluar', [App\Http\Controllers\ReturConsigneeController::class, 'getProdukKonsinyasiKeluar'])->name('returconsignee.produk_keluar');
// Route retur consignee
Route::get('returconsignee/get-produk', [ReturConsigneeController::class, 'getProduk'])->name('returconsignee.getProduk');
Route::get('returconsignee/create', [ReturConsigneeController::class, 'create'])->name('returconsignee.create');
Route::post('returconsignee/store', [ReturConsigneeController::class, 'store'])->name('returconsignee.store');
Route::get('returconsignee/create-returterima', [App\Http\Controllers\ReturConsigneeController::class, 'createReturTerima'])->name('returconsignee.createReturTerima');
Route::resource('returconsignee', App\Http\Controllers\ReturConsigneeController::class);

// Route kartu persediaan produk konsinyasi masuk
Route::resource('kartuperskonsinyasi', App\Http\Controllers\KartuPersKonsinyasiController::class);

// Route jurnal
Route::get('/jurnal', [JurnalController::class, 'index'])->name('jurnal.index');
Route::get('/buku-besar', [JurnalController::class, 'bukuBesar'])->name('jurnal.buku_besar');
Route::get('/jurnalpenyesuaian', [JurnalController::class, 'penyesuaian'])->name('jurnal.penyesuaian');

// Barcode batch routes
Route::get('/barcode-batch/info', [BarcodeBatchController::class, 'info']);
Route::get('/barcode-batch/print', [BarcodeBatchController::class, 'printBatchBarcodes']);
Route::get('/barcode-batch/barcode-image', [BarcodeBatchController::class, 'barcodeImage']);

// Kartu Persediaan Produk Konsinyasi
Route::get('/kartuperskonsinyasi', [App\Http\Controllers\KartuPersKonsinyasiController::class, 'produkKonsinyasi'])->name('kartuperskonsinyasi.index');
Route::get('/kartuperskonsinyasi/api-produk/{kode_produk}', [App\Http\Controllers\KartuPersKonsinyasiController::class, 'getKartuPersProdukKonsinyasi']);

// Route custom BayarConsignor (letakkan sebelum resource agar tidak tertimpa)
Route::get('bayarconsignor/create', [App\Http\Controllers\BayarConsignorController::class, 'create'])->name('bayarconsignor.create');
Route::get('bayarconsignor/produk/{kode_consignor}', [App\Http\Controllers\BayarConsignorController::class, 'produkByConsignor']);
Route::resource('bayarconsignor', BayarConsignorController::class);

// Route khusus create retur dari penerimaan konsinyasi
Route::get('returconsignee/create-returterima', [App\Http\Controllers\ReturConsigneeController::class, 'createReturTerima'])->name('returconsignee.createReturTerima');

Route::post('/returconsignor', [App\Http\Controllers\ReturConsignorController::class, 'store'])->name('returconsignor.store');

// Route API
Route::get('/api/konsinyasikeluar/by-consignee/{kode_consignee}', [App\Http\Controllers\PenerimaanKonsinyasiController::class, 'apiKonsinyasiKeluarByConsignee']);
// API untuk ambil detail konsinyasi keluar (AJAX form penerimaan konsinyasi)
Route::get('/api/konsinyasikeluar/detail/{no_konsinyasikeluar}', [App\Http\Controllers\PenerimaanKonsinyasiController::class, 'apiKonsinyasiKeluarDetail']);
Route::get('/bayarconsignor/produk-penjualan/{kode_consignor}', [BayarConsignorController::class, 'getProdukPenjualanConsignor']);
Route::get('/returconsignor/produk-masuk', [App\Http\Controllers\ReturConsignorController::class, 'getProdukKonsinyasiMasuk']);
Route::get('/api/harga-jual-konsinyasi/{kode_produk}', [App\Http\Controllers\KonsinyasiMasukController::class, 'getHargaJualKonsinyasi']);
Route::get('returconsignor/{no_returconsignor}', [App\Http\Controllers\ReturConsignorController::class, 'show'])->name('returconsignor.show');
Route::get('returconsignor/{no_returconsignor}/edit', [App\Http\Controllers\ReturConsignorController::class, 'edit'])->name('returconsignor.edit');
Route::put('returconsignor/{no_returconsignor}', [App\Http\Controllers\ReturConsignorController::class, 'update'])->name('returconsignor.update');
Route::resource('returconsignor', App\Http\Controllers\ReturConsignorController::class);
