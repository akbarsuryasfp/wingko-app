<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\PenyesuaianBarang;
use Carbon\Carbon;
use App\Helpers\JurnalHelper;

class PenyesuaianBarangController extends Controller
{
    public function index()
    {
        $today = \Carbon\Carbon::today()->toDateString();

        $bahanKadaluarsa = DB::table('t_kartupersbahan')
            ->select(
                't_kartupersbahan.kode_bahan',
                't_bahan.nama_bahan',
                't_kartupersbahan.tanggal_exp',
                't_kartupersbahan.harga',
                't_bahan.satuan',
                DB::raw('SUM(masuk) as total_masuk'),
                DB::raw('SUM(keluar) as total_keluar')
            )
            ->join('t_bahan', 't_bahan.kode_bahan', '=', 't_kartupersbahan.kode_bahan')
            ->where('t_kartupersbahan.tanggal_exp', '<=', $today)
            ->groupBy(
                't_kartupersbahan.kode_bahan',
                't_kartupersbahan.tanggal_exp',
                't_kartupersbahan.harga',
                't_bahan.nama_bahan',
                't_bahan.satuan'
            )
            ->havingRaw('SUM(masuk) - SUM(keluar) > 0')
            ->get()
            ->map(function($item) {
                $item->stok = $item->total_masuk - $item->total_keluar;
                return $item;
            });

        $produkKadaluarsa = DB::table('t_kartupersproduk')
            ->select(
                't_kartupersproduk.kode_produk',
                't_produk.nama_produk',
                't_kartupersproduk.tanggal_exp',
                't_kartupersproduk.hpp', // GANTI 'harga' MENJADI 'hpp'
                't_produk.satuan',
                DB::raw('SUM(masuk) as total_masuk'),
                DB::raw('SUM(keluar) as total_keluar')
            )
            ->join('t_produk', 't_produk.kode_produk', '=', 't_kartupersproduk.kode_produk')
            ->where('t_kartupersproduk.tanggal_exp', '<=', $today)
            ->groupBy(
                't_kartupersproduk.kode_produk',
                't_kartupersproduk.tanggal_exp',
                't_kartupersproduk.hpp', // GANTI 'harga' MENJADI 'hpp'
                't_produk.nama_produk',
                't_produk.satuan'
            )
            ->havingRaw('SUM(masuk) - SUM(keluar) > 0')
            ->get()
            ->map(function($item) {
                $item->stok = $item->total_masuk - $item->total_keluar;
                $item->harga = $item->hpp; // AGAR DI BLADE TETAP BISA $item->harga
                return $item;
            });

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
                    'tipe_item'      => $item['tipe_item'],
                    'kode_item'      => $item['kode_item'],
                    'jumlah'         => $item['jumlah'],
                    'harga_satuan'   => $item['harga_satuan'],
                    'total_nilai'    => $nilai, // <-- tambahkan baris ini
                    'alasan'         => $item['alasan'] ?? null,
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
                        'hpp' => $item['harga_satuan'], // GANTI 'harga' MENJADI 'hpp'
                        'satuan' => $item['satuan'] ?? null,
                        'keterangan' => $item['alasan'] ?? 'Penyesuaian Exp',
                    ]);
                }
            }

            // ============ BUAT JURNAL UMUM ============
            $no_jurnal = JurnalHelper::generateNoJurnal();

            DB::table('t_jurnal_umum')->insert([
                'no_jurnal'   => $no_jurnal,
                'tanggal'     => $request->tanggal,
                'keterangan'  => 'Penyesuaian persediaan',
                'nomor_bukti' => $no_penyesuaian,
            ]);

            $jurnal = [];

            // Beban kerugian persediaan (debit)
            $jurnal[] = [
                'no_jurnal_detail' => JurnalHelper::generateNoJurnalDetail(),
                'no_jurnal'        => $no_jurnal,
                'kode_akun'        => '511', // contoh kode akun kerugian
                'debit'            => $totalKerugian,
                'kredit'           => 0,
            ];

            if ($kreditBahan > 0) {
                $jurnal[] = [
                    'no_jurnal_detail' => JurnalHelper::generateNoJurnalDetail(),
                    'no_jurnal' => $no_jurnal,
                    'kode_akun' => 103,
                    'debit' => 0,
                    'kredit' => $kreditBahan,
                ];
            }

            if ($kreditProduk > 0) {
                $jurnal[] = [
                    'no_jurnal_detail' => JurnalHelper::generateNoJurnalDetail(),
                    'no_jurnal' => $no_jurnal,
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