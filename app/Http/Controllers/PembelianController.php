<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PembelianController extends Controller
{
    // Untuk pembelian berdasarkan order (tetap menggunakan create.blade.php)
    public function create(Request $request)
    {
        // Generate kode pembelian otomatis
        $last = DB::table('t_pembelian')->orderBy('no_pembelian', 'desc')->first();
        if ($last && preg_match('/P(\d+)/', $last->no_pembelian, $match)) {
            $nextNumber = (int)$match[1] + 1;
        } else {
            $nextNumber = 1;
        }
        $kode_pembelian = 'P' . str_pad($nextNumber, 7, '0', STR_PAD_LEFT);

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

    // Untuk pembelian langsung (tanpa order), gunakan view langsung.blade.php
    public function createLangsung()
    {
        // Ambil kode pembelian terakhir
        $last = DB::table('t_pembelian')->orderBy('no_pembelian', 'desc')->first();
        if ($last && preg_match('/P(\d+)/', $last->no_pembelian, $match)) {
            $nextNumber = (int)$match[1] + 1;
        } else {
            $nextNumber = 1;
        }
        $kode_pembelian = 'P' . str_pad($nextNumber, 7, '0', STR_PAD_LEFT);

        $supplier = DB::table('t_supplier')->get();
        $bahan = DB::table('t_bahan')->get();

        return view('pembelian.langsung', compact('kode_pembelian', 'supplier', 'bahan'));
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

        return redirect()->route('pembelian.index')->with('success', 'Data pembelian & penerimaan berhasil disimpan.');
    }

    // Metode index() dan show() tetap seperti sebelumnya...
    public function index(Request $request)
{
    $query = DB::table('t_pembelian')
        ->leftJoin('t_terimabahan', 't_pembelian.no_terima_bahan', '=', 't_terimabahan.no_terima_bahan')
        ->leftJoin('t_order_beli', 't_terimabahan.no_order_beli', '=', 't_order_beli.no_order_beli')
        ->leftJoin('t_supplier', 't_pembelian.kode_supplier', '=', 't_supplier.kode_supplier')
        ->select(
            't_pembelian.no_pembelian',
            't_pembelian.tanggal_pembelian',
            't_pembelian.no_terima_bahan',
            't_supplier.nama_supplier',
            't_pembelian.total_pembelian',
            't_pembelian.uang_muka',
            't_pembelian.total_bayar',
            't_pembelian.hutang',
            't_pembelian.status',
            't_order_beli.uang_muka as uang_muka_order'
        );


        if ($request->jenis_pembelian) {
            $query->where('t_pembelian.jenis_pembelian', $request->jenis_pembelian);
        }

        $pembelian = $query->orderBy('t_pembelian.no_pembelian', 'asc')->get();

        return view('pembelian.index', compact('pembelian'));
    }

    public function show($no_pembelian)
    {
        $pembelian = DB::table('t_pembelian')
            ->leftJoin('t_supplier', 't_pembelian.kode_supplier', '=', 't_supplier.kode_supplier')
            ->where('t_pembelian.no_pembelian', $no_pembelian)
            ->select('t_pembelian.*', 't_supplier.nama_supplier', 't_pembelian.no_nota') // pastikan ada no_nota
            ->first();

        if (!$pembelian) {
            abort(404, 'Data pembelian tidak ditemukan.');
        }

        if ($pembelian->no_terima_bahan && $pembelian->no_terima_bahan !== '-') {
            $details = DB::table('t_terimab_detail')
                ->join('t_bahan', 't_terimab_detail.kode_bahan', '=', 't_bahan.kode_bahan')
                ->where('t_terimab_detail.no_terima_bahan', $pembelian->no_terima_bahan)
                ->select(
                    't_bahan.nama_bahan',
                    't_bahan.satuan',
                    't_terimab_detail.bahan_masuk',
                    't_terimab_detail.harga_beli',
                    DB::raw('t_terimab_detail.bahan_masuk * t_terimab_detail.harga_beli as subtotal')
                )
                ->get();
        } else {
            $details = collect([]);
        }

        return view('pembelian.detail', compact('pembelian', 'details'));
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
        // Ambil no_terima_bahan terkait
        $no_terima_bahan = DB::table('t_pembelian')
            ->where('no_pembelian', $no_pembelian)
            ->value('no_terima_bahan');

        // Hapus data pembelian dan utang
        DB::table('t_pembelian')->where('no_pembelian', $no_pembelian)->delete();
        DB::table('t_utang')->where('no_pembelian', $no_pembelian)->delete();

        // Ambil data pembelian
        $pembelian = DB::table('t_pembelian')->where('no_pembelian', $no_pembelian)->first();

        if ($pembelian && $pembelian->jenis_pembelian === 'pembelian langsung') {
            $no_terima_bahan = $pembelian->no_terima_bahan;

            // Ambil semua kode_bahan lama
            $kode_bahan_lama = DB::table('t_terimab_detail')->where('no_terima_bahan', $no_terima_bahan)->pluck('kode_bahan');

            // Hapus detail & kartu stok lama
            DB::table('t_terimab_detail')->where('no_terima_bahan', $no_terima_bahan)->delete();
            DB::table('t_kartupersbahan')->where('no_transaksi', $no_terima_bahan)->where('keterangan', 'Pembelian Langsung')->delete();

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

        return view('pembelian.edit', compact('pembelian', 'suppliers', 'bahan', 'details', 'nama_supplier'));
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
            // tambahkan validasi lain sesuai kebutuhan
        ]);

        // Ambil data lama
        $pembelian = DB::table('t_pembelian')->where('no_pembelian', $no_pembelian)->first();

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
}