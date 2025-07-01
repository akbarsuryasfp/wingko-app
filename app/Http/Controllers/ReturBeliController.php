<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\JurnalHelper;

class ReturBeliController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
public function index(Request $request)
{
    $returList = DB::table('t_returbeli')
        ->join('t_supplier', 't_returbeli.kode_supplier', '=', 't_supplier.kode_supplier')
        ->select(
            't_returbeli.no_retur_beli',
            't_returbeli.tanggal_retur_beli',
            't_returbeli.no_pembelian',
            't_supplier.nama_supplier'
        )
        ->orderByDesc('t_returbeli.no_retur_beli');

    // Filter berdasarkan tanggal jika ada
    $tanggal_mulai = $request->tanggal_mulai ?? now()->startOfMonth()->format('Y-m-d');
$tanggal_selesai = $request->tanggal_selesai ?? now()->endOfMonth()->format('Y-m-d');

$returList->whereBetween('t_returbeli.tanggal_retur_beli', [$tanggal_mulai, $tanggal_selesai]);

    $returList = $returList->get();

    // Ambil detail bahan untuk setiap retur
    foreach ($returList as $retur) {
        $details = DB::table('t_returb_detail')
            ->join('t_bahan', 't_returb_detail.kode_bahan', '=', 't_bahan.kode_bahan')
            ->leftJoin('t_terimab_detail', function($join) use ($retur) {
                $join->on('t_returb_detail.kode_bahan', '=', 't_terimab_detail.kode_bahan')
                     ->where('t_terimab_detail.no_terima_bahan', '=', function($query) use ($retur) {
                         $query->select('no_terima_bahan')
                               ->from('t_pembelian')
                               ->where('no_pembelian', $retur->no_pembelian)
                               ->limit(1);
                     });
            })
            ->where('t_returb_detail.no_retur_beli', $retur->no_retur_beli)
            ->select(
                't_returb_detail.*',
                't_bahan.nama_bahan',
                't_terimab_detail.bahan_masuk'
            )
            ->get();
        $retur->details = $details;
    }

    return view('returbeli.index', compact('returList'));
}

    public function create()
    {
        // Generate kode retur otomatis
        $last = DB::table('t_returbeli')->orderByDesc('no_retur_beli')->value('no_retur_beli');
        if ($last) {
            $num = (int)substr($last, 4) + 1;
            $kode_retur = 'RTRB' . str_pad($num, 6, '0', STR_PAD_LEFT);
        } else {
            $kode_retur = 'RTRB000001';
        }

        // Ambil daftar pembelian beserta tanggal dan supplier
        $pembelian = DB::table('t_pembelian')
            ->join('t_supplier', 't_pembelian.kode_supplier', '=', 't_supplier.kode_supplier')
            ->select('t_pembelian.no_pembelian', 't_pembelian.tanggal_pembelian', 't_supplier.nama_supplier')
            ->get();

        return view('returbeli.create', compact('kode_retur', 'pembelian'));
    }

    public function store(Request $request)
    {
        // Ambil kode supplier dari pembelian
        $kode_supplier = DB::table('t_pembelian')
            ->where('no_pembelian', $request->kode_pembelian)
            ->value('kode_supplier');

        // Hitung total retur dari input (atau hitung manual jika perlu)
        $total_retur = 0;
        for ($i = 0; $i < count($request->kode_bahan); $i++) {
            $total_retur += ($request->harga_beli[$i] ?? 0) * ($request->jumlah_retur[$i] ?? 0);
        }

        // Simpan header retur
        $no_retur_beli = $request->kode_retur;
        DB::table('t_returbeli')->insert([
            'no_retur_beli'      => $no_retur_beli,
            'no_pembelian'       => $request->kode_pembelian,
            'tanggal_retur_beli' => $request->tanggal_retur_beli,
            'kode_supplier'      => $kode_supplier,
            'total_retur'        => $total_retur,
            'keterangan'         => $request->keterangan,
        ]);

        // Simpan detail retur & kartu stok
        $kode_bahan   = $request->kode_bahan;
        $jumlah_retur = $request->jumlah_retur;
        $harga_beli   = $request->harga_beli;
        $alasan       = $request->alasan;

        $lastDetail = DB::table('t_returb_detail')->max('no_returb_detail');
        $nextDetail = $lastDetail ? $lastDetail + 1 : 1;
        $lastId = DB::table('t_kartupersbahan')->max('id');
        $nextId = $lastId ? $lastId + 1 : 1;

        for ($i = 0; $i < count($kode_bahan); $i++) {
            // Insert ke t_returb_detail
            DB::table('t_returb_detail')->insert([
                'no_returb_detail' => $nextDetail++,
                'no_retur_beli'    => $no_retur_beli,
                'kode_bahan'       => $kode_bahan[$i],
                'harga_beli'       => $harga_beli[$i],
                'jumlah_retur'     => $jumlah_retur[$i],
                'subtotal'         => $harga_beli[$i] * $jumlah_retur[$i],
                'alasan'           => $alasan[$i] ?? null,
            ]);

            // Insert ke t_kartupersbahan (keluar)
            if ($jumlah_retur[$i] > 0) {
                $satuan = DB::table('t_bahan')->where('kode_bahan', $kode_bahan[$i])->value('satuan') ?? '';
                
// Ambil no_terima_bahan dari pembelian
$no_terima_bahan = DB::table('t_pembelian')
    ->where('no_pembelian', $request->kode_pembelian)
    ->value('no_terima_bahan');

// Ambil tanggal_exp dari t_terimab_detail
$tanggal_exp = DB::table('t_terimab_detail')
    ->where('no_terima_bahan', $no_terima_bahan)
    ->where('kode_bahan', $kode_bahan[$i])
    ->value('tanggal_exp');

    $batch = DB::table('t_kartupersbahan')
    ->where('kode_bahan', $kode_bahan[$i])
    ->where('harga', $harga_beli[$i]) // pastikan harga sama dengan yang diretur
    ->whereRaw('(masuk - keluar) > 0')
    ->orderBy('tanggal')
    ->first();

                DB::table('t_kartupersbahan')->insert([
                    'id'           => $nextId++,
                    'no_transaksi' => $no_retur_beli,
                    'tanggal'      => $request->tanggal_retur_beli,
                    'tanggal_exp'  => $tanggal_exp,
                    'kode_bahan'   => $kode_bahan[$i],
                    'masuk'        => 0,
                    'keluar'       => $jumlah_retur[$i],
                    'harga'        => $harga_beli[$i],
                    'satuan'       => $satuan,
                    'keterangan'   => 'Retur Pembelian',
                ]);
                app('App\Http\Controllers\BahanController')->updateStokBahan($kode_bahan[$i]);
            }
        }

        // --- JURNAL UMUM & DETAIL ---

        if ($total_retur > 0) {
            $no_jurnal = JurnalHelper::generateNoJurnal();
            DB::table('t_jurnal_umum')->insert([
                'no_jurnal'   => $no_jurnal,
                'tanggal'     => $request->tanggal_retur_beli,
                'keterangan'  => 'Retur Pembelian ' . $no_retur_beli,
                'nomor_bukti' => $no_retur_beli,
            ]);

            $sisa_utang = DB::table('t_utang')->where('no_pembelian', $request->kode_pembelian)->value('sisa_utang');
            $status = ($sisa_utang > 0) ? 'Hutang' : 'Lunas';
            $akun_debit = ($status === 'Hutang') ? '201' : '101';

            DB::table('t_jurnal_detail')->insert([
                [
                    'no_jurnal_detail' => JurnalHelper::generateNoJurnalDetail(),
                    'no_jurnal'        => $no_jurnal,
                    'kode_akun'        => $akun_debit,
                    'debit'            => $total_retur,
                    'kredit'           => 0,
                ],
                [
                    'no_jurnal_detail' => JurnalHelper::generateNoJurnalDetail(),
                    'no_jurnal'        => $no_jurnal,
                    'kode_akun'        => '103',
                    'debit'            => 0,
                    'kredit'           => $total_retur,
                ],
            ]);
        }

        return redirect()->route('returbeli.index')->with('success', 'Data retur pembelian berhasil disimpan.');
    }

