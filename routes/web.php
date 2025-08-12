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
use App\Http\Controllers\LaporanKeuanganController;
use App\Http\Controllers\AsetTetapController;
use App\Http\Controllers\OverheadController;
use App\Http\Controllers\SettingController;


Route::get('/admin-area', function () {
    return 'Hanya admin/gudang yang bisa lihat ini!';
})->middleware(['auth', 'role:admin,gudang']);


Route::get('/', [\App\Http\Controllers\DashboardController::class, 'index'])->middleware('auth')->name('welcome');

// Route login
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'role:gudang,admin'])->group(function () {
    // Route supplier
    Route::resource('supplier', SupplierController::class);

    // Route bahan
    Route::resource('bahan', BahanController::class);
    Route::get('/bahan/sync-stok', [BahanController::class, 'updateSemuaStokBahan'])->name('bahan.syncStok');
    Route::get('/bahan/reminder', [BahanController::class, 'reminderKadaluarsa'])->name('bahan.reminder');

    // Semua route di bawah ini hanya untuk gudang & pemilik
    Route::get('/transferproduk/produk-by-lokasi', [TransferProdukController::class, 'produkByLokasi'])->name('transferproduk.produkByLokasi');
    Route::resource('transferproduk', TransferProdukController::class);
    Route::get('/transferproduk/create', [TransferProdukController::class, 'create'])->name('transferproduk.create');
    Route::post('/transferproduk/store', [TransferProdukController::class, 'store'])->name('transferproduk.store');
    Route::get('/{no_transaksi}/edit', [TransferProdukController::class, 'edit'])->name('transferproduk.edit');
    Route::put('/{no_transaksi}', [TransferProdukController::class, 'update'])->name('transferproduk.update');
    Route::delete('/{no_transaksi}', [TransferProdukController::class, 'destroy'])->name('transferproduk.destroy');
    Route::get('/transferproduk/get-products', [TransferProdukController::class, 'getProductsByLocation']);
    Route::get('/transfer/fifo-hpp', [TransferProdukController::class, 'calculateFifoHpp']);
    Route::get('transferproduk/laporan/pdf', [TransferProdukController::class, 'laporanPdf'])->name('transferproduk.laporan.pdf');
        // Route stok opname
    Route::get('/stokopname/bahan', [StokOpnameController::class, 'create'])->name('stokopname.create');
    Route::post('/stokopname/bahan', [StokOpnameController::class, 'store'])->name('stokopname.store');

    Route::get('/stokopname/produk', [StokOpnameController::class, 'produk'])->name('stokopname.produk');
    Route::post('/stokopname/produk/store', [StokOpnameController::class, 'storeProduk'])->name('stokopname.storeProduk');

    // Route hutang
    Route::get('/hutang', [HutangController::class, 'index'])->name('hutang.index');
    Route::get('/hutang/create', [HutangController::class, 'create'])->name('hutang.create');
    Route::post('/hutang', [HutangController::class, 'store'])->name('hutang.store');
    Route::get('/hutang/{no_utang}/detail', [HutangController::class, 'detail'])->name('hutang.detail');
    Route::get('/hutang/{no_utang}/bayar', [HutangController::class, 'bayar'])->name('hutang.bayar');
    Route::post('/hutang/{no_utang}/bayar', [HutangController::class, 'bayarStore'])->name('hutang.bayar.store');
    Route::get('hutang/{no_utang}/pembayaran/{no_jurnal}/edit', [HutangController::class, 'editPembayaran'])->name('hutang.editPembayaran');Route::delete('hutang/{no_utang}/pembayaran/{no_jurnal}', [HutangController::class, 'hapusPembayaran'])->name('hutang.hapusPembayaran');
    Route::put('hutang/{no_utang}/pembayaran/{no_jurnal}', [HutangController::class, 'updatePembayaran'])->name('hutang.bayar.update');
    Route::get('hutang/laporan', [HutangController::class, 'laporanPdf'])->name('hutang.laporan');
    Route::get('hutang/laporan/pdf', [HutangController::class, 'laporanPdf'])->name('hutang.laporan.pdf');

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

    // Route terima bahan
    Route::get('/terimabahan/laporan', [TerimabahanController::class, 'laporan'])->name('terimabahan.laporan');
    Route::resource('terimabahan', TerimabahanController::class);
    Route::get('/terimabahan/sisa-order/{no_order_beli}', [TerimaBahanController::class, 'getSisaOrder']);
    Route::get('/terimabahan/{id}/edit', [TerimabahanController::class, 'edit'])->name('terimabahan.edit');
    Route::get('/terimabahan/{no_terima_bahan}/detail', [PembelianController::class, 'detailTerimaBahan']);
    Route::get('/terimabahan/{no_terima_bahan}/data', [PembelianController::class, 'getTerimaBahan']);
    Route::get('/terimabahan/sisa-order/{no_order}', [TerimaBahanController::class, 'getSisaOrder']);
    Route::get('/get-order-detail/{no_order_beli}', [TerimaBahanController::class, 'getOrderDetail']);

    // Route order beli
    Route::resource('orderbeli', OrderBeliController::class);
    Route::post('orderbeli/{no_order_beli}/setujui', [OrderBeliController::class, 'setujui'])->name('orderbeli.setujui');
    Route::get('/orderbeli/{no_order_beli}/cetak', [OrderBeliController::class, 'cetak'])->name('orderbeli.cetak');
    Route::post('orderbeli/{no_order_beli}/update-pembayaran', [OrderBeliController::class, 'updatePembayaran'])->name('orderbeli.updatePembayaran');

    // Route pembelian khusus (AJAX dan form)
    Route::get('/pembelian/create', [PembelianController::class, 'create'])->name('pembelian.create');
    Route::post('/pembelian', [PembelianController::class, 'store'])->name('pembelian.store');

    Route::get('/pembelian/langsung', [PembelianController::class, 'createLangsung'])->name('pembelian.langsung');
    Route::post('/pembelian/langsung', [PembelianController::class, 'storeLangsung'])->name('pembelian.storeLangsung');

    Route::get('/pembelian/detail-terima-bahan/{no_terima_bahan}', [PembelianController::class, 'detailTerimaBahan']);

    Route::get('/pembelian/{no_pembelian}', [PembelianController::class, 'show'])->name('pembelian.show');
    Route::get('/pembelian/{no_pembelian}/detail-json', [\App\Http\Controllers\PembelianController::class, 'getDetailPembelian']);
    Route::get('/pembelian/laporan', [PembelianController::class, 'laporanPdf'])->name('pembelian.laporan');
    Route::resource('pembelian', PembelianController::class);

    // Tambahkan route lain yang hanya boleh diakses oleh gudang & pemilik di sini
});

