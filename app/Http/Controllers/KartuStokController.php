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

        if ($kode_bahan) {
            $bahan = Kartustok::where('kode_bahan', $kode_bahan)->first();
            $satuan = $bahan ? $bahan->satuan : '';

            // Query gabungan masuk/keluar
            $dataPersediaan = DB::select("
                SELECT tanggal_terima AS tanggal, no_terima_bahan AS no_transaksi, 'Penerimaan' AS keterangan, jumlah_masuk AS masuk, 0 AS keluar, harga_beli AS harga, satuan
                FROM t_terimab_detail
                JOIN t_terimabahan ON t_terimabahan.no_terima_bahan = t_terimab_detail.no_terima_bahan
                JOIN t_bahan ON t_bahan.kode_bahan = t_terimab_detail.kode_bahan
                WHERE t_terimab_detail.kode_bahan = ?
                UNION ALL
                SELECT tanggal_keluar AS tanggal, no_keluar AS no_transaksi, 'Pemakaian' AS keterangan, 0 AS masuk, jumlah_keluar AS keluar, harga_keluar AS harga, satuan
                FROM t_pemakaian_detail
                JOIN t_pemakaian ON t_pemakaian.no_keluar = t_pemakaian_detail.no_keluar
                JOIN t_bahan ON t_bahan.kode_bahan = t_pemakaian_detail.kode_bahan
                WHERE t_pemakaian_detail.kode_bahan = ?
                ORDER BY tanggal ASC
            ", [$kode_bahan, $kode_bahan]);
        }

        return view('kartustok.bahan', compact('bahanList', 'dataPersediaan', 'satuan'));
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
    }
}
