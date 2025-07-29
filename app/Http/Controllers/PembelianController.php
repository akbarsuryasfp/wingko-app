<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Helpers\JurnalHelper;
use App\Models\JadwalProduksi;

class PembelianController extends Controller
{
    // Untuk pembelian berdasarkan order (tetap menggunakan create.blade.php)
    public function create(Request $request)
    {
        // Generate kode pembelian otomatis
        $last = DB::table('t_pembelian')
            ->whereRaw("no_pembelian REGEXP '^PB[0-9]{8}$'")
            ->orderBy('no_pembelian', 'desc')
            ->first();

        if ($last && preg_match('/PB(\d{8})/', $last->no_pembelian, $match)) {
            $nextNumber = (int)$match[1] + 1;
        } else {
            $nextNumber = 1;
        }
        $kode_pembelian = 'PB' . str_pad($nextNumber, 8, '0', STR_PAD_LEFT);

        // Ambil data supplier dan bahan untuk form
        $suppliers = DB::table('t_supplier')->get();
        $bahan = DB::table('t_bahan')->get();

        // Ambil data terima bahan untuk dropdown (jika ada)
        $terimabahan = DB::table('t_terimabahan')
            ->leftJoin('t_supplier', 't_terimabahan.kode_supplier', '=', 't_supplier.kode_supplier')
            ->select('t_terimabahan.*', 't_supplier.nama_supplier')
            ->whereNotIn('t_terimabahan.no_terima_bahan', function($q) {
                $q->select('no_terima_bahan')->from('t_pembelian');
            })
            ->get();

        return view('pembelian.create', compact('kode_pembelian', 'suppliers', 'bahan', 'terimabahan'));
    }

    public function createLangsung()
    {
        // Generate kode pembelian otomatis
        $last = DB::table('t_pembelian')->orderBy('no_pembelian', 'desc')->first();
        if ($last && preg_match('/PBL(\d+)/', $last->no_pembelian, $match)) {
            $nextNumber = (int)$match[1] + 1;
        } else {
            $nextNumber = 1;
        }
        $kode_pembelian = 'PBL' . str_pad($nextNumber, 9, '0', STR_PAD_LEFT);
    
        // Ambil data supplier dan bahan
        $suppliers = DB::table('t_supplier')->get();
        $bahan = DB::table('t_bahan')->get();
    
        // --- Kebutuhan produksi dari jadwal ---
        $jadwalList = DB::table('t_jadwal_produksi')
            ->join('t_jadwal_produksi_detail', 't_jadwal_produksi.kode_jadwal', '=', 't_jadwal_produksi_detail.kode_jadwal')
            ->join('t_produk', 't_jadwal_produksi_detail.kode_produk', '=', 't_produk.kode_produk')
            ->join('t_resep', 't_produk.kode_produk', '=', 't_resep.kode_produk')
            ->join('t_resep_detail', 't_resep.kode_resep', '=', 't_resep_detail.kode_resep')
            ->join('t_bahan', 't_resep_detail.kode_bahan', '=', 't_bahan.kode_bahan')
            ->select(
                't_bahan.kode_bahan',
                't_bahan.nama_bahan',
                't_bahan.satuan',
                't_bahan.stok',
                't_jadwal_produksi_detail.jumlah',
                't_resep_detail.jumlah_kebutuhan'
            )
            ->get();
    
        $kebutuhan = [];
        foreach ($jadwalList as $item) {
            $kode_bahan = $item->kode_bahan;
            $total = $item->jumlah * $item->jumlah_kebutuhan;
            if (!isset($kebutuhan[$kode_bahan])) {
                $kebutuhan[$kode_bahan] = [
                    'kode_bahan' => $kode_bahan,
                    'nama_bahan' => $item->nama_bahan,
                    'satuan' => $item->satuan,
                    'jumlah' => 0,
                    'stok' => $item->stok
                ];
            }
            $kebutuhan[$kode_bahan]['jumlah'] += $total;
        }
    
        // --- Kekurangan bahan dari kebutuhan produksi ---
        $bahanKurangProduksi = [];
        foreach ($kebutuhan as $kode_bahan => $b) {
            if ($b['stok'] < $b['jumlah']) {
                $bahanKurangProduksi[] = [
                    'kode_bahan' => $kode_bahan,
                    'nama_bahan' => $b['nama_bahan'],
                    'satuan' => $b['satuan'],
                    'jumlah_beli' => $b['jumlah'] - $b['stok']
                ];
            }
        }
    
        // --- Bahan di bawah stok minimal ---
        $bahanKurangStokMin = DB::table('t_bahan')
            ->select(
                'kode_bahan',
                'nama_bahan',
                'satuan',
                'stok',
                'stokmin',
                DB::raw('(stokmin - stok) as jumlah_beli')
            )
            ->whereColumn('stok', '<', 'stokmin')
            ->get()
            ->map(function($item) {
                return [
                    'kode_bahan' => $item->kode_bahan,
                    'nama_bahan' => $item->nama_bahan,
                    'satuan' => $item->satuan,
                    'jumlah_beli' => $item->jumlah_beli
                ];
            })->toArray();
    
        // --- Gabungkan kekurangan produksi dan stok minimal ---
        $bahanKurangLangsung = array_merge($bahanKurangProduksi, $bahanKurangStokMin);
    
        // --- Prediksi kebutuhan harian ---
        $today = date('Y-m-d');
        $bahansPrediksiHarian = DB::table('t_bahan')
            ->where('frekuensi_pembelian', 'Harian')
            ->get()
            ->map(function($item) {
                return [
                    'kode_bahan' => $item->kode_bahan,
                    'nama_bahan' => $item->nama_bahan,
                    'satuan' => $item->satuan,
                    'jumlah_per_order' => $item->interval ?? 1
                ];
            })->toArray();
    
        // --- Stok minimal untuk modal khusus ---
        $stokMinList = $this->getBahanStokMinimal();
    
        return view('pembelian.langsung', compact(
            'kode_pembelian',
            'suppliers',
            'bahan',
            'bahanKurangProduksi', // Only production shortage
            'stokMinList',         // Only minimum stock
            'bahansPrediksiHarian' // Daily prediction
        ));
    }