// Route kategori
Route::resource('kategori', KategoriController::class);


// Route produk
Route::resource('produk', ProdukController::class);


// Route pelanggan
Route::resource('pelanggan', PelangganController::class);

// Route consignor
Route::resource('consignor', ConsignorController::class);

// Route consignee
Route::resource('consignee', ConsigneeController::class);
Route::get('/consignee/{kode_consignee}/setor', [ConsigneeController::class, 'setor'])->name('consignee.setor');
Route::post('/consignee/{kode_consignee}/setor', [ConsigneeController::class, 'storeSetor'])->name('consignee.storeSetor');


// Route cetak laporan penjualan (HARUS sebelum resource agar tidak tertimpa)
Route::get('/penjualan/cetak-laporan-pdf', [App\Http\Controllers\PenjualanController::class, 'cetakLaporanPdf'])->name('penjualan.cetak_laporan');
// Route penjualan
Route::resource('penjualan', PenjualanController::class);
Route::get('/penjualan/{no_jual}/cetak', [PenjualanController::class, 'cetak'])->name('penjualan.cetak');
Route::get('/penjualan/cetak-tagihan/{no_jual}', [PenjualanController::class, 'cetakTagihanPdf'])->name('penjualan.cetak_tagihan');
Route::get('create-pesanan', [PenjualanController::class, 'createPesanan'])->name('penjualan.createPesanan');

