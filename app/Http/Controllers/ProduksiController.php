<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produksi;
use App\Models\ProduksiDetail;
use App\Models\JadwalProduksi;
use Illuminate\Support\Facades\DB;

class ProduksiController extends Controller
{
    public function create()
    {
        $jadwal = JadwalProduksi::with('details.produk')->get();

        return view('produksi.create', compact('jadwal'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal_produksi' => 'required|date',
            'produk.*.kode_produk' => 'required',
            'produk.*.jumlah_unit' => 'required|integer|min:1',
            'produk.*.tanggal_expired' => 'required|date',
        ]);

        DB::transaction(function () use ($request) {
            $kode = 'PRD' . now()->format('YmdHis');
            $keterangan = 'Produksi tanggal ' . date('d/m/Y', strtotime($request->tanggal_produksi));

            Produksi::create([
                'no_produksi' => $kode,
                'tanggal_produksi' => $request->tanggal_produksi,
                'keterangan' => $keterangan,
            ]);

            foreach ($request->produk as $i => $produk) {
                \DB::table('t_produksi_detail')->insert([
                    'no_detail_produksi' => $kode . '-' . ($i + 1),
                    'no_produksi' => $kode,
                    'kode_produk' => $produk['kode_produk'],
                    'jumlah_unit' => $produk['jumlah_unit'],
                    'tanggal_expired' => $produk['tanggal_expired'], // <-- perbaiki di sini
                ]);
            }
        });

        return redirect()->route('produksi.index')->with('success', 'Data produksi berhasil disimpan!');
    }
    
    
    public function index()
    {
        $produksi = Produksi::with('details.produk')->orderBy('tanggal_produksi', 'desc')->get();
        return view('produksi.index', compact('produksi'));
    }
}