public function show($no_retur_beli)
{
    $retur = DB::table('t_returbeli')
        ->join('t_supplier', 't_returbeli.kode_supplier', '=', 't_supplier.kode_supplier')
        ->where('t_returbeli.no_retur_beli', $no_retur_beli)
        ->select('t_returbeli.*', 't_supplier.nama_supplier')
        ->first();

    if (!$retur) {
        abort(404, 'Data retur tidak ditemukan.');
    }

    $details = DB::table('t_returb_detail')
        ->join('t_bahan', 't_returb_detail.kode_bahan', '=', 't_bahan.kode_bahan')
        ->where('t_returb_detail.no_retur_beli', $no_retur_beli)
        ->select(
            't_bahan.nama_bahan',
            't_returb_detail.harga_beli',
            't_returb_detail.jumlah_retur',
            DB::raw('t_returb_detail.harga_beli * t_returb_detail.jumlah_retur as subtotal'),
            't_returb_detail.alasan'
        )
        ->get();

    return view('returbeli.detail', compact('retur', 'details'));
}

public function edit($no_retur_beli)
{
    $retur = DB::table('t_returbeli')->where('no_retur_beli', $no_retur_beli)->first();
$details = DB::table('t_returb_detail')
    ->join('t_bahan', 't_returb_detail.kode_bahan', '=', 't_bahan.kode_bahan')
    ->join('t_returbeli', 't_returb_detail.no_retur_beli', '=', 't_returbeli.no_retur_beli')
    ->join('t_pembelian', 't_returbeli.no_pembelian', '=', 't_pembelian.no_pembelian')
    ->join('t_terimabahan', 't_pembelian.no_terima_bahan', '=', 't_terimabahan.no_terima_bahan')
    ->leftJoin('t_terimab_detail', function($join) {
        $join->on('t_returb_detail.kode_bahan', '=', 't_terimab_detail.kode_bahan')
             ->on('t_terimab_detail.no_terima_bahan', '=', 't_terimabahan.no_terima_bahan');
    })
    ->where('t_returb_detail.no_retur_beli', $no_retur_beli)
    ->select(
        't_returb_detail.*',
        't_bahan.nama_bahan',
        't_terimab_detail.bahan_masuk as jumlah_terima'
    )
    ->get();

    // Data untuk dropdown pembelian dan supplier
    $pembelian = DB::table('t_pembelian')
        ->join('t_supplier', 't_pembelian.kode_supplier', '=', 't_supplier.kode_supplier')
        ->select('t_pembelian.no_pembelian', 't_pembelian.tanggal_pembelian', 't_supplier.nama_supplier')
        ->get();

    return view('returbeli.edit', compact('retur', 'details', 'pembelian'));
}