// Route cetak laporan returjual (HARUS sebelum resource agar tidak tertimpa)
Route::get('/returjual/cetak-laporan', [ReturJualController::class, 'cetakLaporan'])->name('returjual.cetak_laporan');
// Route retur penjualan
Route::resource('returjual', ReturJualController::class);
Route::get('/returjual/{no_returjual}/cetak', [ReturJualController::class, 'cetak'])->name('returjual.cetak');

// Route cetak retur consignor (pemilik barang)
Route::get('/returconsignor/{no_returconsignor}/cetak', [\App\Http\Controllers\ReturConsignorController::class, 'cetak'])->name('returconsignor.cetak');
Route::get('/returjual/filter-penjualan', [\App\Http\Controllers\ReturJualController::class, 'filterPenjualan'])->name('returjual.filter-penjualan');
Route::get('/returjual/detail-penjualan/{no_jual}', [ReturJualController::class, 'getDetailPenjualan']);

// Route pesanan penjualan
Route::resource('pesananpenjualan', PesananPenjualanController::class);
Route::get('/pesananpenjualan/get-data/{no_pesanan}', [PesananPenjualanController::class, 'getData']);
Route::get('/pesananpenjualan/{no_pesanan}/cetak', [PesananPenjualanController::class, 'cetakPdf'])->name('pesananpenjualan.cetak');



// Route bayar consignor
Route::get('/bayarconsignor/cetak-laporan', [\App\Http\Controllers\BayarConsignorController::class, 'cetakLaporan'])->name('bayarconsignor.cetak_laporan');
Route::resource('bayarconsignor', App\Http\Controllers\BayarConsignorController::class);
Route::get('/bayarconsignor/{no_bayarconsignor}/cetak', [App\Http\Controllers\BayarConsignorController::class, 'cetak'])->name('bayarconsignor.cetak');


// Route permintaan produksi
Route::get('/permintaan-produksi', [PermintaanProduksiController::class, 'index'])->name('permintaan_produksi.index');
Route::get('/permintaan-produksi/create', [PermintaanProduksiController::class, 'create'])->name('permintaan_produksi.create');
Route::post('/permintaan-produksi', [PermintaanProduksiController::class, 'store'])->name('permintaan.store');
Route::resource('permintaan_produksi', \App\Http\Controllers\PermintaanProduksiController::class);
// Route detail Resep
Route::get('/resep', [ResepController::class, 'index'])->name('resep.index');
Route::get('/resep/create', [ResepController::class, 'create'])->name('resep.create');
Route::post('/resep', [ResepController::class, 'store'])->name('resep.store');

// Route jadwal produksi
Route::get('/jadwal-produksi/create', [JadwalProduksiController::class, 'create'])->name('jadwal_.create');
Route::post('/jadwal-produksi', [JadwalProduksiController::class, 'store'])->name('jadwal.store');
Route::get('/jadwal-produksi', [JadwalProduksiController::class, 'index'])->name('jadwal.index');
Route::get('/jadwal-produksi/{kode}', [JadwalProduksiController::class, 'show'])->name('jadwal.show');
Route::delete('/jadwal/{no_jadwal}', [JadwalProduksiController::class, 'destroy'])->name('jadwal.destroy');

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
Route::get('/hpp/laporan', [HppController::class, 'laporan'])->name('hpp.laporan');

// Route karyawan
Route::resource('karyawan', KaryawanController::class);


