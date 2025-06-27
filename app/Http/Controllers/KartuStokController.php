<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Kartustok;

class KartuStokController extends Controller
{
    public function bahan(Request $request)
    {
        $bahanList = Kartustok::all();
        $kode_bahan = $request->kode_bahan;
        $satuan = '';
        $dataPersediaan = [];
        $stokAkhir = [];

        if ($kode_bahan) {
            $bahan = Kartustok::where('kode_bahan', $kode_bahan)->first();
            $satuan = $bahan ? $bahan->satuan : '';

            $dataPersediaan = DB::table('t_kartupersbahan')
                ->where('kode_bahan', $kode_bahan)
                ->orderBy('tanggal')
                ->orderBy('id')
                ->get();

            $stokAkhir = DB::table('t_kartupersbahan')
                ->select('harga', DB::raw('SUM(masuk) as total_masuk'), DB::raw('SUM(keluar) as total_keluar'), DB::raw('SUM(masuk) - SUM(keluar) as sisa'))
                ->where('kode_bahan', $kode_bahan)
                ->groupBy('harga')
                ->havingRaw('sisa > 0')
                ->orderBy('harga')
                ->get();
        }

        return view('kartustok.bahan', compact('bahanList', 'dataPersediaan', 'satuan', 'stokAkhir'));
    }

    public function create()
    {
        $bahanList = \DB::table('t_bahan')->get();
        return view('terimabahan.create', compact('bahanList'));
    }

    public function getKartuPersBahan($kode_bahan)
    {
        $dataPersediaan = \DB::table('t_kartupersbahan')
            ->where('kode_bahan', $kode_bahan)
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();

        return response()->json($dataPersediaan);
    }

    public function produk(Request $request)
    {
        $produkList = \DB::table('t_produk')->get();
        $satuan = '';
        return view('kartustok.produk', compact('produkList', 'satuan'));
    }

    public function getKartuPersProduk($kode_produk)
    {
        $dataPersediaan = \DB::table('t_kartupersproduk')
            ->where('kode_produk', $kode_produk)
            ->orderBy('tanggal')
            ->get();

        return response()->json($dataPersediaan);
    }

    public function updateStokBahan($kode_bahan)
    {
        // Logika untuk memperbarui stok bahan setelah transaksi
        $stokAkhir = DB::table('t_kartupersbahan')
            ->select('harga', DB::raw('SUM(masuk) as total_masuk'), DB::raw('SUM(keluar) as total_keluar'), DB::raw('SUM(masuk) - SUM(keluar) as sisa'))
            ->where('kode_bahan', $kode_bahan)
            ->groupBy('harga')
            ->havingRaw('sisa > 0')
            ->orderBy('harga')
            ->get();
    }
}