public function update(Request $request, $no_retur_beli)
{
    // Update header
    $kode_supplier = DB::table('t_pembelian')
        ->where('no_pembelian', $request->kode_pembelian)
        ->value('kode_supplier');

    $subtotal = $request->subtotal;
    $total_retur = is_array($subtotal) ? array_sum($subtotal) : 0;

    DB::table('t_returbeli')->where('no_retur_beli', $no_retur_beli)->update([
        'no_pembelian'       => $request->kode_pembelian,
        'tanggal_retur_beli' => $request->tanggal_retur_beli,
        'kode_supplier'      => $kode_supplier,
        'total_retur'        => $total_retur,
        'keterangan'         => $request->keterangan,
    ]);

    // Hapus detail lama
    DB::table('t_returb_detail')->where('no_retur_beli', $no_retur_beli)->delete();

    // Hapus kartu stok lama
    DB::table('t_kartupersbahan')
        ->where('no_transaksi', $no_retur_beli)
        ->where('keterangan', 'Retur Pembelian')
        ->delete();

    // Simpan detail baru & kartu stok baru
    $kode_bahan   = $request->kode_bahan;
    $jumlah_retur = $request->jumlah_retur;
    $harga_beli   = $request->harga_beli;
    $alasan       = $request->alasan;

    $lastDetail = DB::table('t_returb_detail')->max('no_returb_detail');
    $nextDetail = $lastDetail ? $lastDetail + 1 : 1;
    $lastId = DB::table('t_kartupersbahan')->max('id');
    $nextId = $lastId ? $lastId + 1 : 1;

    for ($i = 0; $i < count($kode_bahan); $i++) {
        DB::table('t_returb_detail')->insert([
            'no_returb_detail' => $nextDetail++,
            'no_retur_beli'    => $no_retur_beli,
            'kode_bahan'       => $kode_bahan[$i],
            'harga_beli'       => $harga_beli[$i],
            'jumlah_retur'     => $jumlah_retur[$i],
            'subtotal'         => $harga_beli[$i] * $jumlah_retur[$i],
            'alasan'           => $alasan[$i] ?? null,
        ]);

        // Insert ulang ke t_kartupersbahan
        if ($jumlah_retur[$i] > 0) {
            $satuan = DB::table('t_bahan')->where('kode_bahan', $kode_bahan[$i])->value('satuan') ?? '';
            
// Ambil no_terima_bahan dari pembelian
$no_terima_bahan = DB::table('t_pembelian')
    ->where('no_pembelian', $request->kode_pembelian)
    ->value('no_terima_bahan');

// Ambil tanggal_exp dari t_terimab_detail
$tanggal_exp = DB::table('t_terimab_detail')
    ->where('no_terima_bahan', $no_terima_bahan)
    ->where('kode_bahan', $kode_bahan[$i])
    ->value('tanggal_exp');

            DB::table('t_kartupersbahan')->insert([
                'id'           => $nextId++,
                'no_transaksi' => $no_retur_beli,
                'tanggal'      => $request->tanggal_retur_beli,
                'tanggal_exp'  => $tanggal_exp,
                'kode_bahan'   => $kode_bahan[$i],
                'masuk'        => 0,
                'keluar'       => $jumlah_retur[$i],
                'harga'        => $harga_beli[$i],
                'satuan'       => $satuan,
                'keterangan'   => 'Retur Pembelian',
            ]);
        }
    }

    // Hitung total_retur dari detail yang baru saja di-insert
    $total_retur = DB::table('t_returb_detail')
        ->where('no_retur_beli', $no_retur_beli)
        ->sum('subtotal');

    // Simpan ke t_returbeli
    DB::table('t_returbeli')
        ->where('no_retur_beli', $no_retur_beli)
        ->update(['total_retur' => $total_retur]);

    app('App\Http\Controllers\BahanController')->updateStokBahan($kode_bahan);

    // Hapus jurnal lama
    $no_jurnal = DB::table('t_jurnal_umum')->where('nomor_bukti', $no_retur_beli)->value('no_jurnal');
    if ($no_jurnal) {
        DB::table('t_jurnal_detail')->where('no_jurnal', $no_jurnal)->delete();
        DB::table('t_jurnal_umum')->where('no_jurnal', $no_jurnal)->delete();
    }

    // Insert jurnal baru (sama seperti di store)
    if ($total_retur > 0) {
        $no_jurnal = JurnalHelper::generateNoJurnal();
        DB::table('t_jurnal_umum')->insert([
            'no_jurnal'   => $no_jurnal,
            'tanggal'     => $request->tanggal_retur_beli,
            'keterangan'  => 'Retur Pembelian ' . $no_retur_beli,
            'nomor_bukti' => $no_retur_beli,
        ]);

        $sisa_utang = DB::table('t_utang')->where('no_pembelian', $request->kode_pembelian)->value('sisa_utang');
        $status = ($sisa_utang > 0) ? 'Hutang' : 'Lunas';
        $akun_debit = ($status === 'Hutang') ? '201' : '101';

        DB::table('t_jurnal_detail')->insert([
            [
                'no_jurnal_detail' => JurnalHelper::generateNoJurnalDetail(),
                'no_jurnal'        => $no_jurnal,
                'kode_akun'        => $akun_debit,
                'debit'            => $total_retur,
                'kredit'          => 0,
        ],
            [
                'no_jurnal_detail' => JurnalHelper::generateNoJurnalDetail(),
                'no_jurnal'        => $no_jurnal,
                'kode_akun'        => '103', // Persediaan Bahan (pastikan '103' ada di t_akun)
                'debit'            => 0,
                'kredit'           => $total_retur,
            ], 
        ]);
    }

    return redirect()->route('returbeli.index')->with('success', 'Data retur berhasil diupdate.');
}