// Route kartu stok
Route::get('/kartustok/bahan', [KartuStokController::class, 'bahan'])->name('kartustok.bahan');
Route::get('/kartustok/api/{kode_bahan}', [KartuStokController::class, 'getKartuPersBahan']);
Route::get('/kartustok/produk', [KartuStokController::class, 'produk'])->name('kartustok.produk');
Route::get('/kartustok/api-produk/{kode_produk}', [KartuStokController::class, 'getKartuPersProduk']);
Route::get('/kartustok/laporan-bahan', [KartuStokController::class, 'laporanBahan'])->name('kartustok.laporan_bahan');
Route::get('/kartustok/laporan-bahan-pdf', [KartuStokController::class, 'laporanBahanPdf'])->name('kartustok.laporan_bahan_pdf');
Route::get('/kartustok/laporan-produk', [KartuStokController::class, 'laporanProduk'])->name('kartustok.laporan_produk');
Route::get('/kartustok/laporan-produk-pdf', [KartuStokController::class, 'laporanProdukPdf'])->name('kartustok.laporan_produk_pdf');

// Route kas keluar
Route::get('/kaskeluar/laporan', [KasKeluarController::class, 'laporan'])->name('kaskeluar.laporan');
Route::resource('kaskeluar', KaskeluarController::class);


// Route cetak laporan piutang (HARUS sebelum semua route /piutang/{...} dan resource agar tidak tertimpa)
Route::get('/piutang/cetak-laporan', [App\Http\Controllers\PiutangController::class, 'cetakLaporan'])->name('piutang.cetak_laporan');
Route::get('/piutang/cetak-laporan-pdf', [App\Http\Controllers\PiutangController::class, 'cetakLaporanPdf'])->name('piutang.cetak_laporan_pdf');

// Route piutang custom (letakkan setelah cetak laporan, sebelum resource)
Route::get('/piutang/{no_piutang}/bayar', [PiutangController::class, 'bayar'])->name('piutang.bayar');
Route::post('/piutang/{no_piutang}/bayar', [PiutangController::class, 'bayarStore'])->name('piutang.bayar.store');
Route::get('/piutang/{no_piutang}/detail', [PiutangController::class, 'show'])->name('piutang.detail');

// Route resource piutang (PASTIKAN DIBAWAH route cetak laporan dan custom)
Route::resource('piutang', PiutangController::class);

// Route index dan cetak
// Route Konsinyasi Masuk
Route::post('/konsinyasimasuk/{no_konsinyasimasuk}/update-harga-jual', [\App\Http\Controllers\KonsinyasiMasukController::class, 'updateHargaJual'])->name('konsinyasimasuk.update-harga-jual');
Route::get('/konsinyasimasuk/cetak-laporan', [KonsinyasiMasukController::class, 'cetakLaporan'])->name('konsinyasimasuk.cetak_laporan');
Route::resource('konsinyasimasuk', \App\Http\Controllers\KonsinyasiMasukController::class);
// Route Konsinyasi Masuk
// Route Jual Konsinyasi Masuk
Route::get('/jualkonsinyasimasuk', [\App\Http\Controllers\JualKonsinyasiMasukController::class, 'index'])->name('jualkonsinyasimasuk.index');
Route::get('/jualkonsinyasimasuk/cetak-laporan', [\App\Http\Controllers\JualKonsinyasiMasukController::class, 'cetakLaporan'])->name('jualkonsinyasimasuk.cetak_laporan');
// Route pembayaran ke consignor (untuk sidebar konsinyasi)
Route::get('/bayarconsignor', [App\Http\Controllers\BayarConsignorController::class, 'index'])->name('bayarconsignor.index');

// Route komisi penjualan konsinyasi
Route::get('/komisijual', [App\Http\Controllers\KomisiJualController::class, 'index'])->name('komisijual.index');

