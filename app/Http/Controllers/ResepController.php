<?php

namespace App\Http\Controllers;

use App\Models\Resep;
use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\Bahan;
use App\Models\ResepDetail;
use Illuminate\Support\Facades\DB;

class ResepController extends Controller
{
    public function index()
    {
        $reseps = Resep::with(['produk', 'details.bahan'])->get();
        return view('resep.index', compact('reseps'));
    }

    public function create()
    {
        $produk = Produk::all();
        $bahan = Bahan::all();

        // Ambil kode terakhir dari t_resep
        $lastKode = \DB::table('t_resep')->orderBy('kode_resep', 'desc')->value('kode_resep');
        // Format kode baru, misal: RSP001, RSP002, dst
        $nextNumber = $lastKode ? intval(substr($lastKode, 3)) + 1 : 1;
        $kode_resep = 'RSP' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        return view('resep.create', compact('produk', 'bahan', 'kode_resep'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_resep' => 'required|unique:t_resep,kode_resep',
            'kode_produk' => 'required|exists:t_produk,kode_produk',
            'bahan.*.kode_bahan' => 'required|exists:t_bahan,kode_bahan',
            'bahan.*.satuan' => 'required|string',
            'bahan.*.jumlah_kebutuhan' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            Resep::create([
                'kode_resep' => $request->kode_resep,
                'kode_produk' => $request->kode_produk,
                'keterangan' => $request->keterangan,
            ]);

            foreach ($request->bahan as $i => $row) {
                ResepDetail::create([
                    'kode_resep_detail' => $request->kode_resep . '-' . str_pad($i + 1, 2, '0', STR_PAD_LEFT),
                    'kode_resep' => $request->kode_resep,
                    'kode_bahan' => $row['kode_bahan'],
                    'jumlah_kebutuhan' => $row['jumlah_kebutuhan'],
                    'satuan' => $row['satuan'],
                ]);
            }
        });

        return redirect()->route('resep.index')->with('success', 'Resep berhasil disimpan!');
    }
}