    // Simpan data untuk pembelian berdasarkan order (create.blade.php)
    public function store(Request $request)
    {
        $request->validate([
            'kode_pembelian'     => 'required|string',
            'tanggal_pembelian'  => 'required|date',
            'no_terima_bahan'    => 'required|string',
            'kode_supplier'      => 'required|string',
            'total_harga'        => 'required|numeric|min:0',
            'diskon'             => 'required|numeric|min:0',
            'ongkir'             => 'required|numeric|min:0',
            'total_pembelian'    => 'required|numeric|min:0',
            'total_bayar'        => 'required|numeric|min:0',
            'metode_bayar'       => 'required|string', // ganti dari jenis_pembayaran
            'jatuh_tempo' => 'nullable|date',
        ]);

        $uang_muka = 0;
        // Ambil no_order_beli dari t_terimabahan
        $no_order_beli = DB::table('t_terimabahan')
            ->where('no_terima_bahan', $request->no_terima_bahan)
            ->value('no_order_beli');

        // Ambil uang muka dari t_order_beli (jika ada)
        $uang_muka_awal = DB::table('t_order_beli')
            ->where('no_order_beli', $no_order_beli)
            ->value('uang_muka') ?? 0;

        // Ambil semua no_terima_bahan dari order ini
        $no_terima_bahan_list = DB::table('t_terimabahan')
            ->where('no_order_beli', $no_order_beli)
            ->pluck('no_terima_bahan');

        // Hitung total uang muka yang sudah dipakai di pembelian sebelumnya
        $uang_muka_terpakai = 0;
        if ($no_terima_bahan_list->count() > 0) {
            $uang_muka_terpakai = DB::table('t_pembelian')
                ->whereIn('no_terima_bahan', $no_terima_bahan_list)
                ->sum('uang_muka');
        }

        // Sisa uang muka sebelum transaksi ini
        $sisa_uang_muka = $uang_muka_awal - $uang_muka_terpakai;
        if ($sisa_uang_muka < 0) $sisa_uang_muka = 0;

        // Total pembelian (tanpa dikurangi uang muka)
        $total_pembelian = $request->total_harga - $request->diskon + $request->ongkir;
        if ($total_pembelian < 0) $total_pembelian = 0;

        // Uang muka yang dipakai untuk transaksi ini
        if ($total_pembelian < $sisa_uang_muka) {
            $uang_muka_dipakai = $total_pembelian;
            $sisa_uang_muka_baru = $sisa_uang_muka - $uang_muka_dipakai;
        } else {
            $uang_muka_dipakai = $sisa_uang_muka;
            $sisa_uang_muka_baru = 0;
        }

        // Kurang bayar = total_pembelian - uang_muka_dipakai - total_bayar
        $kurang_bayar = $total_pembelian - $uang_muka_dipakai - $request->total_bayar;
        if ($kurang_bayar < 0) $kurang_bayar = 0;

        $status = $kurang_bayar > 0 ? 'Hutang' : 'Lunas';

        // Simpan data pembelian
        DB::table('t_pembelian')->insert([
            'no_pembelian'     => $request->kode_pembelian,
            'tanggal_pembelian'=> $request->tanggal_pembelian,
            'no_terima_bahan'  => $request->no_terima_bahan,
            'kode_supplier'    => $request->kode_supplier,
            'total_harga'      => $request->total_harga,
            'diskon'           => $request->diskon,
            'ongkir'           => $request->ongkir,
            'total_pembelian'  => $total_pembelian,
            'total_bayar'      => $request->total_bayar,
            'hutang'           => $kurang_bayar,
            'status'           => $status,
            'jenis_pembelian'  => 'pembelian berdasarkan order',
            'uang_muka'        => $uang_muka_dipakai,
            'metode_bayar'     => $request->metode_bayar,
            'no_nota'          => $request->no_nota,

        ]);

        if ($kurang_bayar > 0) {
            // Generate no_utang otomatis
            $lastUtang = DB::table('t_utang')->orderBy('no_utang', 'desc')->first();
            if ($lastUtang && preg_match('/U(\d+)/', $lastUtang->no_utang, $match)) {
                $nextUtang = (int)$match[1] + 1;
            } else {
                $nextUtang = 1;
            }
            $no_utang = 'U' . str_pad($nextUtang, 7, '0', STR_PAD_LEFT);

            DB::table('t_utang')->insert([
                'no_utang'      => $no_utang,
                'no_pembelian'  => $request->kode_pembelian,
                'kode_supplier' => $request->kode_supplier,
                'total_tagihan' => $kurang_bayar,      // sesuai permintaan
                'total_bayar'   => 0,            // sesuai permintaan
                'sisa_utang'    => $kurang_bayar,      // sesuai permintaan
                'jatuh_tempo'   => $request->jatuh_tempo,
            ]);
        }

        // --- JURNAL UMUM & DETAIL ---
        $no_jurnal = JurnalHelper::generateNoJurnal();

        DB::table('t_jurnal_umum')->insert([
            'no_jurnal'   => $no_jurnal,
            'tanggal'     => $request->tanggal_pembelian,
            'keterangan'  => 'Pembelian ' . $request->kode_pembelian,
            'nomor_bukti' => $request->kode_pembelian,
        ]);

        // Nilai-nilai jurnal
        $nilai_persediaan = $request->total_harga;
        $ongkir           = $request->ongkir;
        $diskon           = $request->diskon;
        $dibayar_sekarang = $request->total_bayar;
        $uang_muka        = $uang_muka_dipakai ?? 0; // dari logika sebelumnya
        $sisa_hutang      = $kurang_bayar;

        // Kode akun dari mapping JurnalHelper
        $kode_akun_persediaan = JurnalHelper::getKodeAkun('persediaan_bahan');
        $kode_akun_ongkir     = JurnalHelper::getKodeAkun('ongkos_kirim');
        $kode_akun_diskon     = JurnalHelper::getKodeAkun('diskon_pembelian');
        $kode_akun_kas        = JurnalHelper::getKodeAkun('kas_bank');
        $kode_akun_uangmuka   = JurnalHelper::getKodeAkun('uang_muka');
        $kode_akun_hutang     = JurnalHelper::getKodeAkun('utang_usaha');

        // 1. Persediaan Barang (Debit)
        DB::table('t_jurnal_detail')->insert([
            'no_jurnal_detail' => JurnalHelper::generateNoJurnalDetail($no_jurnal),
            'no_jurnal'        => $no_jurnal,
            'kode_akun'        => $kode_akun_persediaan,
            'debit'            => $nilai_persediaan,
            'kredit'           => 0,
        ]);

        // 2. Ongkos Kirim (Debit)
        if ($ongkir > 0) {
            DB::table('t_jurnal_detail')->insert([
                'no_jurnal_detail' => JurnalHelper::generateNoJurnalDetail($no_jurnal),
                'no_jurnal'        => $no_jurnal,
                'kode_akun'        => $kode_akun_ongkir,
                'debit'            => $ongkir,
                'kredit'           => 0,
            ]);
        }

        // 3. Diskon Pembelian (Kredit)
        if ($diskon > 0) {
            DB::table('t_jurnal_detail')->insert([
                'no_jurnal_detail' => JurnalHelper::generateNoJurnalDetail($no_jurnal),
                'no_jurnal'        => $no_jurnal,
                'kode_akun'        => $kode_akun_diskon,
                'debit'            => 0,
                'kredit'           => $diskon,
            ]);
        }

        // 4. Kas/Bank (Kredit)
        if ($dibayar_sekarang > 0) {
            DB::table('t_jurnal_detail')->insert([
                'no_jurnal_detail' => JurnalHelper::generateNoJurnalDetail($no_jurnal),
                'no_jurnal'        => $no_jurnal,
                'kode_akun'        => $kode_akun_kas,
                'debit'            => 0,
                'kredit'           => $dibayar_sekarang,
            ]);
        }

        // 5. Uang Muka (Kredit)
        if ($uang_muka > 0) {
            DB::table('t_jurnal_detail')->insert([
                'no_jurnal_detail' => JurnalHelper::generateNoJurnalDetail($no_jurnal),
                'no_jurnal'        => $no_jurnal,
                'kode_akun'        => $kode_akun_uangmuka,
                'debit'            => 0,
                'kredit'           => $uang_muka,
            ]);
        }

        // 6. Hutang Usaha (Kredit)
        if ($sisa_hutang > 0) {
            DB::table('t_jurnal_detail')->insert([
                'no_jurnal_detail' => JurnalHelper::generateNoJurnalDetail($no_jurnal),
                'no_jurnal'        => $no_jurnal,
                'kode_akun'        => $kode_akun_hutang,
                'debit'            => 0,
                'kredit'           => $sisa_hutang,
            ]);
        }

        return redirect()->route('pembelian.index')->with('success', 'Data pembelian berhasil disimpan.');
    }

