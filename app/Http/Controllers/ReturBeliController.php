<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReturBeliController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
public function index()
{
    $returList = DB::table('t_returbeli')
        ->join('t_supplier', 't_returbeli.kode_supplier', '=', 't_supplier.kode_supplier')
        ->select(
            't_returbeli.no_retur_beli',
            't_returbeli.tanggal_retur_beli',
            't_returbeli.no_pembelian',
            't_supplier.nama_supplier'
        )
        ->orderByDesc('t_returbeli.no_retur_beli')
        ->get();

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
                DB::table('t_kartupersbahan')->insert([
                    'id'           => $nextId++,
                    'no_transaksi' => $no_retur_beli,
                    'tanggal'      => $request->tanggal_retur_beli,
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

        return redirect()->route('returbeli.index')->with('success', 'Data retur pembelian berhasil disimpan.');
    }

public function show($no_retur_beli)
{
    $retur = DB::table('t_returbeli')
        ->join('t_supplier', 't_returbeli.kode_supplier', '=', 't_supplier.kode_supplier')
        ->where('t_returbeli.no_retur_beli', $no_retur_beli)
        ->select('t_returbeli.*', 't_supplier.nama_supplier')
        ->first();

    $details = DB::table('t_returb_detail')
        ->join('t_bahan', 't_returb_detail.kode_bahan', '=', 't_bahan.kode_bahan')
        ->where('t_returb_detail.no_retur_beli', $no_retur_beli)
        ->select('t_returb_detail.*', 't_bahan.nama_bahan')
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
            DB::table('t_kartupersbahan')->insert([
                'id'           => $nextId++,
                'no_transaksi' => $no_retur_beli,
                'tanggal'      => $request->tanggal_retur_beli,
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
                't_terimab_detail.harga_beli'
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
}

