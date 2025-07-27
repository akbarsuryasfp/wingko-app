<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KartuPersKonsinyasiController extends Controller
{
    // Tampilkan halaman kartu stok produk konsinyasi
    public function produkKonsinyasi(Request $request)
    {
        $produkKonsinyasiList = DB::table('t_produk_konsinyasi')
            ->select('kode_produk', 'nama_produk', 'satuan')
            ->get();
        $satuan = '';
        return view('kartuperskonsinyasi.produkkonsinyasi', compact('produkKonsinyasiList', 'satuan'));
    }
    
    // API: Data riwayat masuk/keluar produk konsinyasi
    public function getKartuPersProdukKonsinyasi(Request $request, $kode_produk)
    {
        $lokasi = $request->get('lokasi');
        $query = DB::table('t_kartuperskonsinyasi')
            ->select('id', 'no_transaksi', 'tanggal', 'kode_produk', 'masuk', 'keluar', 'sisa', 'harga_konsinyasi', 'lokasi', 'keterangan')
            ->where('kode_produk', $kode_produk);
        if ($lokasi) {
            $query->where('lokasi', $lokasi);
        }
        $data = $query->orderBy('tanggal')->orderBy('id')->get();
        return response()->json($data);
    }
}