    // Simpan data untuk pembelian langsung (langsung.blade.php)
    public function storeLangsung(Request $request)
    {
        $request->validate([
            'kode_pembelian'     => 'required|string',
            'tanggal_pembelian'  => 'required|date',
            'kode_supplier'      => 'required|string',
            'total_harga'        => 'required|numeric|min:0',
            'diskon'             => 'required|numeric|min:0',
            'ongkir'             => 'required|numeric|min:0',
            'total_pembelian'    => 'required|numeric|min:0',
            'total_bayar'        => 'required|numeric|min:0',
            'bahan'              => 'required|array',
            'jumlah'             => 'required|array',
            'harga'              => 'required|array',
            'tanggal_exp'        => 'required|array',
            'metode_bayar'       => 'required|string', // ganti dari jenis_pembayaran
            'jatuh_tempo' => 'nullable|date',
        ]);

        $hutang = $request->total_pembelian - $request->total_bayar;
        $status = $hutang > 0 ? 'Hutang' : 'Lunas';

        // --- 1. Generate no_terima_bahan otomatis (increment seperti sebelumnya) ---
$last = DB::table('t_terimabahan')
    ->whereRaw("no_terima_bahan REGEXP '^TB[0-9]{6}$'")
    ->orderBy('no_terima_bahan', 'desc')
    ->first();

$no_terima_bahan = $last
    ? 'TB' . str_pad((int)substr($last->no_terima_bahan, 2) + 1, 6, '0', STR_PAD_LEFT)
    : 'TB000001';

        // --- 2. Simpan ke t_terimabahan ---
        DB::table('t_terimabahan')->insert([
            'no_terima_bahan' => $no_terima_bahan,
            'no_pembelian'    => $request->kode_pembelian,
            'tanggal_terima'  => $request->tanggal_pembelian,
            'kode_supplier'   => $request->kode_supplier,
            'status'          => 'Selesai',
        ]);

        // --- 3. Simpan ke t_terimab_detail (banyak detail untuk satu header) ---
        foreach ($request->bahan as $i => $kode_bahan) {
            $jumlah = $request->jumlah[$i];
            $harga  = $request->harga[$i];
            $tanggal_exp = $request->tanggal_exp[$i] ?? null;

            // Gunakan kode unik untuk setiap detail
            $no_terimab_detail = 'TD' . date('ymdHis') . rand(100,999);

            DB::table('t_terimab_detail')->insert([
                'no_terimab_detail' => $no_terimab_detail,
                'no_terima_bahan'   => $no_terima_bahan, // sama untuk semua detail dalam satu transaksi
                'kode_bahan'        => $kode_bahan,
                'bahan_masuk'       => $jumlah,
                'harga_beli'        => $harga,
                'total'             => $jumlah * $harga,
                'tanggal_exp'       => $tanggal_exp,
            ]);

            // Insert ke t_kartupersbahan
            DB::table('t_kartupersbahan')->insert([
                'no_transaksi' => $no_terima_bahan,
                'tanggal'      => $request->tanggal_pembelian,
                'kode_bahan'   => $kode_bahan,
                'masuk'        => $jumlah,
                'keluar'       => 0,
                'harga'        => $harga,
                'satuan'       => DB::table('t_bahan')->where('kode_bahan', $kode_bahan)->value('satuan'),
                'keterangan'   => 'Pembelian Langsung',
                'tanggal_exp'  => $tanggal_exp,
            ]);

            // Update stok di t_bahan
            $saldo = DB::table('t_kartupersbahan')
                ->where('kode_bahan', $kode_bahan)
                ->selectRaw('COALESCE(SUM(masuk),0) - COALESCE(SUM(keluar),0) as saldo')
                ->value('saldo');
            DB::table('t_bahan')->where('kode_bahan', $kode_bahan)->update(['stok' => $saldo]);
        }

        // --- 4. Simpan ke t_pembelian ---
        DB::table('t_pembelian')->insert([
            'no_pembelian'     => $request->kode_pembelian,
            'tanggal_pembelian'  => $request->tanggal_pembelian,
            'no_terima_bahan'  => $no_terima_bahan,
            'kode_supplier'    => $request->kode_supplier,
            'total_harga'      => $request->total_harga,
            'diskon'           => $request->diskon,
            'ongkir'           => $request->ongkir,
            'total_pembelian'  => $request->total_pembelian,
            'total_bayar'      => $request->total_bayar,
            'hutang'           => $hutang,
            'status'           => $status,
            'jenis_pembelian'  => 'pembelian langsung',
            'metode_bayar'     => $request->metode_bayar, 
            'no_nota'          => $request->no_nota,
            'uang_muka'        => 0, // Tidak ada uang muka untuk pembelian langsung
        ]);

        if ($hutang > 0) {
    // Generate no_utang otomatis
    $lastUtang = DB::table('t_utang')->orderBy('no_utang', 'desc')->first();
    if ($lastUtang && preg_match('/U(\d+)/', $lastUtang->no_utang, $match)) {
        $nextUtang = (int)$match[1] + 1;
    } else {
        $nextUtang = 1;
    }
    $no_utang = 'U' . str_pad($nextUtang, 7, '0', STR_PAD_LEFT);

    DB::table('t_utang')->insert([
        'no_utang'      => $no_utang,
        'no_pembelian'  => $request->kode_pembelian,
        'kode_supplier' => $request->kode_supplier,
        'total_tagihan' => $hutang,
        'total_bayar'   => 0,
        'sisa_utang'    => $hutang,
        'jatuh_tempo'   => $request->jatuh_tempo, // pastikan input jatuh_tempo sudah ada di form
    ]);
}
$no_jurnal = JurnalHelper::generateNoJurnal();

DB::table('t_jurnal_umum')->insert([
    'no_jurnal'   => $no_jurnal,
    'tanggal'     => $request->tanggal_pembelian,
    'keterangan'  => 'Pembelian Langsung ' . $request->kode_pembelian,
    'nomor_bukti' => $request->kode_pembelian,
]);

// Nilai-nilai jurnal
$nilai_persediaan = $request->total_harga;
$ongkir           = $request->ongkir;
$diskon           = $request->diskon;
$dibayar_sekarang = $request->total_bayar;
$sisa_hutang      = $hutang;

// Kode akun dari mapping JurnalHelper
$kode_akun_persediaan = JurnalHelper::getKodeAkun('persediaan_bahan');
$kode_akun_ongkir     = JurnalHelper::getKodeAkun('ongkos_kirim');
$kode_akun_diskon     = JurnalHelper::getKodeAkun('diskon_pembelian');
$kode_akun_kas        = JurnalHelper::getKodeAkun('kas_bank');
$kode_akun_hutang     = JurnalHelper::getKodeAkun('utang_usaha');

// 1. Persediaan Barang (Debit)
DB::table('t_jurnal_detail')->insert([
    'no_jurnal_detail' => JurnalHelper::generateNoJurnalDetail($no_jurnal),
    'no_jurnal'        => $no_jurnal,
    'kode_akun'        => $kode_akun_persediaan,
    'debit'            => $nilai_persediaan,
    'kredit'           => 0,
]);

// 2. Ongkos Kirim (Debit)
if ($ongkir > 0) {
    DB::table('t_jurnal_detail')->insert([
        'no_jurnal_detail' => JurnalHelper::generateNoJurnalDetail($no_jurnal),
        'no_jurnal'        => $no_jurnal,
        'kode_akun'        => $kode_akun_ongkir,
        'debit'            => $ongkir,
        'kredit'           => 0,
    ]);
}

// 3. Diskon Pembelian (Kredit)
if ($diskon > 0) {
    DB::table('t_jurnal_detail')->insert([
        'no_jurnal_detail' => JurnalHelper::generateNoJurnalDetail($no_jurnal),
        'no_jurnal'        => $no_jurnal,
        'kode_akun'        => $kode_akun_diskon,
        'debit'            => 0,
        'kredit'           => $diskon,
    ]);
}

// 4. Kas/Bank (Kredit)
if ($dibayar_sekarang > 0) {
    DB::table('t_jurnal_detail')->insert([
        'no_jurnal_detail' => JurnalHelper::generateNoJurnalDetail($no_jurnal),
        'no_jurnal'        => $no_jurnal,
        'kode_akun'        => $kode_akun_kas,
        'debit'            => 0,
        'kredit'           => $dibayar_sekarang,
    ]);
}

// 5. Hutang Usaha (Kredit)
if ($sisa_hutang > 0) {
    DB::table('t_jurnal_detail')->insert([
        'no_jurnal_detail' => JurnalHelper::generateNoJurnalDetail($no_jurnal),
        'no_jurnal'        => $no_jurnal,
        'kode_akun'        => $kode_akun_hutang,
        'debit'            => 0,
        'kredit'           => $sisa_hutang,
    ]);
}
        return redirect()->route('pembelian.index')->with('success', 'Data pembelian & penerimaan berhasil disimpan.');
    }