public function destroy($no_retur_beli)
{
    // Ambil semua kode_bahan yang akan dihapus
    $kode_bahan_list = DB::table('t_returb_detail')
        ->where('no_retur_beli', $no_retur_beli)
        ->pluck('kode_bahan')
        ->toArray();

    // Hapus kartu stok terkait retur ini
    DB::table('t_kartupersbahan')
        ->where('no_transaksi', $no_retur_beli)
        ->where('keterangan', 'Retur Pembelian')
        ->delete();

    // Hapus detail dan header retur
    DB::table('t_returb_detail')->where('no_retur_beli', $no_retur_beli)->delete();
    DB::table('t_returbeli')->where('no_retur_beli', $no_retur_beli)->delete();

    // Hapus jurnal umum & detail untuk retur ini
    $no_jurnal = DB::table('t_jurnal_umum')->where('nomor_bukti', $no_retur_beli)->value('no_jurnal');
    if ($no_jurnal) {
        DB::table('t_jurnal_detail')->where('no_jurnal', $no_jurnal)->delete();
        DB::table('t_jurnal_umum')->where('no_jurnal', $no_jurnal)->delete();
    }

    // Update stok untuk semua bahan yang terlibat
    foreach ($kode_bahan_list as $kode_bahan) {
        app('App\Http\Controllers\BahanController')->updateStokBahan($kode_bahan);
    }

    return redirect()->route('returbeli.index')->with('success', 'Data retur berhasil dihapus.');
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
                't_terimab_detail.harga_beli',
                't_terimab_detail.tanggal_exp'
            )
            ->get();
    }

    return response()->json(['details' => $details]);
}
public function cetak($no_retur_beli)
{
    $retur = DB::table('t_returbeli')
        ->join('t_supplier', 't_returbeli.kode_supplier', '=', 't_supplier.kode_supplier')
        ->join('t_pembelian', 't_returbeli.no_pembelian', '=', 't_pembelian.no_pembelian')
        ->leftJoin('t_terimabahan', 't_pembelian.no_terima_bahan', '=', 't_terimabahan.no_terima_bahan')
        ->leftJoin('t_order_beli', 't_terimabahan.no_order_beli', '=', 't_order_beli.no_order_beli')
        ->where('t_returbeli.no_retur_beli', $no_retur_beli)
        ->select(
            't_returbeli.*',
            't_supplier.nama_supplier',
            't_pembelian.no_terima_bahan',
            't_terimabahan.tanggal_terima',
            't_order_beli.no_order_beli'
        )
        ->first();

    $details = DB::table('t_returb_detail')
        ->join('t_bahan', 't_returb_detail.kode_bahan', '=', 't_bahan.kode_bahan')
        ->where('t_returb_detail.no_retur_beli', $no_retur_beli)
        ->select('t_returb_detail.*', 't_bahan.nama_bahan')
        ->get();

    return view('returbeli.cetak', compact('retur', 'details'));
}
public function laporan(Request $request)
{
    $tanggal_mulai = $request->tanggal_mulai ?? now()->startOfMonth()->format('Y-m-d');
    $tanggal_selesai = $request->tanggal_selesai ?? now()->endOfMonth()->format('Y-m-d');

    $returList = \DB::table('t_returbeli')
        ->join('t_supplier', 't_returbeli.kode_supplier', '=', 't_supplier.kode_supplier')
        ->whereBetween('t_returbeli.tanggal_retur_beli', [$tanggal_mulai, $tanggal_selesai])
        ->select('t_returbeli.*', 't_supplier.nama_supplier')
        ->orderBy('t_returbeli.tanggal_retur_beli', 'desc')
        ->get();

    foreach ($returList as $retur) {
        $retur->details = \DB::table('t_returb_detail')
            ->join('t_bahan', 't_returb_detail.kode_bahan', '=', 't_bahan.kode_bahan')
            ->where('t_returb_detail.no_retur_beli', $retur->no_retur_beli)
            ->select('t_bahan.nama_bahan', 't_returb_detail.jumlah_retur', 't_returb_detail.alasan')
            ->get();
    }

    return view('returbeli.laporan', compact('returList', 'tanggal_mulai', 'tanggal_selesai'));
}
public function laporanPdf(Request $request)
{
    $tanggal_mulai = $request->tanggal_mulai ?? now()->startOfMonth()->format('Y-m-d');
    $tanggal_selesai = $request->tanggal_selesai ?? now()->endOfMonth()->format('Y-m-d');

    $returList = \DB::table('t_returbeli')
        ->join('t_supplier', 't_returbeli.kode_supplier', '=', 't_supplier.kode_supplier')
        ->whereBetween('t_returbeli.tanggal_retur_beli', [$tanggal_mulai, $tanggal_selesai])
        ->select('t_returbeli.*', 't_supplier.nama_supplier')
        ->orderBy('t_returbeli.tanggal_retur_beli', 'desc')
        ->get();

    // Tambahkan details pada setiap retur
    foreach ($returList as $retur) {
        $retur->details = \DB::table('t_returb_detail')
            ->join('t_bahan', 't_returb_detail.kode_bahan', '=', 't_bahan.kode_bahan')
            ->where('t_returb_detail.no_retur_beli', $retur->no_retur_beli)
            ->select('t_bahan.nama_bahan', 't_returb_detail.jumlah_retur', 't_returb_detail.alasan')
            ->get();
    }

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('returbeli.laporan', compact('returList', 'tanggal_mulai', 'tanggal_selesai'));
    return $pdf->stream('laporan_retur_pembelian.pdf');
}
}

