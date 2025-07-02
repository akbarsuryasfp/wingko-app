<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PesananPenjualanController extends Controller
{
    public function index(Request $request)
    {
        $sort = $request->get('sort', 'asc');
        $query = DB::table('t_pesanan')
            ->leftJoin('t_pelanggan', 't_pesanan.kode_pelanggan', '=', 't_pelanggan.kode_pelanggan')
            ->select('t_pesanan.*', 't_pelanggan.nama_pelanggan');

        // Tambahkan filter periode tanggal_pesanan
        if ($request->filled('tanggal_awal')) {
            $query->whereDate('t_pesanan.tanggal_pesanan', '>=', $request->tanggal_awal);
        }
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('t_pesanan.tanggal_pesanan', '<=', $request->tanggal_akhir);
        }

        $pesanan = $query->orderBy('t_pesanan.no_pesanan', $sort)->get();

        foreach ($pesanan as $psn) {
            $details = DB::table('t_pesanan_detail')
                ->join('t_produk', 't_pesanan_detail.kode_produk', '=', 't_produk.kode_produk')
                ->where('t_pesanan_detail.no_pesanan', $psn->no_pesanan)
                ->select(
                    't_pesanan_detail.*',
                    't_produk.nama_produk'
                )
                ->get();
            $psn->details = $details;
        }

        return view('pesananpenjualan.index', compact('pesanan'));
    }

    public function create()
    {
        $last = DB::table('t_pesanan')->orderBy('no_pesanan', 'desc')->first();
        if ($last) {
            $lastNumber = intval(substr($last->no_pesanan, 2));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        $no_pesanan = 'PS' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);

        $pelanggan = DB::table('t_pelanggan')->get();
        $produk = DB::table('t_produk')->get();

        return view('pesananpenjualan.create', compact('no_pesanan', 'pelanggan', 'produk'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'no_pesanan' => 'required|unique:t_pesanan,no_pesanan',
            'tanggal_pesanan' => 'required|date',
            'kode_pelanggan' => 'required',
            'total_pesanan' => 'required|numeric',
            'keterangan' => 'nullable|string|max:255',
            'detail_json' => 'required|json'
        ]);

        DB::transaction(function () use ($request) {
            DB::table('t_pesanan')->insert([
                'no_pesanan' => $request->no_pesanan,
                'tanggal_pesanan' => $request->tanggal_pesanan,
                'kode_pelanggan' => $request->kode_pelanggan,
                'total_pesanan' => $request->total_pesanan,
                'keterangan' => $request->keterangan,
            ]);

            $details = json_decode($request->detail_json, true);
            foreach ($details as $i => $detail) {
                DB::table('t_pesanan_detail')->insert([
                    'no_detailpesanan' => $request->no_pesanan . '-' . ($i+1),
                    'no_pesanan' => $request->no_pesanan,
                    'kode_produk' => $detail['kode_produk'],
                    'jumlah' => $detail['jumlah'],
                    'harga_satuan' => $detail['harga_satuan'],
                    'subtotal' => $detail['subtotal'],
                ]);
            }
        });

        return redirect()->route('pesananpenjualan.index')->with('success', 'Pesanan penjualan berhasil disimpan!');
    }

    public function edit($no_pesanan)
    {
        $pesanan = DB::table('t_pesanan')->where('no_pesanan', $no_pesanan)->first();
        $pelanggan = DB::table('t_pelanggan')->get();
        $produk = DB::table('t_produk')->get();

        $details = DB::table('t_pesanan_detail')
            ->join('t_produk', 't_pesanan_detail.kode_produk', '=', 't_produk.kode_produk')
            ->where('t_pesanan_detail.no_pesanan', $no_pesanan)
            ->select(
                't_pesanan_detail.kode_produk',
                't_produk.nama_produk',
                't_pesanan_detail.jumlah',
                't_pesanan_detail.harga_satuan',
                't_pesanan_detail.subtotal'
            )
            ->get();

        $detailsArr = [];
        foreach ($details as $d) {
            $detailsArr[] = [
                'kode_produk' => $d->kode_produk,
                'nama_produk' => $d->nama_produk,
                'jumlah' => $d->jumlah,
                'harga_satuan' => $d->harga_satuan,
                'subtotal' => $d->subtotal,
            ];
        }

        return view('pesananpenjualan.edit', [
            'pesanan' => $pesanan,
            'pelanggan' => $pelanggan,
            'produk' => $produk,
            'details' => $detailsArr
        ]);
    }

    public function update(Request $request, $no_pesanan)
    {
        $request->validate([
            'tanggal_pesanan' => 'required|date',
            'kode_pelanggan' => 'required',
            'total_pesanan' => 'required|numeric',
            'keterangan' => 'nullable|string|max:255',
            'detail_json' => 'required|json'
        ]);

        DB::transaction(function () use ($request, $no_pesanan) {
            DB::table('t_pesanan')->where('no_pesanan', $no_pesanan)->update([
                'tanggal_pesanan' => $request->tanggal_pesanan,
                'kode_pelanggan' => $request->kode_pelanggan,
                'total_pesanan' => $request->total_pesanan,
                'keterangan' => $request->keterangan,
            ]);

            DB::table('t_pesanan_detail')->where('no_pesanan', $no_pesanan)->delete();

            $details = json_decode($request->detail_json, true);
            foreach ($details as $i => $detail) {
                DB::table('t_pesanan_detail')->insert([
                    'no_detailpesanan' => $no_pesanan . '-' . ($i+1),
                    'no_pesanan' => $no_pesanan,
                    'kode_produk' => $detail['kode_produk'],
                    'jumlah' => $detail['jumlah'],
                    'harga_satuan' => $detail['harga_satuan'],
                    'subtotal' => $detail['subtotal'],
                ]);
            }
        });

        return redirect()->route('pesananpenjualan.index')->with('success', 'Pesanan penjualan berhasil diupdate!');
    }

    public function destroy($no_pesanan)
    {
        DB::transaction(function () use ($no_pesanan) {
            DB::table('t_pesanan_detail')->where('no_pesanan', $no_pesanan)->delete();
            DB::table('t_pesanan')->where('no_pesanan', $no_pesanan)->delete();
        });

        return redirect()->route('pesananpenjualan.index')->with('success', 'Pesanan penjualan berhasil dihapus!');
    }

    public function show($no_pesanan)
    {
        $pesanan = DB::table('t_pesanan')
            ->leftJoin('t_pelanggan', 't_pesanan.kode_pelanggan', '=', 't_pelanggan.kode_pelanggan')
            ->where('no_pesanan', $no_pesanan)
            ->select('t_pesanan.*', 't_pelanggan.nama_pelanggan')
            ->first();

        $details = DB::table('t_pesanan_detail')
            ->join('t_produk', 't_pesanan_detail.kode_produk', '=', 't_produk.kode_produk')
            ->where('t_pesanan_detail.no_pesanan', $no_pesanan)
            ->select(
                't_pesanan_detail.*',
                't_produk.nama_produk'
            )
            ->get();

        return view('pesananpenjualan.detail', compact('pesanan', 'details'));
    }

    public function cetak($no_pesanan)
    {
        $pesanan = DB::table('t_pesanan')
            ->leftJoin('t_pelanggan', 't_pesanan.kode_pelanggan', '=', 't_pelanggan.kode_pelanggan')
            ->where('no_pesanan', $no_pesanan)
            ->select('t_pesanan.*', 't_pelanggan.nama_pelanggan')
            ->first();

        $details = DB::table('t_pesanan_detail')
            ->join('t_produk', 't_pesanan_detail.kode_produk', '=', 't_produk.kode_produk')
            ->where('t_pesanan_detail.no_pesanan', $no_pesanan)
            ->select(
                't_pesanan_detail.*',
                't_produk.nama_produk'
            )
            ->get();

        return view('pesananpenjualan.cetak', compact('pesanan', 'details'));
    }
}