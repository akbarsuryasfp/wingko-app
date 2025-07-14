<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produksi;
use App\Models\ProduksiDetail;
use App\Models\JadwalProduksi;
use Illuminate\Support\Facades\DB;

class ProduksiController extends Controller
{
    public function create(Request $request)
    {
        $jadwal = \App\Models\JadwalProduksi::with('details.produk')->get();
        $jadwalTerpilih = null;
        if ($request->has('jadwal')) {
            $jadwalTerpilih = $jadwal->where('kode_jadwal', $request->jadwal)->first();
        }
        return view('produksi.create', compact('jadwal', 'jadwalTerpilih'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal_produksi' => 'required|date',
            'produk.*.kode_produk' => 'required',
            'produk.*.jumlah_unit' => 'required|integer|min:1',
            'produk.*.tanggal_expired' => 'required|date',
        ]);

        DB::transaction(function () use ($request) {
            $kode = 'PRD' . now()->format('YmdHis');
            $keterangan = 'Produksi tanggal ' . date('d/m/Y', strtotime($request->tanggal_produksi));

            Produksi::create([
                'no_produksi' => $kode,
                'tanggal_produksi' => $request->tanggal_produksi,
                'keterangan' => $keterangan,
            ]);

            // Simpan detail produk dulu
            foreach ($request->produk as $i => $produk) {
                $no_detail_produksi = $kode . '-' . ($i + 1);
                $kode_produk = $produk['kode_produk'];
                $jumlah_unit = $produk['jumlah_unit'];
                $tanggal_expired = $produk['tanggal_expired'];
                $harga_per_unit = $produk['harga_per_unit']; // Ambil harga dari input

            $produkData = DB::table('t_produk')
                ->where('kode_produk', $kode_produk)
                ->first();

            if (!$produkData) {
                throw new \Exception("Produk dengan kode {$kode_produk} tidak ditemukan");
            }

                DB::table('t_produksi_detail')->insert([
                    'no_detail_produksi' => $no_detail_produksi,
                    'no_produksi' => $kode,
                    'kode_produk' => $kode_produk,
                    'jumlah_unit' => $jumlah_unit,
                    'tanggal_expired' => $tanggal_expired,
                    //'harga_per_unit' => $harga_per_unit, // Pastikan kolom ini ada
                ]);

                // Tambahkan ke kartu stok produk (produk jadi masuk)
                DB::table('t_kartupersproduk')->insert([
                    'no_transaksi' => $no_detail_produksi,
                    'tanggal' => $request->tanggal_produksi,
                    'kode_produk' => $kode_produk,
                    'masuk' => $jumlah_unit,
                    'keluar' => 0,
                    'hpp' => null,
                    'satuan' => $produkData->satuan,
                    'keterangan' => 'Hasil produksi',
                    'tanggal_exp' => $tanggal_expired,
                    'lokasi' => 'Gudang',
                ]);
            }

            // Ambil seluruh stok FIFO bahan SEKALI SAJA di awal
            $stokFIFO = [];
            $kode_bahan_list = [];
            foreach ($request->produk as $produk) {
                $kode_produk = $produk['kode_produk'];
                $resep = DB::table('t_resep')->where('kode_produk', $kode_produk)->first();
                if (!$resep) continue;
                $resepDetails = DB::table('t_resep_detail')->where('kode_resep', $resep->kode_resep)->get();
                foreach ($resepDetails as $rd) {
                    $kode_bahan_list[$rd->kode_bahan] = true;
                }
            }
            foreach (array_keys($kode_bahan_list) as $kode_bahan) {
                $stokFIFO[$kode_bahan] = DB::table('t_kartupersbahan')
                    ->where('kode_bahan', $kode_bahan)
                    ->whereRaw('(masuk - keluar) > 0')
                    ->orderBy('tanggal')
                    ->orderBy('harga')
                    ->get()
                    ->map(function($row) {
                        $row->sisa = ($row->masuk ?? 0) - ($row->keluar ?? 0);
                        return $row;
                    })->toArray();
            }

            // Setelah semua detail produk tersimpan, baru proses bahan per produk
            foreach ($request->produk as $i => $produk) {
                $no_detail_produksi = $kode . '-' . ($i + 1);
                $kode_produk = $produk['kode_produk'];
                $jumlah_unit = $produk['jumlah_unit'];

                $resep = DB::table('t_resep')->where('kode_produk', $kode_produk)->first();
                if (!$resep) continue;
                $resepDetails = DB::table('t_resep_detail')->where('kode_resep', $resep->kode_resep)->get();

                foreach ($resepDetails as $rd) {
                    $kode_bahan = $rd->kode_bahan;
                    $total = $jumlah_unit * $rd->jumlah_kebutuhan; // Biarkan hasil float/desimal
                    $sisa = $total;

                    // Ambil dari array stokFIFO, bukan query ulang!
                    foreach ($stokFIFO[$kode_bahan] as &$batch) {
                        if ($batch->sisa <= 0) continue;
                        $pakai = min($batch->sisa, $sisa);

                        // Catat mutasi keluar di kartu stok
                        DB::table('t_kartupersbahan')->insert([
                            'no_transaksi' => $kode,
                            'tanggal' => $request->tanggal_produksi,
                            'kode_bahan' => $kode_bahan,
                            'masuk' => 0,
                            'keluar' => $pakai, // $pakai bisa float/desimal
                            'harga' => $batch->harga,
                            'satuan' => $rd->satuan,
                            'keterangan' => 'Pemakaian produksi ' . $kode,
                        ]);

                        // Catat detail pemakaian bahan per batch dan per produk
                        DB::table('t_produksi_bahan')->insert([
                            'no_detail_produksi' => $no_detail_produksi,
                            'kode_bahan' => $kode_bahan,
                            'jumlah' => $pakai, // $pakai bisa float/desimal
                            'harga' => $batch->harga,
                            'no_terima_bahan' => $batch->no_transaksi,
                            'satuan' => $rd->satuan,
                            'keterangan' => 'FIFO produksi ' . $kode,
                        ]);

                        $batch->sisa -= $pakai;
                        $sisa -= $pakai;
                        if ($sisa <= 0) break;
                    }
                    unset($batch);

                    if ($sisa > 0) {
                        throw new \Exception('Stok bahan ' . $kode_bahan . ' tidak cukup untuk produksi!');
                    }
                }
            }
        });

        return redirect()->route('produksi.index')->with('success', 'Data produksi berhasil disimpan!');
    }
    
    
    public function index()
    {
        $produksi = Produksi::with('details.produk')->orderBy('tanggal_produksi', 'desc')->get();
        return view('produksi.index', compact('produksi'));
    }
    
