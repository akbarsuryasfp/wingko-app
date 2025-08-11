<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Produk Reminder
        $reminderProduk = DB::table('t_kartupersproduk')
            ->select(
                't_kartupersproduk.kode_produk',
                't_produk.nama_produk',
                't_kartupersproduk.tanggal_exp',
                't_kartupersproduk.lokasi',
                DB::raw('SUM(masuk) - SUM(keluar) as stok')
            )
            ->join('t_produk', 't_produk.kode_produk', '=', 't_kartupersproduk.kode_produk')
            ->whereNotNull('t_kartupersproduk.tanggal_exp')
            ->groupBy('t_kartupersproduk.kode_produk', 't_kartupersproduk.tanggal_exp', 't_produk.nama_produk', 't_kartupersproduk.lokasi')
            ->havingRaw('stok > 0')
            ->get();

        $kadaluarsaProduk = collect($reminderProduk)
            ->filter(fn($r) => Carbon::parse($r->tanggal_exp)->isPast() && $r->lokasi === '1');

        $hampirProduk = collect($reminderProduk)
            ->filter(function ($r) {
                $diff = Carbon::today()->diffInDays(Carbon::parse($r->tanggal_exp), false);
                return $diff > 0 && $diff <= 3 && $r->lokasi === '1';
            });

        $groupedProduk = $kadaluarsaProduk->groupBy('lokasi');

        // Bahan Reminder
        $reminder = DB::table('t_kartupersbahan')
            ->select(
                't_kartupersbahan.kode_bahan',
                't_bahan.nama_bahan',
                't_kartupersbahan.tanggal_exp',
                DB::raw('SUM(masuk) - SUM(keluar) as stok')
            )
            ->join('t_bahan', 't_bahan.kode_bahan', '=', 't_kartupersbahan.kode_bahan')
            ->whereNotNull('t_kartupersbahan.tanggal_exp')
            ->groupBy('t_kartupersbahan.kode_bahan', 't_kartupersbahan.tanggal_exp', 't_bahan.nama_bahan')
            ->havingRaw('stok > 0')
            ->get();

        $kadaluarsa = collect($reminder)->filter(fn($r) => Carbon::parse($r->tanggal_exp)->isPast());
        $hampir = collect($reminder)->filter(function ($r) {
            $diff = Carbon::today()->diffInDays(Carbon::parse($r->tanggal_exp), false);
            return $diff > 0 && $diff <= 3;
        });
        $grouped = $kadaluarsa->groupBy('nama_bahan');

        // Penjualan Bulan Ini
        $bulan = date('m');
        $tahun = date('Y');
        $penjualanBulanIni = DB::table('t_penjualan')
            ->select(
                DB::raw('COUNT(*) as total_transaksi'),
                DB::raw('SUM(total_jual) as total_penjualan'),
                DB::raw('AVG(total_jual) as rata_rata')
            )
            ->whereMonth('tanggal_jual', $bulan)
            ->whereYear('tanggal_jual', $tahun)
            ->first();

        // Grafik Penjualan
        $grafikPenjualan = DB::table('t_penjualan')
            ->select(
                DB::raw('DAY(tanggal_jual) as hari'),
                DB::raw('SUM(total_jual) as total')
            )
            ->whereMonth('tanggal_jual', $bulan)
            ->whereYear('tanggal_jual', $tahun)
            ->groupBy(DB::raw('DAY(tanggal_jual)'))
            ->orderBy('hari')
            ->get();

        $labels = $grafikPenjualan->pluck('hari')->map(fn($h) => str_pad($h, 2, '0', STR_PAD_LEFT))->toArray();
        $dataGrafik = $grafikPenjualan->pluck('total')->toArray();

        // Penjualan Hari Ini
        $penjualanHariIni = DB::table('t_penjualan')
            ->whereDate('tanggal_jual', Carbon::today())
            ->select(
                DB::raw('COUNT(*) as total_transaksi'),
                DB::raw('SUM(total_jual) as total_penjualan')
            )->first();

        // Stok Minimum Bahan
        $stokMinBahan = DB::table('t_kartupersbahan')
            ->select(
                't_kartupersbahan.kode_bahan',
                't_bahan.nama_bahan',
                DB::raw('SUM(masuk) - SUM(keluar) as stok')
            )
            ->join('t_bahan', 't_bahan.kode_bahan', '=', 't_kartupersbahan.kode_bahan')
            ->groupBy('t_kartupersbahan.kode_bahan', 't_bahan.nama_bahan')
            ->havingRaw('stok <= 10')
            ->get();

        // Stok Minimum Produk
        $stokMinProduk = DB::table('t_kartupersproduk')
            ->select(
                't_kartupersproduk.kode_produk',
                't_produk.nama_produk',
                DB::raw('SUM(masuk) - SUM(keluar) as stok')
            )
            ->join('t_produk', 't_produk.kode_produk', '=', 't_kartupersproduk.kode_produk')
            ->groupBy('t_kartupersproduk.kode_produk', 't_produk.nama_produk')
            ->havingRaw('stok <= 10')
            ->get();

        // Penjualan Per Lokasi
        $penjualanPerLokasi = DB::table('t_penjualan')
            ->select('t_lokasi.nama_lokasi', DB::raw('SUM(total_jual) as total'), DB::raw('COUNT(*) as jumlah_transaksi'))
            ->join('t_lokasi', 't_penjualan.lokasi', '=', 't_lokasi.kode_lokasi')
            ->whereMonth('tanggal_jual', $bulan)
            ->whereYear('tanggal_jual', $tahun)
            ->groupBy('t_lokasi.nama_lokasi')
            ->get();

        // Laporan Singkat Stok Bahan
        $stokBahan = DB::table('t_kartupersbahan')
            ->select(
                't_kartupersbahan.kode_bahan',
                't_bahan.nama_bahan',
                DB::raw('SUM(masuk) - SUM(keluar) as stok')
            )
            ->join('t_bahan', 't_bahan.kode_bahan', '=', 't_kartupersbahan.kode_bahan')
            ->groupBy('t_kartupersbahan.kode_bahan', 't_bahan.nama_bahan')
            ->get();

        // Laporan Singkat Stok Produk
        $stokProduk = DB::table('t_kartupersproduk')
            ->select(
                't_kartupersproduk.kode_produk',
                't_produk.nama_produk',
                DB::raw('SUM(masuk) - SUM(keluar) as stok')
            )
            ->join('t_produk', 't_produk.kode_produk', '=', 't_kartupersproduk.kode_produk')
            ->groupBy('t_kartupersproduk.kode_produk', 't_produk.nama_produk')
            ->get();

        $orderMenunggu = [];
            if(auth()->user() && auth()->user()->role == 'admin') {
                $orderMenunggu = \App\Models\OrderBeli::whereNull('status')->get();
            };

        return view('welcome', compact(
            'kadaluarsa',
            'hampir',
            'kadaluarsaProduk',
            'hampirProduk',
            'grouped',
            'groupedProduk',
            'penjualanBulanIni',
            'labels',
            'dataGrafik',
            'penjualanHariIni',
            'stokMinBahan',
            'stokMinProduk',
            'penjualanPerLokasi',
            'stokBahan',
            'stokProduk',
            'orderMenunggu'
        ));
    }

    public function kadaluarsa()
    {
        // Bahan
        $reminder = DB::table('t_kartupersbahan')
            ->select(
                't_kartupersbahan.kode_bahan',
                't_bahan.nama_bahan',
                't_kartupersbahan.tanggal_exp',
                DB::raw('SUM(masuk) - SUM(keluar) as stok')
            )
            ->join('t_bahan', 't_bahan.kode_bahan', '=', 't_kartupersbahan.kode_bahan')
            ->whereNotNull('t_kartupersbahan.tanggal_exp')
            ->groupBy('t_kartupersbahan.kode_bahan', 't_kartupersbahan.tanggal_exp', 't_bahan.nama_bahan')
            ->havingRaw('stok > 0')
            ->get();

        $kadaluarsa = collect($reminder)->filter(fn($r) => \Carbon\Carbon::parse($r->tanggal_exp)->isPast());
        $hampir = collect($reminder)->filter(function ($r) {
            $diff = \Carbon\Carbon::today()->diffInDays(\Carbon\Carbon::parse($r->tanggal_exp), false);
            return $diff > 0 && $diff <= 6;
        });
        $grouped = $kadaluarsa->groupBy('nama_bahan');

        // Produk
        $reminderProduk = DB::table('t_kartupersproduk')
            ->select(
                't_kartupersproduk.kode_produk',
                't_produk.nama_produk',
                't_kartupersproduk.tanggal_exp',
                't_kartupersproduk.lokasi',
                DB::raw('SUM(masuk) - SUM(keluar) as stok')
            )
            ->join('t_produk', 't_produk.kode_produk', '=', 't_kartupersproduk.kode_produk')
            ->whereNotNull('t_kartupersproduk.tanggal_exp')
            ->groupBy('t_kartupersproduk.kode_produk', 't_kartupersproduk.tanggal_exp', 't_produk.nama_produk', 't_kartupersproduk.lokasi')
            ->havingRaw('stok > 0')
            ->get();

        $kadaluarsaProduk = collect($reminderProduk)
            ->filter(fn($r) => \Carbon\Carbon::parse($r->tanggal_exp)->isPast() && $r->lokasi === 'Gudang');
        $hampirProduk = collect($reminderProduk)
            ->filter(function ($r) {
                $diff = \Carbon\Carbon::today()->diffInDays(\Carbon\Carbon::parse($r->tanggal_exp), false);
                return $diff > 0 && $diff <= 6 && $r->lokasi === 'Gudang';
            });
        $groupedProduk = $kadaluarsaProduk->groupBy('lokasi');

        return view('penyesuaian.exp', compact(
            'kadaluarsa', 'hampir', 'grouped',
            'kadaluarsaProduk', 'hampirProduk', 'groupedProduk'
        ));
    }
}