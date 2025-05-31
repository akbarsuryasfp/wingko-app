<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenjualanController extends Controller
{
    public function index()
    {
        $penjualan = DB::table('t_penjualan')
            ->leftJoin('t_pelanggan', 't_penjualan.kode_pelanggan', '=', 't_pelanggan.kode_pelanggan')
            ->select('t_penjualan.*', 't_pelanggan.nama_pelanggan')
            ->orderBy('t_penjualan.tanggal_jual', 'desc')
            ->get();

        foreach ($penjualan as $jual) {
            $details = DB::table('t_penjualan_detail')
                ->join('t_produk', 't_penjualan_detail.kode_produk', '=', 't_produk.kode_produk')
                ->where('t_penjualan_detail.no_jual', $jual->no_jual)
                ->select(
                    't_penjualan_detail.*',
                    't_produk.nama_produk'
                )
                ->get();
            $jual->details = $details;
        }

        return view('penjualan.index', compact('penjualan'));
    }

    public function create()
    {
        // Generate kode penjualan otomatis
        $last = DB::table('t_penjualan')->orderBy('no_jual', 'desc')->first();
        if ($last) {
            $lastNumber = intval(substr($last->no_jual, 2));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        $no_jual = 'PJ' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);

        $pelanggan = DB::table('t_pelanggan')->get();
        $produk = DB::table('t_produk')->get();

        return view('penjualan.create', compact('no_jual', 'pelanggan', 'produk'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'no_jual' => 'required|unique:t_penjualan,no_jual',
            'tanggal_jual' => 'required|date',
            'kode_pelanggan' => 'required',
            'total' => 'required|numeric',
            'metode_pembayaran' => 'required|in:tunai,kredit',
            'status_pembayaran' => 'required|in:belum lunas,lunas',
            'keterangan' => 'nullable',
            'kode_user' => 'required',
            'detail_json' => 'required|json'
        ]);

        DB::transaction(function () use ($request) {
            DB::table('t_penjualan')->insert([
                'no_jual' => $request->no_jual,
                'tanggal_jual' => $request->tanggal_jual,
                'kode_pelanggan' => $request->kode_pelanggan,
                'total' => $request->total,
                'metode_pembayaran' => $request->metode_pembayaran,
                'status_pembayaran' => $request->status_pembayaran,
                'keterangan' => $request->keterangan,
                'kode_user' => $request->kode_user,
            ]);

            // Insert detail
            $details = json_decode($request->detail_json, true);
            foreach ($details as $i => $detail) {
                DB::table('t_penjualan_detail')->insert([
                    'no_detailjual' => $request->no_jual . '-' . ($i+1),
                    'no_jual' => $request->no_jual,
                    'kode_produk' => $detail['kode_produk'],
                    'jumlah' => $detail['jumlah'],
                    'harga_satuan' => $detail['harga_satuan'],
                    'subtotal' => $detail['subtotal'],
                ]);
            }
        });

        return redirect()->route('penjualan.index')->with('success', 'Penjualan berhasil disimpan!');
    }

    public function edit($no_jual)
    {
        $penjualan = DB::table('t_penjualan')->where('no_jual', $no_jual)->first();
        $pelanggan = DB::table('t_pelanggan')->get();
        $produk = DB::table('t_produk')->get();

        $details = DB::table('t_penjualan_detail')
            ->join('t_produk', 't_penjualan_detail.kode_produk', '=', 't_produk.kode_produk')
            ->where('t_penjualan_detail.no_jual', $no_jual)
            ->select(
                't_penjualan_detail.kode_produk',
                't_produk.nama_produk',
                't_penjualan_detail.jumlah',
                't_penjualan_detail.harga_satuan',
                't_penjualan_detail.subtotal'
            )
            ->get();

        // Untuk JS, array asosiatif
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

        return view('penjualan.edit', [
            'penjualan' => $penjualan,
            'pelanggan' => $pelanggan,
            'produk' => $produk,
            'details' => $detailsArr
        ]);
    }

    public function update(Request $request, $no_jual)
    {
        $request->validate([
            'tanggal_jual' => 'required|date',
            'kode_pelanggan' => 'required',
            'total' => 'required|numeric',
            'metode_pembayaran' => 'required|in:tunai,kredit',
            'status_pembayaran' => 'required|in:belum lunas,lunas',
            'keterangan' => 'nullable',
            'kode_user' => 'required',
            'detail_json' => 'required|json'
        ]);

        DB::transaction(function () use ($request, $no_jual) {
            DB::table('t_penjualan')->where('no_jual', $no_jual)->update([
                'tanggal_jual' => $request->tanggal_jual,
                'kode_pelanggan' => $request->kode_pelanggan,
                'total' => $request->total,
                'metode_pembayaran' => $request->metode_pembayaran,
                'status_pembayaran' => $request->status_pembayaran,
                'keterangan' => $request->keterangan,
                'kode_user' => $request->kode_user,
            ]);

            // Hapus detail lama
            DB::table('t_penjualan_detail')->where('no_jual', $no_jual)->delete();

            // Simpan detail baru
            $details = json_decode($request->detail_json, true);
            foreach ($details as $i => $detail) {
                DB::table('t_penjualan_detail')->insert([
                    'no_detailjual' => $no_jual . '-' . ($i+1),
                    'no_jual' => $no_jual,
                    'kode_produk' => $detail['kode_produk'],
                    'jumlah' => $detail['jumlah'],
                    'harga_satuan' => $detail['harga_satuan'],
                    'subtotal' => $detail['subtotal'],
                ]);
            }
        });

        return redirect()->route('penjualan.index')->with('success', 'Penjualan berhasil diupdate!');
    }

    public function destroy($no_jual)
    {
        DB::transaction(function () use ($no_jual) {
            DB::table('t_penjualan_detail')->where('no_jual', $no_jual)->delete();
            DB::table('t_penjualan')->where('no_jual', $no_jual)->delete();
        });

        return redirect()->route('penjualan.index')->with('success', 'Penjualan berhasil dihapus!');
    }

    public function show($no_jual)
    {
        $terima = DB::table('t_penjualan')
            ->leftJoin('t_pelanggan', 't_penjualan.kode_pelanggan', '=', 't_pelanggan.kode_pelanggan')
            ->where('no_jual', $no_jual)
            ->select('t_penjualan.*', 't_pelanggan.nama_pelanggan')
            ->first();

        $details = DB::table('t_penjualan_detail')
            ->join('t_produk', 't_penjualan_detail.kode_produk', '=', 't_produk.kode_produk')
            ->where('t_penjualan_detail.no_jual', $no_jual)
            ->select(
                't_penjualan_detail.*',
                't_produk.nama_produk'
            )
            ->get();

        return view('penjualan.detail', compact('terima', 'details'));
    }
}