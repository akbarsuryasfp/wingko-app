<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Kartustok;
use Barryvdh\DomPDF\Facade\Pdf;

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

    // GABUNGAN: fungsi produk dari versi asli (ada riwayat)
    public function produk(Request $request)
    {
        $produkList = \DB::table('t_produk')->get();
        $kode_produk = $request->kode_produk;
        $lokasi = $request->lokasi;

        $riwayat = [];
        $satuan = '';

        if ($kode_produk) {
            $riwayatQuery = DB::table('t_kartupersproduk')
                ->where('kode_produk', $kode_produk);

            if ($lokasi) {
                $riwayatQuery->where('lokasi', $lokasi);
            }

            $riwayat = $riwayatQuery
                ->orderBy('tanggal', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            $satuan = DB::table('t_produk')->where('kode_produk', $kode_produk)->value('satuan');
        }

        return view('kartustok.produk', compact('produkList', 'riwayat', 'satuan'));
    }

    // GABUNGAN: getKartuPersProduk, tambahkan 'satuan' ke select
    public function getKartuPersProduk(Request $request, $kode_produk)
    {
        $lokasi = $request->get('lokasi');
        $query = DB::table('t_kartupersproduk')
            ->select(
                'no_transaksi',
                'tanggal',
                'masuk',
                'keluar',
                'hpp',
                'satuan', // pastikan field satuan tetap ada
                'keterangan',
                'tanggal_exp',
                'lokasi'
            )
            ->where('kode_produk', $kode_produk);
        if ($lokasi) {
            $query->where('lokasi', $lokasi);
        }
        $data = $query->orderBy('tanggal')->get();

        return response()->json($data);
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

    public function laporanBahan()
    {
        $bahanList = \DB::table('t_bahan')->select('kode_bahan','nama_bahan','satuan','stokmin')->get();
        $tanggal = date('Y-m-d');

        // Ambil stok akhir per bahan dan harga dari t_kartupersbahan
        $stokAkhirList = \DB::table('t_kartupersbahan')
            ->select('kode_bahan', 'harga', \DB::raw('SUM(masuk) - SUM(keluar) as stok'))
            ->groupBy('kode_bahan', 'harga')
            ->havingRaw('stok > 0')
            ->get();

        // Gabungkan stok akhir ke bahanList
        foreach ($bahanList as $bahan) {
            $bahan->stok_akhir = $stokAkhirList->where('kode_bahan', $bahan->kode_bahan)->values();
        }

        return view('kartustok.laporan_bahan', compact('bahanList', 'tanggal'));
    }

    public function laporanProduk()
    {
        $produkList = \DB::table('t_produk')->select('kode_produk','nama_produk','satuan','stokmin')->get();
        $tanggal = date('Y-m-d');

        // Ambil stok akhir per produk dan HPP dari t_kartupersproduk
        $stokAkhirList = \DB::table('t_kartupersproduk')
            ->select('kode_produk', 'lokasi', 'hpp', \DB::raw('SUM(masuk) - SUM(keluar) as stok'))
            ->groupBy('kode_produk', 'lokasi', 'hpp')
            ->havingRaw('stok > 0')
            ->get();

        // Gabungkan stok akhir ke produkList
        foreach ($produkList as $produk) {
            $produk->stok_akhir = $stokAkhirList->where('kode_produk', $produk->kode_produk)->values();
        }

        return view('kartustok.laporan_produk', compact('produkList', 'tanggal'));
    }

    public function laporanBahanPdf(Request $request)
    {
        $bahanList = \DB::table('t_bahan')->select('kode_bahan','nama_bahan','satuan','stokmin')->get();
        $tanggal = date('Y-m-d');

        $stokAkhirList = \DB::table('t_kartupersbahan')
            ->select('kode_bahan', 'harga', \DB::raw('SUM(masuk) - SUM(keluar) as stok'))
            ->groupBy('kode_bahan', 'harga')
            ->havingRaw('stok > 0')
            ->get();

        foreach ($bahanList as $bahan) {
            $bahan->stok_akhir = $stokAkhirList->where('kode_bahan', $bahan->kode_bahan)->values();
        }

        return Pdf::loadView('kartustok.laporan_bahan_pdf', compact('bahanList'))
            ->setPaper('a4', 'landscape')
            ->download('Laporan_Stok_Bahan_Baku.pdf');
    }
    public function laporanProdukPdf()
    {
        $produkList = \DB::table('t_produk')->select('kode_produk','nama_produk','satuan','stokmin')->get();
        $tanggal = date('Y-m-d');

        $stokAkhirList = \DB::table('t_kartupersproduk')
            ->select('kode_produk', 'lokasi', 'hpp', \DB::raw('SUM(masuk) - SUM(keluar) as stok'))
            ->groupBy('kode_produk', 'lokasi', 'hpp')
            ->havingRaw('stok > 0')
            ->get();

        foreach ($produkList as $produk) {
            $produk->stok_akhir = $stokAkhirList->where('kode_produk', $produk->kode_produk)->values();
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('kartustok.laporan_produk_pdf', compact('produkList', 'tanggal'))
            ->setPaper('a4', 'landscape');

        return $pdf->stream('Laporan_Stok_Akhir_Produk.pdf');
    }

    public function getStokAkhirProduk($kode_produk)
    {
        $stok = \DB::table('t_kartupersproduk')
            ->where('kode_produk', $kode_produk)
            ->selectRaw('SUM(masuk) - SUM(keluar) as stok')
            ->value('stok');
        return response()->json(['stok' => $stok ?? 0]);
    }
}
