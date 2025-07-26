<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\JualKonsinyasiMasuk;
use App\Models\JualKonsinyasiMasukDetail;
use App\Models\ProdukKonsinyasi;
use App\Models\Consignor;

class JualKonsinyasiMasukController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\Penjualan::with(['pelanggan', 'details.produk'])
            ->whereHas('details', function($q) {
                $q->where('kode_produk', 'like', 'PKM%');
            });
        if ($request->filled('tanggal_awal')) {
            $query->where('tanggal_jual', '>=', $request->tanggal_awal);
        }
        if ($request->filled('tanggal_akhir')) {
            $query->where('tanggal_jual', '<=', $request->tanggal_akhir);
        }
        $sort = $request->get('sort', 'asc');
        $query->orderBy('no_jual', $sort);
        $penjualanKonsinyasi = $query->get();
        return view('jualkonsinyasimasuk.index', compact('penjualanKonsinyasi'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'detail_json' => 'required|json',
            'tanggal_jual' => 'required|date',
            'kode_consignor' => 'required',
        ]);
        DB::transaction(function () use ($request) {
            $header = JualKonsinyasiMasuk::create([
                'no_jualkonsinyasimasuk' => $request->no_jualkonsinyasimasuk,
                'tanggal_jual' => $request->tanggal_jual,
                'kode_consignor' => $request->kode_consignor,
                'total_jual' => $request->total_jual,
                'keterangan' => $request->keterangan,
            ]);
            $details = json_decode($request->detail_json, true);
            foreach ($details as $d) {
                JualKonsinyasiMasukDetail::create([
                    'no_jualkonsinyasimasuk' => $header->no_jualkonsinyasimasuk,
                    'kode_produk' => $d['kode_produk'],
                    'jumlah' => $d['jumlah'],
                    'harga_jual' => $d['harga_jual'],
                    'subtotal' => $d['subtotal'],
                ]);
                // Kurangi stok produk konsinyasi
                ProdukKonsinyasi::where('kode_produk', $d['kode_produk'])->decrement('stok', $d['jumlah']);
            }
        });
        return redirect()->route('jualkonsinyasimasuk.index')->with('success', 'Data berhasil disimpan!');
    }

    public function show($no_jualkonsinyasimasuk)
    {
        $jual = JualKonsinyasiMasuk::with(['consignor', 'details'])->where('no_jualkonsinyasimasuk', $no_jualkonsinyasimasuk)->firstOrFail();
        $produkMaster = DB::table('t_produk')->select('kode_produk', 'nama_produk')->get();
        return view('jualkonsinyasimasuk.detail', compact('jual', 'produkMaster'));
    }

    public function edit($no_jualkonsinyasimasuk)
    {
        $jual = JualKonsinyasiMasuk::with(['consignor', 'details'])->where('no_jualkonsinyasimasuk', $no_jualkonsinyasimasuk)->firstOrFail();
        $consignor = Consignor::all();
        $produkKonsinyasi = ProdukKonsinyasi::all();
        $details = $jual->details;
        return view('jualkonsinyasimasuk.edit', compact('jual', 'consignor', 'produkKonsinyasi', 'details'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'detail_json' => 'required|json',
        ]);
        DB::transaction(function () use ($request, $id) {
            $jual = JualKonsinyasiMasuk::where('no_jualkonsinyasimasuk', $id)->firstOrFail();
            $jual->update([
                'tanggal_jual' => $request->tanggal_jual,
                'kode_consignor' => $request->kode_consignor,
                'total_jual' => $request->total_jual,
                'keterangan' => $request->keterangan,
            ]);
            JualKonsinyasiMasukDetail::where('no_jualkonsinyasimasuk', $id)->delete();
            $details = json_decode($request->detail_json, true);
            foreach ($details as $d) {
                JualKonsinyasiMasukDetail::create([
                    'no_jualkonsinyasimasuk' => $id,
                    'kode_produk' => $d['kode_produk'],
                    'jumlah' => $d['jumlah'],
                    'harga_jual' => $d['harga_jual'],
                    'subtotal' => $d['subtotal'],
                ]);
            }
        });
        return redirect()->route('jualkonsinyasimasuk.index')->with('success', 'Data berhasil diupdate!');
    }

    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            JualKonsinyasiMasukDetail::where('no_jualkonsinyasimasuk', $id)->delete();
            JualKonsinyasiMasuk::where('no_jualkonsinyasimasuk', $id)->delete();
        });
        return redirect()->route('jualkonsinyasimasuk.index')->with('success', 'Data berhasil dihapus!');
    }

    public function cetak($no_jualkonsinyasimasuk)
    {
        $jual = JualKonsinyasiMasuk::with(['consignor', 'details'])->where('no_jualkonsinyasimasuk', $no_jualkonsinyasimasuk)->firstOrFail();
        $produkMaster = DB::table('t_produk')->select('kode_produk', 'nama_produk')->get();
        return view('jualkonsinyasimasuk.cetak', compact('jual', 'produkMaster'));
    }
}