    // Metode index() dan show() tetap seperti sebelumnya...
public function index(Request $request)
{
    $query = DB::table('t_pembelian')
        ->leftJoin('t_supplier', 't_pembelian.kode_supplier', '=', 't_supplier.kode_supplier')
        ->leftJoin('t_utang', 't_pembelian.no_pembelian', '=', 't_utang.no_pembelian')
        ->select(
            't_pembelian.*',
            't_supplier.nama_supplier',
            't_utang.status as utang_status'
        );

    // Filter jenis pembelian
    if ($request->jenis_pembelian) {
        $query->where('t_pembelian.jenis_pembelian', $request->jenis_pembelian);
    }

    // Filter tanggal
    $tanggal_mulai = $request->tanggal_mulai ?? now()->startOfMonth()->format('Y-m-d');
    $tanggal_selesai = $request->tanggal_selesai ?? now()->endOfMonth()->format('Y-m-d');
    $query->whereBetween('t_pembelian.tanggal_pembelian', [$tanggal_mulai, $tanggal_selesai]);

    // Filter status lunas
    if ($request->status_lunas == 'lunas') {
        $query->where('t_pembelian.hutang', '<=', 0);
    } elseif ($request->status_lunas == 'belum') {
        $query->where('t_pembelian.hutang', '>', 0);
    }

    // Filter search (No Pembelian / Nama Supplier)
    if ($request->search) {
        $search = trim($request->search);
        $query->where(function($q) use ($search) {
            $q->where('t_pembelian.no_pembelian', 'like', "%{$search}%")
              ->orWhere('t_supplier.nama_supplier', 'like', "%{$search}%");
        });
    }

    $pembelian = $query->orderBy('t_pembelian.tanggal_pembelian', 'asc')->get();

    // Sinkronisasi status: jika utang_status == 'lunas', maka status pembelian juga 'Lunas' dan hutang = 0
    foreach ($pembelian as $p) {
        if ($p->utang_status === 'Lunas') {
            DB::table('t_pembelian')
                ->where('no_pembelian', $p->no_pembelian)
                ->update([
                    'status' => 'Lunas',
                    'hutang' => 0
                ]);
            $p->status = 'Lunas';
            $p->hutang = 0;
        }
    }
    return view('pembelian.index', compact('pembelian'));
}
    public function show($no_pembelian)
    {
        $pembelian = DB::table('t_pembelian')
            ->leftJoin('t_supplier', 't_pembelian.kode_supplier', '=', 't_supplier.kode_supplier')
            ->where('t_pembelian.no_pembelian', $no_pembelian)
            ->select('t_pembelian.*', 't_supplier.nama_supplier', 't_pembelian.no_nota')
            ->first();

        if (!$pembelian) {
            abort(404, 'Data pembelian tidak ditemukan.');
        }

        // Ambil jatuh tempo dari t_utang
        $jatuh_tempo = DB::table('t_utang')
            ->where('no_pembelian', $no_pembelian)
            ->value('jatuh_tempo');

        if ($pembelian->no_terima_bahan && $pembelian->no_terima_bahan !== '-') {
            $details = DB::table('t_terimab_detail')
                ->join('t_bahan', 't_terimab_detail.kode_bahan', '=', 't_bahan.kode_bahan')
                ->where('t_terimab_detail.no_terima_bahan', $pembelian->no_terima_bahan)
                ->select(
                    't_bahan.nama_bahan',
                    't_bahan.satuan',
                    't_terimab_detail.bahan_masuk',
                    't_terimab_detail.harga_beli',
                    't_terimab_detail.tanggal_exp',
                    DB::raw('t_terimab_detail.bahan_masuk * t_terimab_detail.harga_beli as subtotal')
                )
                ->get();
        } else {
            $details = collect([]);
        }

        return view('pembelian.detail', compact('pembelian', 'details', 'jatuh_tempo'));
    }

