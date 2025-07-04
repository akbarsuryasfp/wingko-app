<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\KonsinyasiMasuk;
use App\Models\ProdukKonsinyasi; // Pastikan model ini ada
use App\Models\Consignor; // Tambahkan use statement untuk model Consignor

class KonsinyasiMasukController extends Controller
{
    public function index()
    {
        $konsinyasiMasukList = KonsinyasiMasuk::with(['consignor', 'details'])->get();
        return view('konsinyasimasuk.index', compact('konsinyasiMasukList'));
    }

    public function create()
    {
        $consignor = Consignor::all();
        $produkKonsinyasi = ProdukKonsinyasi::all(); // ambil semua produk
        $last = DB::table('t_konsinyasimasuk')->orderBy('no_surattitipjual', 'desc')->first();
        $no_surattitipjual = $last ? 'KTJ' . str_pad(intval(substr($last->no_surattitipjual, 3)) + 1, 6, '0', STR_PAD_LEFT) : 'KTJ000001';
        return view('konsinyasimasuk.create', compact('consignor', 'produkKonsinyasi', 'no_surattitipjual'));
    }

    public function store(Request $request)
    {
        // Validasi
        $request->validate([
            'no_surattitipjual' => 'required|unique:t_konsinyasimasuk,no_surattitipjual',
            'kode_consignor' => 'required',
            'tanggal_masuk' => 'required|date', // Ganti ke tanggal_masuk
            'total_titip' => 'required|numeric',
            'keterangan' => 'nullable|string|max:255',
            'detail_json' => 'required|json'
        ]);

        DB::transaction(function () use ($request) {
            DB::table('t_konsinyasimasuk')->insert([
                'no_surattitipjual' => $request->no_surattitipjual,
                'kode_consignor' => $request->kode_consignor,
                'tanggal_masuk' => $request->tanggal_masuk, // Ambil dari tanggal_masuk
                'total_titip' => $request->total_titip,
                'keterangan' => $request->keterangan,
            ]);

            $details = json_decode($request->detail_json, true);
            foreach ($details as $i => $detail) {
                DB::table('t_konsinyasimasuk_detail')->insert([
                    'no_surattitipjual' => $request->no_surattitipjual,
                    'kode_produk' => $detail['kode_produk'],
                    'jumlah_stok' => $detail['jumlah_stok'],
                    'harga_titip' => $detail['harga_titip'],
                    'subtotal' => $detail['subtotal'],
                ]);
            }
        });

        return redirect()->route('konsinyasimasuk.index')->with('success', 'Konsinyasi masuk berhasil disimpan!');
    }

    public function show($no_surattitipjual)
    {
        $konsinyasi = KonsinyasiMasuk::with(['consignor'])->where('no_surattitipjual', $no_surattitipjual)->firstOrFail();
        $details = DB::table('t_konsinyasimasuk_detail')
            ->join('t_produk', 't_konsinyasimasuk_detail.kode_produk', '=', 't_produk.kode_produk')
            ->where('t_konsinyasimasuk_detail.no_surattitipjual', $no_surattitipjual)
            ->select(
                't_konsinyasimasuk_detail.*',
                't_produk.nama_produk'
            )
            ->get();
        return view('konsinyasimasuk.detail', compact('konsinyasi', 'details'));
    }

    public function getProdukByConsignor($kode_consignor)
    {
        $produk = ProdukKonsinyasi::where('kode_consignor', $kode_consignor)->get();
        return response()->json($produk);
    }

    // Tambahkan edit, update, destroy sesuai kebutuhan
}