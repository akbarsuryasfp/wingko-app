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
    // Prevent error for undefined show method
    public function show($id)
    {
        abort(404, 'Halaman tidak ditemukan');
    }
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
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('no_jual', 'like', "%$search%")
                  ->orWhereHas('pelanggan', function($q2) use ($search) {
                      $q2->where('nama_pelanggan', 'like', "%$search%");
                  })
                  ->orWhereHas('details', function($q3) use ($search) {
                      $q3->whereHas('produk', function($q4) use ($search) {
                          $q4->where('nama_produk', 'like', "%$search%");
                      });
                  });
            });
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


    public function cetakLaporan(Request $request)
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
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('no_jual', 'like', "%$search%")
                  ->orWhereHas('pelanggan', function($q2) use ($search) {
                      $q2->where('nama_pelanggan', 'like', "%$search%");
                  })
                  ->orWhereHas('details', function($q3) use ($search) {
                      $q3->whereHas('produk', function($q4) use ($search) {
                          $q4->where('nama_produk', 'like', "%$search%");
                      });
                  });
            });
        }
        $sort = $request->get('sort', 'asc');
        $query->orderBy('no_jual', $sort);
        $penjualanKonsinyasi = $query->get();
        return view('jualkonsinyasimasuk.cetak_laporan', compact('penjualanKonsinyasi'));
    }
}