    public function detailTerimaBahan($no_terima_bahan)
    {
        $terimaBahan = \DB::table('t_terimabahan')
            ->join('t_supplier', 't_terimabahan.kode_supplier', '=', 't_supplier.kode_supplier')
            ->where('t_terimabahan.no_terima_bahan', $no_terima_bahan)
            ->select('t_terimabahan.*', 't_supplier.nama_supplier')
            ->first();

        $details = \DB::table('t_terimab_detail')
            ->join('t_bahan', 't_terimab_detail.kode_bahan', '=', 't_bahan.kode_bahan')
            ->where('t_terimab_detail.no_terima_bahan', $no_terima_bahan)
            ->select('t_terimab_detail.*', 't_bahan.nama_bahan', 't_bahan.satuan')
            ->get();

        // Ambil uang muka jika ada order beli
        $uang_muka_awal = 0;
        $sisa_uang_muka = 0;
        if ($terimaBahan && $terimaBahan->no_order_beli) {
            $uang_muka_awal = \DB::table('t_order_beli')
                ->where('no_order_beli', $terimaBahan->no_order_beli)
                ->value('uang_muka') ?? 0;

            // Ambil semua no_terima_bahan dari order ini
            $no_terima_bahan_list = \DB::table('t_terimabahan')
                ->where('no_order_beli', $terimaBahan->no_order_beli)
                ->pluck('no_terima_bahan');

            // Hitung total uang muka yang sudah dipakai di pembelian sebelumnya
            $uang_muka_terpakai = 0;
            if ($no_terima_bahan_list->count() > 0) {
                $uang_muka_terpakai = \DB::table('t_pembelian')
                    ->whereIn('no_terima_bahan', $no_terima_bahan_list)
                    ->sum('uang_muka');
            }

            $sisa_uang_muka = $uang_muka_awal - $uang_muka_terpakai;
            if ($sisa_uang_muka < 0) $sisa_uang_muka = 0;
        }
        $terimaBahan->uang_muka = $uang_muka_awal;
        $terimaBahan->sisa_uang_muka = $sisa_uang_muka;

        return response()->json([
            'terimaBahan' => $terimaBahan,
            'details' => $details,
        ]);
    }