    public function show($no_produksi)
    {
        $produksi = \App\Models\Produksi::with(['details.produk'])->findOrFail($no_produksi);

        // Ambil semua no_detail_produksi dari produksi ini
        $no_detail_list = DB::table('t_produksi_detail')
            ->where('no_produksi', $no_produksi)
            ->pluck('no_detail_produksi');

        // Ambil data penggunaan bahan dari tabel mutasi produksi (per produk)
        $bahanPakai = DB::table('t_produksi_bahan')
            ->whereIn('no_detail_produksi', $no_detail_list)
            ->join('t_bahan', 't_bahan.kode_bahan', '=', 't_produksi_bahan.kode_bahan')
            ->select(
                't_bahan.nama_bahan',
                't_produksi_bahan.jumlah',
                't_produksi_bahan.satuan',
                't_produksi_bahan.harga',
                DB::raw('(t_produksi_bahan.jumlah * t_produksi_bahan.harga) as total_harga'),
                't_produksi_bahan.no_terima_bahan as batch',
                't_produksi_bahan.keterangan'
            )
            ->get();

        // Ambil semua detail produksi untuk produksi ini
        $details = DB::table('t_produksi_detail')
            ->where('no_produksi', $no_produksi)
            ->get();

        // Ambil bahan pakai per produk
        $bahanPakaiPerProduk = [];
        foreach ($details as $detail) {
            $bahanPakaiPerProduk[] = [
                'nama_produk' => DB::table('t_produk')->where('kode_produk', $detail->kode_produk)->value('nama_produk'),
                'detail' => DB::table('t_produksi_bahan')
                    ->where('no_detail_produksi', $detail->no_detail_produksi)
                    ->join('t_bahan', 't_bahan.kode_bahan', '=', 't_produksi_bahan.kode_bahan')
                    ->select(
                        't_bahan.nama_bahan',
                        't_produksi_bahan.jumlah',
                        't_produksi_bahan.satuan',
                        't_produksi_bahan.harga',
                        DB::raw('(t_produksi_bahan.jumlah * t_produksi_bahan.harga) as total_harga'),
                        't_produksi_bahan.no_terima_bahan as batch',
                        't_produksi_bahan.keterangan'
                    )
                    ->get()
            ];
        }

        $totalBiayaBahan = $bahanPakai->sum('total_harga');

        return view('produksi.show', compact('produksi', 'bahanPakai', 'totalBiayaBahan', 'bahanPakaiPerProduk'));
    }
    
    public function destroy($no_produksi)
    {
        DB::transaction(function() use ($no_produksi) {
            // Ambil semua no_detail_produksi dari produksi ini
            $no_detail_list = DB::table('t_produksi_detail')
                ->where('no_produksi', $no_produksi)
                ->pluck('no_detail_produksi');

            // Hapus data HPP terkait
            DB::table('t_hpp_per_produk')->whereIn('no_detail_produksi', $no_detail_list)->delete();
            DB::table('t_hpp_bahan_baku_detail')->whereIn('no_detail_produksi', $no_detail_list)->delete();
            DB::table('t_hpp_overhead_detail')->whereIn('no_detail_produksi', $no_detail_list)->delete();
            DB::table('t_hpp_tenaga_kerja_detail')->whereIn('no_detail_produksi', $no_detail_list)->delete();

            // Hapus detail bahan yang sudah dipakai (FIFO)
            $bahanPakai = DB::table('t_produksi_bahan')
                ->whereIn('no_detail_produksi', $no_detail_list)
                ->get();
            foreach ($bahanPakai as $b) {
                DB::table('t_kartupersbahan')
                    ->where('no_transaksi', $no_produksi)
                    ->where('kode_bahan', $b->kode_bahan)
                    ->where('keluar', $b->jumlah)
                    ->where('harga', $b->harga)
                    ->delete();
            }
            DB::table('t_produksi_bahan')->whereIn('no_detail_produksi', $no_detail_list)->delete();

            // Hapus detail produk yang sudah masuk stok
            $produkDetail = DB::table('t_produksi_detail')->where('no_produksi', $no_produksi)->get();
            foreach ($produkDetail as $d) {
                DB::table('t_kartupersproduk')
                    ->where('no_transaksi', $no_produksi)
                    ->where('kode_produk', $d->kode_produk)
                    ->where('masuk', $d->jumlah_unit)
                    ->delete();
            }
            DB::table('t_produksi_detail')->where('no_produksi', $no_produksi)->delete();

            // Hapus data produksi utama
            DB::table('t_produksi')->where('no_produksi', $no_produksi)->delete();
        });

        return redirect()->route('produksi.index')->with('success', 'Produksi dan seluruh data terkait berhasil dihapus.');
    }
}
