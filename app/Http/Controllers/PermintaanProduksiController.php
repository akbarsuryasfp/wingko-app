<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PermintaanProduksi;
use App\Models\PermintaanProduksiDetail;
use App\Models\Produk;
use Illuminate\Support\Facades\DB;

class PermintaanProduksiController extends Controller
{
    // Form input permintaan produksi
    public function create()
    {
        $produk = Produk::all();

        return view('permintaan_produksi.create', compact('produk'));
    }
    public function index()
    {
        // Ambil semua permintaan produksi beserta detail dan produk
        $permintaanProduksi = \App\Models\PermintaanProduksi::with('details.produk')->orderBy('tanggal', 'desc')->get();

        return view('permintaan_produksi.index', compact('permintaanProduksi'));
    }

    


    // Simpan permintaan produksi dan detailnya
    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string',
            'produk.*.kode_produk' => 'required|exists:t_produk,kode_produk',
            'produk.*.unit' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request) {
            // Generate kode permintaan produksi (boleh kamu ganti logikanya)
            $kode = 'PP' . now()->format('YmdHis');

            // Simpan ke t_permintaan_produksi
            $permintaan = PermintaanProduksi::create([
                'kode_permintaan_produksi' => $kode,
                'tanggal' => $request->tanggal,
                'keterangan' => $request->keterangan,
                'status' => 'Diproses',
            ]);

            // Simpan detail
            foreach ($request->produk as $i => $item) {
                PermintaanProduksiDetail::create([
                    'kode_detail_permintaan_produksi' => $kode . '-' . str_pad($i + 1, 2, '0', STR_PAD_LEFT),
                    'kode_permintaan_produksi' => $kode,
                    'kode_produk' => $item['kode_produk'],
                    'unit' => $item['unit'],
                ]);
            }
        });

        return redirect()->route('permintaan_produksi.index')->with('success', 'Permintaan produksi berhasil disimpan!');
    }
}