    public function destroy($no_pembelian)
    {
        // Ambil data pembelian
        $pembelian = DB::table('t_pembelian')->where('no_pembelian', $no_pembelian)->first();

        // Ambil no_terima_bahan terkait
        $no_terima_bahan = $pembelian ? $pembelian->no_terima_bahan : null;

        // Hapus data pembelian dan utang
        DB::table('t_pembelian')->where('no_pembelian', $no_pembelian)->delete();
        DB::table('t_utang')->where('no_pembelian', $no_pembelian)->delete();

        // Hapus jurnal umum & detail
        $no_jurnal = DB::table('t_jurnal_umum')->where('nomor_bukti', $no_pembelian)->value('no_jurnal');
        if ($no_jurnal) {
            DB::table('t_jurnal_detail')->where('no_jurnal', $no_jurnal)->delete();
            DB::table('t_jurnal_umum')->where('no_jurnal', $no_jurnal)->delete();
        }

        // Jika pembelian langsung, hapus juga data terima bahan, detail, dan kartu persediaan
        if ($pembelian && $pembelian->jenis_pembelian === 'pembelian langsung' && $no_terima_bahan) {
            // Ambil semua kode_bahan lama
            $kode_bahan_lama = DB::table('t_terimab_detail')->where('no_terima_bahan', $no_terima_bahan)->pluck('kode_bahan');

            // Hapus detail & kartu stok lama
            DB::table('t_terimab_detail')->where('no_terima_bahan', $no_terima_bahan)->delete();
            DB::table('t_kartupersbahan')->where('no_transaksi', $no_terima_bahan)->where('keterangan', 'Pembelian Langsung')->delete();

            // Hapus header terima bahan
            DB::table('t_terimabahan')->where('no_terima_bahan', $no_terima_bahan)->delete();

            // Update stok untuk semua bahan terkait
            foreach ($kode_bahan_lama as $kode_bahan) {
                $saldo = DB::table('t_kartupersbahan')
                    ->where('kode_bahan', $kode_bahan)
                    ->selectRaw('COALESCE(SUM(masuk),0) - COALESCE(SUM(keluar),0) as saldo')
                    ->value('saldo');
                DB::table('t_bahan')->where('kode_bahan', $kode_bahan)->update(['stok' => $saldo]);
            }
        }

        return redirect()->route('pembelian.index')->with('success', 'Data pembelian berhasil dihapus.');
    }

public function edit($no_pembelian)
{
    $pembelian = DB::table('t_pembelian')
        ->where('no_pembelian', $no_pembelian)
        ->first();

    if (!$pembelian) {
        abort(404, 'Data pembelian tidak ditemukan.');
    }

    $suppliers = DB::table('t_supplier')->get();
    $bahan = DB::table('t_bahan')->get();

    // Ambil detail bahan dan nama supplier jika ada no_terima_bahan
    $details = [];
    $nama_supplier = '';
    if ($pembelian->no_terima_bahan && $pembelian->no_terima_bahan !== '-') {
        $details = DB::table('t_terimab_detail')
            ->join('t_bahan', 't_terimab_detail.kode_bahan', '=', 't_bahan.kode_bahan')
            ->where('t_terimab_detail.no_terima_bahan', $pembelian->no_terima_bahan)
            ->select('t_terimab_detail.*', 't_bahan.nama_bahan', 't_bahan.satuan')
            ->get();

        $nama_supplier = DB::table('t_terimabahan')
            ->leftJoin('t_supplier', 't_terimabahan.kode_supplier', '=', 't_supplier.kode_supplier')
            ->where('t_terimabahan.no_terima_bahan', $pembelian->no_terima_bahan)
            ->value('t_supplier.nama_supplier');
    }

    $jatuh_tempo = DB::table('t_utang')
        ->where('no_pembelian', $no_pembelian)
        ->value('jatuh_tempo');

    // Data untuk modal kebutuhan bahan (jika pembelian langsung)
    $bahanKurangProduksi = [];
    $bahansPrediksiHarian = [];
    $stokMinList = [];

    if ($pembelian->jenis_pembelian == 'pembelian langsung') {
        // Ambil data kekurangan produksi
        $jadwal = JadwalProduksi::latest('tanggal_jadwal')->with('details.produk.resep.details.bahan')->first();
        $kebutuhan = [];
        if ($jadwal) {
            foreach ($jadwal->details as $detail) {
                $produk = $detail->produk;
                $jumlah = $detail->jumlah;
                if ($produk && $produk->resep) {
                    foreach ($produk->resep->details as $rdetail) {
                        $kode_bahan = $rdetail->kode_bahan;
                        $total = $jumlah * $rdetail->jumlah_kebutuhan;
                        if (!isset($kebutuhan[$kode_bahan])) {
                            $kebutuhan[$kode_bahan] = [
                                'kode_bahan' => $kode_bahan,
                                'nama_bahan' => $rdetail->bahan->nama_bahan ?? $kode_bahan,
                                'satuan'     => $rdetail->satuan,
                                'jumlah'     => 0,
                            ];
                        }
                        $kebutuhan[$kode_bahan]['jumlah'] += $total;
                    }
                }
            }
        }

        // Bandingkan kebutuhan dengan stok
        foreach ($kebutuhan as $kode_bahan => $b) {
            $stok = DB::table('t_kartupersbahan')
                ->where('kode_bahan', $kode_bahan)
                ->selectRaw('COALESCE(SUM(masuk),0) - COALESCE(SUM(keluar),0) as stok')
                ->value('stok');
            if ($stok < $b['jumlah']) {
                $bahanKurangProduksi[] = [
                    'kode_bahan'  => $kode_bahan,
                    'nama_bahan'  => $b['nama_bahan'],
                    'satuan'      => $b['satuan'],
                    'jumlah_beli' => $b['jumlah'] - $stok,
                ];
            }
        }

        // Ambil prediksi kebutuhan harian
        $bahansPrediksiHarian = DB::table('t_bahan')
            ->where('frekuensi_pembelian', 'Harian')
            ->select('kode_bahan', 'nama_bahan', 'satuan', 'jumlah_per_order')
            ->get()
            ->toArray();

        // Ambil bahan dengan stok di bawah minimal
        $stokMinList = DB::table('t_bahan')
            ->leftJoin('t_kartupersbahan', 't_bahan.kode_bahan', '=', 't_kartupersbahan.kode_bahan')
            ->select(
                't_bahan.kode_bahan',
                't_bahan.nama_bahan',
                't_bahan.satuan',
                't_bahan.stokmin',
                DB::raw('COALESCE(SUM(t_kartupersbahan.masuk),0) - COALESCE(SUM(t_kartupersbahan.keluar),0) as stok')
            )
            ->groupBy('t_bahan.kode_bahan', 't_bahan.nama_bahan', 't_bahan.satuan', 't_bahan.stokmin')
            ->havingRaw('stok < t_bahan.stokmin')
            ->get();
    }

    return view('pembelian.edit', compact(
        'pembelian',
        'suppliers',
        'bahan',
        'details',
        'nama_supplier',
        'jatuh_tempo',
        'bahanKurangProduksi',
        'bahansPrediksiHarian',
        'stokMinList'
    ));
}
public function update(Request $request, $no_pembelian)
{
    $request->validate([
        'tanggal_pembelian'  => 'required|date',
        'metode_bayar'       => 'required|string',
        'diskon'             => 'required|numeric|min:0',
        'ongkir'             => 'required|numeric|min:0',
        'total_bayar'        => 'required|numeric|min:0',
        'no_nota'            => 'nullable|string',
        'jatuh_tempo'        => 'nullable|date',
        // validasi detail bahan hanya untuk pembelian langsung
        'bahan'              => 'sometimes|array',
        'jumlah'             => 'sometimes|array',
        'harga'              => 'sometimes|array',
        'tanggal_exp'        => 'sometimes|array',
    ]);

    $pembelian = DB::table('t_pembelian')->where('no_pembelian', $no_pembelian)->first();

    // --- Update detail bahan jika pembelian langsung ---
    if ($pembelian->jenis_pembelian == 'pembelian langsung' && $request->has('bahan')) {
        $no_terima_bahan = $pembelian->no_terima_bahan;

        // Hapus detail lama
        DB::table('t_terimab_detail')->where('no_terima_bahan', $no_terima_bahan)->delete();
        DB::table('t_kartupersbahan')->where('no_transaksi', $no_terima_bahan)->where('keterangan', 'Pembelian Langsung')->delete();

        $total_harga = 0;
        foreach ($request->bahan as $i => $kode_bahan) {
            $jumlah = $request->jumlah[$i];
            $harga  = $request->harga[$i];
            $tanggal_exp = $request->tanggal_exp[$i] ?? null;

            $no_terimab_detail = 'TD' . date('ymdHis') . rand(100,999);

            DB::table('t_terimab_detail')->insert([
                'no_terimab_detail' => $no_terimab_detail,
                'no_terima_bahan'   => $no_terima_bahan,
                'kode_bahan'        => $kode_bahan,
                'bahan_masuk'       => $jumlah,
                'harga_beli'        => $harga,
                'total'             => $jumlah * $harga,
                'tanggal_exp'       => $tanggal_exp,
            ]);

            DB::table('t_kartupersbahan')->insert([
                'no_transaksi' => $no_terima_bahan,
                'tanggal'      => $request->tanggal_pembelian,
                'kode_bahan'   => $kode_bahan,
                'masuk'        => $jumlah,
                'keluar'       => 0,
                'harga'        => $harga,
                'satuan'       => DB::table('t_bahan')->where('kode_bahan', $kode_bahan)->value('satuan'),
                'keterangan'   => 'Pembelian Langsung',
                'tanggal_exp'  => $tanggal_exp,
            ]);

            // Update stok
            $saldo = DB::table('t_kartupersbahan')
                ->where('kode_bahan', $kode_bahan)
                ->selectRaw('COALESCE(SUM(masuk),0) - COALESCE(SUM(keluar),0) as saldo')
                ->value('saldo');
            DB::table('t_bahan')->where('kode_bahan', $kode_bahan)->update(['stok' => $saldo]);

            $total_harga += $jumlah * $harga;
        }
    } else {
        // Jika bukan pembelian langsung, ambil total_harga lama
        $total_harga = $pembelian->total_harga;
    }

    // --- Hitung total pembelian dan pembayaran ---
    $total_pembelian = $total_harga - $request->diskon + $request->ongkir;
    if ($total_pembelian < 0) $total_pembelian = 0;

    $uang_muka_dipakai = ($pembelian->jenis_pembelian == 'pembelian langsung') ? 0 : ($pembelian->uang_muka ?? 0);
    $kurang_bayar = $total_pembelian - $uang_muka_dipakai - $request->total_bayar;
    if ($kurang_bayar < 0) $kurang_bayar = 0;
    $status = $kurang_bayar > 0 ? 'Hutang' : 'Lunas';


        // Ambil uang muka awal dan sisa uang muka (jika pembelian berdasarkan order)
        $uang_muka_awal = 0;
        $sisa_uang_muka = 0;
        if ($pembelian->no_terima_bahan) {
            $no_order_beli = DB::table('t_terimabahan')
                ->where('no_terima_bahan', $pembelian->no_terima_bahan)
                ->value('no_order_beli');
            if ($no_order_beli) {
                $uang_muka_awal = DB::table('t_order_beli')
                    ->where('no_order_beli', $no_order_beli)
                    ->value('uang_muka') ?? 0;

                $no_terima_bahan_list = DB::table('t_terimabahan')
                    ->where('no_order_beli', $no_order_beli)
                    ->pluck('no_terima_bahan');

                $uang_muka_terpakai = 0;
                if ($no_terima_bahan_list->count() > 0) {
                    $uang_muka_terpakai = DB::table('t_pembelian')
                        ->whereIn('no_terima_bahan', $no_terima_bahan_list)
                        ->where('no_pembelian', '!=', $no_pembelian) // kecuali pembelian ini
                        ->sum('uang_muka');
                }
                $sisa_uang_muka = $uang_muka_awal - $uang_muka_terpakai;
                if ($sisa_uang_muka < 0) $sisa_uang_muka = 0;
            }
        }

        // Hitung total harga dari detail (atau gunakan field lama)
        $total_harga = $pembelian->total_harga;

        // Hitung total pembelian (tanpa dikurangi uang muka)
        $total_pembelian = $total_harga - $request->diskon + $request->ongkir;
        if ($total_pembelian < 0) $total_pembelian = 0;

        // Uang muka yang dipakai untuk transaksi ini
        if ($total_pembelian < $sisa_uang_muka) {
            $uang_muka_dipakai = $total_pembelian;
            $sisa_uang_muka_baru = $sisa_uang_muka - $uang_muka_dipakai;
        } else {
            $uang_muka_dipakai = $sisa_uang_muka;
            $sisa_uang_muka_baru = 0;
        }

        // Kurang bayar = total_pembelian - uang_muka_dipakai - total_bayar
        $kurang_bayar = $total_pembelian - $uang_muka_dipakai - $request->total_bayar;
        if ($kurang_bayar < 0) $kurang_bayar = 0;

        $status = $kurang_bayar > 0 ? 'Hutang' : 'Lunas';

        DB::table('t_pembelian')->where('no_pembelian', $no_pembelian)->update([
            'tanggal_pembelian' => $request->tanggal_pembelian,
            'metode_bayar'      => $request->metode_bayar,
            'diskon'            => $request->diskon,
            'ongkir'            => $request->ongkir,
            'total_pembelian'   => $total_pembelian,
            'total_bayar'       => $request->total_bayar,
            'hutang'            => $kurang_bayar,
            'status'            => $status,
            'no_nota'           => $request->no_nota,
            'uang_muka'         => $uang_muka_dipakai,
        ]);

        // (Opsional) Update t_utang jika perlu, sesuai logika Anda
// Cek apakah sudah ada utang untuk pembelian ini
$utang = DB::table('t_utang')->where('no_pembelian', $no_pembelian)->first();

if ($kurang_bayar > 0) {
    if ($utang) {
        // Update utang
        DB::table('t_utang')->where('no_pembelian', $no_pembelian)->update([
            'total_tagihan' => $kurang_bayar,
            'total_bayar'   => 0, // atau update sesuai pembayaran jika ada
            'sisa_utang'    => $kurang_bayar,
            'jatuh_tempo'   => $request->jatuh_tempo, // <-- update di sini
        ]);
    } else {
        // Generate no_utang otomatis
        $lastUtang = DB::table('t_utang')->orderBy('no_utang', 'desc')->first();
        if ($lastUtang && preg_match('/U(\d+)/', $lastUtang->no_utang, $match)) {
            $nextUtang = (int)$match[1] + 1;
        } else {
            $nextUtang = 1;
        }
        $no_utang = 'U' . str_pad($nextUtang, 7, '0', STR_PAD_LEFT);

        DB::table('t_utang')->insert([
            'no_utang'      => $no_utang,
            'no_pembelian'  => $no_pembelian,
            'kode_supplier' => $pembelian->kode_supplier,
            'total_tagihan' => $kurang_bayar,
            'total_bayar'   => 0,
            'sisa_utang'    => $kurang_bayar,
        ]);
    }
} else {
    // Jika sudah lunas, hapus utang jika ada
    if ($utang) {
        DB::table('t_utang')->where('no_pembelian', $no_pembelian)->delete();
    }
}

// Hapus jurnal lama
$no_jurnal = DB::table('t_jurnal_umum')->where('nomor_bukti', $no_pembelian)->value('no_jurnal');
if ($no_jurnal) {
    DB::table('t_jurnal_detail')->where('no_jurnal', $no_jurnal)->delete();
    DB::table('t_jurnal_umum')->where('no_jurnal', $no_jurnal)->delete();
}

// Insert jurnal baru
$no_jurnal_baru = JurnalHelper::generateNoJurnal();

DB::table('t_jurnal_umum')->insert([
    'no_jurnal'   => $no_jurnal_baru,
    'tanggal'     => $request->tanggal_pembelian,
    'keterangan'  => 'Pembelian ' . $no_pembelian, // atau isi sesuai kebutuhan Anda
    'nomor_bukti' => $no_pembelian,
]);

// Nilai-nilai jurnal
$nilai_persediaan = $total_harga;
$ongkir           = $request->ongkir;
$diskon           = $request->diskon;
$dibayar_sekarang = $request->total_bayar;
$uang_muka        = $uang_muka_dipakai ?? 0;
$sisa_hutang      = $kurang_bayar;

// Kode akun dari mapping JurnalHelper
$kode_akun_persediaan = JurnalHelper::getKodeAkun('persediaan_bahan');
$kode_akun_ongkir     = JurnalHelper::getKodeAkun('ongkos_kirim');
$kode_akun_diskon     = JurnalHelper::getKodeAkun('diskon_pembelian');
$kode_akun_kas        = JurnalHelper::getKodeAkun('kas_bank');
$kode_akun_uangmuka   = JurnalHelper::getKodeAkun('uang_muka');
$kode_akun_hutang     = JurnalHelper::getKodeAkun('utang_usaha');

// Untuk setiap detail:
DB::table('t_jurnal_detail')->insert([
    'no_jurnal_detail' => JurnalHelper::generateNoJurnalDetail($no_jurnal_baru),
    'no_jurnal'        => $no_jurnal_baru,
    'kode_akun'        => $kode_akun_persediaan,
    'debit'            => $nilai_persediaan,
    'kredit'           => 0,
]);

// 2. Ongkos Kirim (Debit)
if ($ongkir > 0) {
    DB::table('t_jurnal_detail')->insert([
        'no_jurnal_detail' => JurnalHelper::generateNoJurnalDetail($no_jurnal_baru),
        'no_jurnal'        => $no_jurnal_baru,
        'kode_akun'        => $kode_akun_ongkir,
        'debit'            => $ongkir,
        'kredit'           => 0,
    ]);
}

// 3. Diskon Pembelian (Kredit)
if ($diskon > 0) {
    DB::table('t_jurnal_detail')->insert([
        'no_jurnal_detail' => JurnalHelper::generateNoJurnalDetail($no_jurnal_baru),
        'no_jurnal'        => $no_jurnal_baru,
        'kode_akun'        => $kode_akun_diskon,
        'debit'            => 0,
        'kredit'           => $diskon,
    ]);
}

// 4. Kas/Bank (Kredit)
if ($dibayar_sekarang > 0) {
    DB::table('t_jurnal_detail')->insert([
        'no_jurnal_detail' => JurnalHelper::generateNoJurnalDetail($no_jurnal_baru),
        'no_jurnal'        => $no_jurnal_baru,
        'kode_akun'        => $kode_akun_kas,
        'debit'            => 0,
        'kredit'           => $dibayar_sekarang,
    ]);
}

// 5. Uang Muka (Kredit)
if ($uang_muka > 0) {
    DB::table('t_jurnal_detail')->insert([
        'no_jurnal_detail' => JurnalHelper::generateNoJurnalDetail($no_jurnal_baru),
        'no_jurnal'        => $no_jurnal_baru,
        'kode_akun'        => $kode_akun_uangmuka,
        'debit'            => 0,
        'kredit'           => $uang_muka,
    ]);
}

// 6. Hutang Usaha (Kredit)
if ($sisa_hutang > 0) {
    DB::table('t_jurnal_detail')->insert([
        'no_jurnal_detail' => JurnalHelper::generateNoJurnalDetail($no_jurnal_baru),
        'no_jurnal'        => $no_jurnal_baru,
        'kode_akun'        => $kode_akun_hutang,
        'debit'            => 0,
        'kredit'           => $sisa_hutang,
    ]);
}

        return redirect()->route('pembelian.index')->with('success', 'Data pembelian berhasil diupdate.');
    }

