<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\PenyesuaianBarang;
use Carbon\Carbon;

class PenyesuaianBarangController extends Controller
{
    public function index()
    {
        $today = Carbon::today()->toDateString();

        $bahanKadaluarsa = DB::table('t_kartupersbahan')
            ->select('kode_bahan', 'harga', 'tanggal_exp', DB::raw('SUM(masuk) as total_masuk'), DB::raw('SUM(keluar) as total_keluar'))
            ->where('tanggal_exp', '<', $today)
            ->groupBy('kode_bahan', 'harga', 'tanggal_exp')
            ->havingRaw('SUM(masuk) - SUM(keluar) > 0')
            ->get();

        foreach ($bahanKadaluarsa as $item) {
            $item->nama_bahan = DB::table('t_bahan')->where('kode_bahan', $item->kode_bahan)->value('nama_bahan');
            $item->satuan = DB::table('t_bahan')->where('kode_bahan', $item->kode_bahan)->value('satuan'); // Tambah baris ini
            $item->stok = $item->total_masuk - $item->total_keluar;
        }

        $produkKadaluarsa = DB::table('t_kartupersproduk')
            ->select('kode_produk', 'harga', 'tanggal_exp', DB::raw('SUM(masuk) as total_masuk'), DB::raw('SUM(keluar) as total_keluar'))
            ->where('tanggal_exp', '<', $today)
            ->groupBy('kode_produk', 'harga', 'tanggal_exp')
            ->havingRaw('SUM(masuk) - SUM(keluar) > 0')
            ->get();

        foreach ($produkKadaluarsa as $item) {
            $item->nama_produk = DB::table('t_produk')->where('kode_produk', $item->kode_produk)->value('nama_produk');
            $item->satuan = DB::table('t_produk')->where('kode_produk', $item->kode_produk)->value('satuan'); // Tambah baris ini
            $item->stok = $item->total_masuk - $item->total_keluar;
        }

        return view('penyesuaianbarang.exp', compact('bahanKadaluarsa', 'produkKadaluarsa'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.tipe_item' => 'required|in:bahan,produk',
            'items.*.kode_item' => 'required',
            'items.*.jumlah' => 'required|numeric|min:1',
            'items.*.harga_satuan' => 'required|numeric|min:0',
            'items.*.alasan' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $no_penyesuaian = 'PNY' . now()->format('YmdHis') . Str::random(3);

            // Simpan header penyesuaian
            PenyesuaianBarang::create([
                'no_penyesuaian' => $no_penyesuaian,
                'tanggal' => $request->tanggal,
                'keterangan' => $request->keterangan,
            ]);

            // Variabel akumulasi nilai
            $totalKerugian = 0;
            $kreditBahan = 0;
            $kreditProduk = 0;

            // Simpan detail & kartu persediaan
            foreach ($request->items as $item) {
                $nilai = $item['jumlah'] * $item['harga_satuan'];
                $totalKerugian += $nilai;

                // Akumulasi untuk jurnal
                if ($item['tipe_item'] === 'bahan') {
                    $kreditBahan += $nilai;
                } else {
                    $kreditProduk += $nilai;
                }

                DB::table('t_penyesuaian_detail')->insert([
                    'no_penyesuaian' => $no_penyesuaian,
                    'tipe_item' => $item['tipe_item'],
                    'kode_item' => $item['kode_item'],
                    'jumlah' => $item['jumlah'],
                    'harga_satuan' => $item['harga_satuan'],
                    'alasan' => $item['alasan'] ?? null,
                ]);

                if ($item['tipe_item'] === 'bahan') {
                    DB::table('t_kartupersbahan')->insert([
                        'no_transaksi' => $no_penyesuaian,
                        'tanggal' => $request->tanggal,
                        'tanggal_exp' => $item['tanggal_exp'],
                        'kode_bahan' => $item['kode_item'],
                        'masuk' => 0,
                        'keluar' => $item['jumlah'],
                        'harga' => $item['harga_satuan'],
                        'satuan' => $item['satuan'] ?? null,
                        'keterangan' => $item['alasan'] ?? 'Penyesuaian Exp',
                    ]);
                } else {
                    DB::table('t_kartupersproduk')->insert([
                        'no_transaksi' => $no_penyesuaian,
                        'tanggal' => $request->tanggal,
                        'tanggal_exp' => $item['tanggal_exp'],
                        'kode_produk' => $item['kode_item'],
                        'masuk' => 0,
                        'keluar' => $item['jumlah'],
                        'harga' => $item['harga_satuan'],
                        'satuan' => $item['satuan'] ?? null,
                        'keterangan' => $item['alasan'] ?? 'Penyesuaian Exp',
                    ]);
                }
            }

            // ============ BUAT JURNAL UMUM ============
            $id_jurnal = DB::table('t_jurnal_umum')->insertGetId([
                'tanggal' => $request->tanggal,
                'keterangan' => 'Penyesuaian barang kadaluarsa',
                'nomor_bukti' => $no_penyesuaian,
            ]);

            $jurnal = [];

            // Beban kerugian persediaan (debit)
            $jurnal[] = [
                'id_jurnal' => $id_jurnal,
                'kode_akun' => 511,
                'debit' => $totalKerugian,
                'kredit' => 0,
            ];

            if ($kreditBahan > 0) {
                $jurnal[] = [
                    'id_jurnal' => $id_jurnal,
                    'kode_akun' => 103,
                    'debit' => 0,
                    'kredit' => $kreditBahan,
                ];
            }

            if ($kreditProduk > 0) {
                $jurnal[] = [
                    'id_jurnal' => $id_jurnal,
                    'kode_akun' => 105,
                    'debit' => 0,
                    'kredit' => $kreditProduk,
                ];
            }

            DB::table('t_jurnal_detail')->insert($jurnal);

            DB::commit();
            return redirect()->route('welcome')->with('success', 'Penyesuaian dan jurnal berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menyimpan: ' . $e->getMessage()]);
        }
    }
}