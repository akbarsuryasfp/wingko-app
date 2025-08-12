<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KartuPersKonsinyasiController extends Controller
{
    // Untuk menghindari error resource route yang memanggil show()
    public function show($id)
    {
        abort(404);
    }
    // Tampilkan halaman kartu stok produk konsinyasi
    public function produkKonsinyasi(Request $request)
    {
        $produkKonsinyasiList = DB::table('t_produk_konsinyasi')
            ->select('kode_produk', 'nama_produk', 'satuan')
            ->get();

        $satuan = '';

        // Ambil filter dari request
        $tanggal_awal = $request->input('tanggal_awal');
        $tanggal_akhir = $request->input('tanggal_akhir');
        $kode_produk = $request->input('kode_produk_konsinyasi');
        $lokasi = $request->input('lokasi_konsinyasi');

        // Query data kartu persediaan konsinyasi
        $query = DB::table('t_kartuperskonsinyasi');
        if ($tanggal_awal) {
            $query->whereDate('tanggal', '>=', $tanggal_awal);
        }
        if ($tanggal_akhir) {
            $query->whereDate('tanggal', '<=', $tanggal_akhir);
        }
        if ($kode_produk) {
            $query->where('kode_produk', $kode_produk);
            // Set satuan otomatis jika produk dipilih
            $produk = $produkKonsinyasiList->where('kode_produk', $kode_produk)->first();
            $satuan = $produk ? $produk->satuan : '';
        }
        if ($lokasi) {
            $query->where('lokasi', $lokasi);
        }
        // Filter search
        $search = $request->input('search');
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('no_transaksi', 'like', "%$search%")
                  ->orWhere('kode_produk', 'like', "%$search%")
                  ->orWhere('keterangan', 'like', "%$search%")
                ;
            });
        }
        $riwayat = $query->orderBy('tanggal')->orderBy('id')->get();

        return view('kartuperskonsinyasi.produkkonsinyasi', compact('produkKonsinyasiList', 'satuan', 'riwayat'));
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
    
    // API: Menghitung stok akhir produk konsinyasi
    public function getStokAkhirProdukKonsinyasi($kode_produk)
    {
        $data = \DB::table('t_kartuperskonsinyasi')
            ->select('harga_konsinyasi', \DB::raw('SUM(masuk) - SUM(keluar) as sisa'))
            ->where('kode_produk', $kode_produk)
            ->groupBy('harga_konsinyasi')
            ->havingRaw('sisa > 0')
            ->get();
        return response()->json($data);
    }
}