// Route cetak laporan retur consignor (HARUS sebelum resource dan parameterized route)
Route::get('/returconsignor/cetak-laporan', [App\Http\Controllers\ReturConsignorController::class, 'cetakLaporan'])->name('returconsignor.cetak_laporan');
// Route retur konsinyasi ke consignor
Route::get('/returconsignor', [App\Http\Controllers\ReturConsignorController::class, 'index'])->name('returconsignor.index');
Route::get('/returconsignor/create', [App\Http\Controllers\ReturConsignorController::class, 'create'])->name('returconsignor.create');

// Route produk konsinyasi
Route::resource('produk-konsinyasi', \App\Http\Controllers\ProdukKonsinyasiController::class);
Route::get('/produk-konsinyasi/by-consignor/{kode_consignor}', [ProdukKonsinyasiController::class, 'getByConsignor']);
Route::get('/produk-konsinyasi/{kode_consignor}', [KonsinyasiMasukController::class, 'getProdukByConsignor']);

// Route laporan pembelian
Route::get('/pembelian/laporan/pdf', [PembelianController::class, 'laporanPdf'])->name('pembelian.laporan.pdf');

// Route penyesuaian barang
Route::get('/penyesuaian/exp/{tipe?}', [PenyesuaianBarangController::class, 'index'])->name('penyesuaian.exp');
Route::post('/penyesuaian/exp', [PenyesuaianBarangController::class, 'store'])->name('penyesuaian.store');

// Route overhead
Route::get('/overhead', [OverheadController::class, 'index'])->name('overhead.index');
Route::get('/overhead/create', [OverheadController::class, 'create'])->name('overhead.create');
Route::post('/overhead/store', [OverheadController::class, 'store'])->name('overhead.store');
Route::get('/overhead/ajax-overhead', [\App\Http\Controllers\OverheadController::class, 'ajaxOverhead'])->name('overhead.ajaxOverhead');

// Route Aset Tetap
Route::resource('aset-tetap', AsetTetapController::class)->only(['index', 'create', 'store']);


// Route transaksi penjualan produk konsinyasi masuk
Route::resource('transaksikonsinyasimasuk', App\Http\Controllers\TransaksiKonsinyasiMasukController::class);

// Route jual konsinyasi masuk
Route::resource('jualkonsinyasimasuk', \App\Http\Controllers\JualKonsinyasiMasukController::class);
Route::get('/jualkonsinyasimasuk/{no_jualkonsinyasimasuk}/cetak', [\App\Http\Controllers\JualKonsinyasiMasukController::class, 'cetak'])->name('jualkonsinyasimasuk.cetak');
Route::get('jualkonsinyasimasuk/cetak-laporan', [App\Http\Controllers\JualKonsinyasiMasukController::class, 'cetakLaporan'])->name('jualkonsinyasimasuk.cetak_laporan');

// Route cetak laporan konsinyasi keluar (letakkan sebelum resource agar tidak tertimpa)
Route::get('/konsinyasikeluar/cetak-laporan', [App\Http\Controllers\KonsinyasiKeluarController::class, 'cetakLaporan'])->name('konsinyasikeluar.cetak_laporan');
// Route konsinyasi keluar
Route::resource('konsinyasikeluar', KonsinyasiKeluarController::class);
Route::get('/konsinyasikeluar/{no_konsinyasikeluar}/cetak', [KonsinyasiKeluarController::class, 'cetak'])->name('konsinyasikeluar.cetak');
Route::get('/konsinyasikeluar/{id}/cetak', [App\Http\Controllers\KonsinyasiKeluarController::class, 'cetak'])->name('konsinyasikeluar.cetak');

// Route cetak laporan penerimaan konsinyasi (letakkan sebelum resource agar tidak tertimpa)
Route::get('/penerimaankonsinyasi/cetak-laporan', [App\Http\Controllers\PenerimaanKonsinyasiController::class, 'cetakLaporan'])->name('penerimaankonsinyasi.cetak_laporan');
// Route penerimaan konsinyasi
Route::resource('penerimaankonsinyasi', App\Http\Controllers\PenerimaanKonsinyasiController::class);

