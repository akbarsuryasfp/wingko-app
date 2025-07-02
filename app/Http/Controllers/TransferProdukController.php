<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransferProdukController extends Controller
{
    public function create()
    {
        // Subquery stok produk di Gudang
        $produkGudang = DB::table('t_kartupersproduk')
            ->select('kode_produk', DB::raw('SUM(masuk) - SUM(keluar) as stok'))
            ->where('lokasi', 'Gudang')
            ->groupBy('kode_produk')
            ->havingRaw('stok > 0');

        // Subquery HPP terakhir per produk di Gudang
        $hppTerakhir = DB::table('t_kartupersproduk as hpp')
            ->select('hpp.kode_produk', 'hpp.hpp')
            ->where('hpp.lokasi', 'Gudang')
            ->whereRaw('hpp.id = (SELECT MAX(id) FROM t_kartupersproduk WHERE kode_produk = hpp.kode_produk AND lokasi = "Gudang")');

        // Subquery tanggal_exp terakhir per produk di Gudang
        $tglExpTerakhir = DB::table('t_kartupersproduk as exp')
            ->select('exp.kode_produk', 'exp.tanggal_exp')
            ->where('exp.lokasi', 'Gudang')
            ->whereRaw('exp.id = (SELECT MAX(id) FROM t_kartupersproduk WHERE kode_produk = exp.kode_produk AND lokasi = "Gudang")');

        // Join ke t_produk untuk ambil nama_produk, satuan, stok, hpp, dan tanggal_exp terakhir
        $produkList = DB::table('t_produk')
            ->joinSub($produkGudang, 'stok_gudang', function ($join) {
                $join->on('t_produk.kode_produk', '=', 'stok_gudang.kode_produk');
            })
            ->leftJoinSub($hppTerakhir, 'hpp_gudang', function ($join) {
                $join->on('t_produk.kode_produk', '=', 'hpp_gudang.kode_produk');
            })
            ->leftJoinSub($tglExpTerakhir, 'exp_gudang', function ($join) {
                $join->on('t_produk.kode_produk', '=', 'exp_gudang.kode_produk');
            })
            ->select(
                't_produk.kode_produk',
                't_produk.nama_produk',
                't_produk.satuan',
                'stok_gudang.stok',
                'hpp_gudang.hpp',
                'exp_gudang.tanggal_exp'
            )
            ->get();

        // Buat kode otomatis sederhana
        $last = DB::table('t_kartupersproduk')->orderByDesc('id')->first();
        $next = $last ? $last->id + 1 : 1;
        $kode_otomatis = 'TRF-' . date('Ymd') . '-' . str_pad($next, 3, '0', STR_PAD_LEFT);

        return view('transferproduk.create', [
            'produk' => $produkList,
            'kode_otomatis' => $kode_otomatis
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'no_transaksi'   => 'required',
            'tanggal'        => 'required|date',
            'lokasi_asal'    => 'required',
            'lokasi_tujuan'  => 'required',
            'produk_id'      => 'required|array',
            'jumlah'         => 'required|array',
            'satuan'         => 'required|array',
            'harga'          => 'required|array',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->produk_id as $i => $produk_id) {
                // Keluarkan stok dari lokasi asal (keluar)
                DB::table('t_kartupersproduk')->insert([
                    'no_transaksi' => $request->no_transaksi,
                    'tanggal'      => $request->tanggal,
                    'tanggal_exp'  => $request->tanggal_exp[$i] ?? null,
                    'kode_produk'  => $produk_id,
                    'masuk'        => 0,
                    'keluar'       => $request->jumlah[$i],
                    'hpp'          => $request->harga[$i], // GANTI 'harga' MENJADI 'hpp'
                    'satuan'       => $request->satuan[$i],
                    'keterangan'   => 'Transfer ke ' . $request->lokasi_tujuan,
                    'lokasi'       => $request->lokasi_asal,
                ]);

                // Masukkan stok ke lokasi tujuan (masuk)
                DB::table('t_kartupersproduk')->insert([
                    'no_transaksi' => $request->no_transaksi,
                    'tanggal'      => $request->tanggal,
                    'tanggal_exp'  => $request->tanggal_exp[$i] ?? null,
                    'kode_produk'  => $produk_id,
                    'masuk'        => $request->jumlah[$i],
                    'keluar'       => 0,
                    'hpp'          => $request->harga[$i], // GANTI 'harga' MENJADI 'hpp'
                    'satuan'       => $request->satuan[$i],
                    'keterangan'   => 'Transfer dari ' . $request->lokasi_asal,
                    'lokasi'       => $request->lokasi_tujuan,
                ]);
            }

            DB::commit();
            return redirect()->route('transferproduk.create')->with('success', 'Transfer produk berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menyimpan: ' . $e->getMessage()]);
        }
    }
}