    public function getDetailPembelian($no_pembelian)
    {
        // Ambil no_terima_bahan dari pembelian
        $no_terima_bahan = \DB::table('t_pembelian')
            ->where('no_pembelian', $no_pembelian)
            ->value('no_terima_bahan');

        // Ambil detail bahan dari t_terimab_detail
        $details = [];
        if ($no_terima_bahan) {
            $details = \DB::table('t_terimab_detail')
                ->join('t_bahan', 't_terimab_detail.kode_bahan', '=', 't_bahan.kode_bahan')
                ->where('t_terimab_detail.no_terima_bahan', $no_terima_bahan)
                ->select(
                    't_terimab_detail.kode_bahan',
                    't_bahan.nama_bahan',
                    't_bahan.satuan',
                    't_terimab_detail.bahan_masuk as jumlah',
                    't_terimab_detail.harga_beli as harga'
                )
                ->get();
        }

        return response()->json(['details' => $details]);
    }

    public function laporanPdf(Request $request)
    {
        $tanggal_mulai = $request->tanggal_mulai ?? now()->startOfMonth()->format('Y-m-d');
        $tanggal_selesai = $request->tanggal_selesai ?? now()->endOfMonth()->format('Y-m-d');

        $query = \DB::table('t_pembelian')
            ->join('t_supplier', 't_pembelian.kode_supplier', '=', 't_supplier.kode_supplier')
            ->select(
                't_pembelian.*',
                't_supplier.nama_supplier'
            )
            ->whereBetween('t_pembelian.tanggal_pembelian', [$tanggal_mulai, $tanggal_selesai]);

        if ($request->jenis_pembelian) {
            $query->where('jenis_pembelian', $request->jenis_pembelian);
        }

        $pembelian = $query->get();

        $periode = [
            'mulai' => $tanggal_mulai,
            'selesai' => $tanggal_selesai,
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pembelian.laporan', compact('pembelian', 'periode'));
        return $pdf->stream('laporan_pembelian.pdf');
    }
    private function getBahanStokMinimal()
{
    return DB::table('t_bahan')
        ->leftJoin('t_kartupersbahan', 't_bahan.kode_bahan', '=', 't_kartupersbahan.kode_bahan')
        ->select(
            't_bahan.kode_bahan',
            't_bahan.nama_bahan',
            't_bahan.satuan',
            't_bahan.stokmin',
            DB::raw('COALESCE(SUM(t_kartupersbahan.masuk),0) - COALESCE(SUM(t_kartupersbahan.keluar),0) as stok')
        )
        ->groupBy('t_bahan.kode_bahan', 't_bahan.nama_bahan', 't_bahan.satuan', 't_bahan.stokmin')
        ->havingRaw('stok < t_bahan.stokmin')
        ->get();
}
}