// Route cetak laporan retur consignee (HARUS sebelum resource dan parameterized route)
Route::get('/returconsignee/cetak-laporan', [App\Http\Controllers\ReturConsigneeController::class, 'cetakLaporan'])->name('returconsignee.cetak_laporan');
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

// Route laporan
Route::get('/laporan/neraca', [LaporanKeuanganController::class, 'neraca'])->name('laporan.neraca');
Route::get('/laporan/laba-rugi', [LaporanKeuanganController::class, 'labaRugi'])->name('laporan.laba_rugi');
Route::get('/laporan/perubahan-ekuitas', [LaporanKeuanganController::class, 'perubahanEkuitas'])->name('laporan.perubahan_ekuitas');
Route::get('/laporan/keuangan', [LaporanKeuanganController::class, 'index'])->name('laporan.keuangan');
Route::get('/laporan/keuangan/cetak', [LaporanKeuanganController::class, 'cetak'])->name('laporan.keuangan.cetak');
Route::get('/konsinyasimasuk/{no_konsinyasimasuk}/detail-json', [\App\Http\Controllers\KonsinyasiMasukController::class, 'detailJson'])->name('konsinyasimasuk.detail-json');

// Route cetak laporan retur consignee (HARUS sebelum resource dan parameterized route)
Route::get('/returconsignee/cetak-laporan', [App\Http\Controllers\ReturConsigneeController::class, 'cetakLaporan'])->name('returconsignee.cetak_laporan');

// Route cetak laporan piutang (HARUS sebelum resource agar tidak tertimpa)
Route::get('/piutang/cetak-laporan', [App\Http\Controllers\PiutangController::class, 'cetakLaporan'])->name('piutang.cetak_laporan');

// Route resource piutang (PASTIKAN DIBAWAH route cetak laporan)
Route::resource('piutang', PiutangController::class);

// API Stok
Route::get('/api/stok-produk/{kode_produk}', [\App\Http\Controllers\KartuStokController::class, 'getStokAkhirProduk']);
Route::get('/api/stok-produk-konsinyasi/{kode_produk}', [\App\Http\Controllers\KartuPersKonsinyasiController::class, 'getStokAkhirProdukKonsinyasi']);

// Route custom
Route::get('/penjualan/cetak-tagihan-pdf/{no_jual}', [PenjualanController::class, 'cetakTagihanPdf'])->name('penjualan.cetak_tagihan_pdf');

// Route PDF untuk cetak laporan penjualan dan nota penjualan
Route::get('/penjualan/cetak-laporan-pdf', [PenjualanController::class, 'cetakLaporanPdf'])->name('penjualan.cetak_laporan_pdf');
Route::get('/penjualan/cetak-pdf/{no_jual}', [App\Http\Controllers\PenjualanController::class, 'cetakPdf'])->name('penjualan.cetak_pdf');
Route::get('/laporan/hpp-penjualan', [LaporanKeuanganController::class, 'hppPenjualan'])->name('laporan.hpp_penjualan');


Route::post('/lokasi/set', [SettingController::class, 'setLokasi'])->name('lokasi.set');Route::post('/lokasi/set', [SettingController::class, 'setLokasi'])->name('lokasi.set');
Route::resource('setting', SettingController::class);
Route::post('/setting/lokasi', [SettingController::class, 'storeLokasi'])->name('setting.lokasi.store');
Route::put('/setting/lokasi/{kode_lokasi}', [SettingController::class, 'updateLokasi'])->name('setting.lokasi.update');
Route::delete('/setting/lokasi/{kode_lokasi}', [SettingController::class, 'destroyLokasi'])->name('setting.lokasi.destroy');
Route::post('/setting/user', [SettingController::class, 'storeUser'])->name('setting.user.store');
Route::put('/setting/user/{id}', [SettingController::class, 'updateUser'])->name('setting.user.update');
Route::delete('/setting/user/{id}', [SettingController::class, 'destroyUser'])->name('setting.user.destroy');