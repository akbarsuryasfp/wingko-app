<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JadwalProduksi;
use App\Models\JadwalProduksiDetail;
use App\Models\PermintaanProduksi;
use Illuminate\Support\Facades\DB;

class JadwalProduksiController extends Controller
{
    public function create(Request $request)
    {
        $permintaan = PermintaanProduksi::with('details.produk')->where('status', 'Menunggu')->get();

        // Ambil pesanan penjualan yang belum dijadwalkan
        $pesanan = \App\Models\PesananPenjualan::with('details.produk', 'pelanggan')->get();

        $selectedPermintaan = null;
        $selectedPesanan = null;

        if ($request->has('permintaan')) {
            $selectedPermintaan = $permintaan->where('kode_permintaan_produksi', $request->permintaan)->first();
        }
        if ($request->has('pesanan')) {
            $selectedPesanan = $pesanan->where('kode_pesanan', $request->pesanan)->first();
        }

        return view('jadwal.create', compact('permintaan', 'pesanan', 'selectedPermintaan', 'selectedPesanan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal_jadwal' => 'required|date',
            'keterangan' => 'nullable|string',
            'produk.*.kode_produk' => 'required|exists:t_produk,kode_produk',
            'produk.*.jumlah' => 'required|integer|min:1',
            'produk.*.kode_sumber' => 'required',
        ]);

        DB::transaction(function () use ($request) {
            $kodeJadwal = 'JD' . now()->format('YmdHis');

            JadwalProduksi::create([
                'kode_jadwal' => $kodeJadwal,
                'tanggal_jadwal' => $request->tanggal_jadwal,
                'keterangan' => $request->keterangan,
            ]);

            $kodeSumberDiproses = [];

            foreach ($request->produk as $i => $p) {
                JadwalProduksiDetail::create([
                    'kode_jadwal_detail' => $kodeJadwal . '-' . str_pad($i + 1, 2, '0', STR_PAD_LEFT),
                    'kode_jadwal' => $kodeJadwal,
                    'kode_produk' => $p['kode_produk'],
                    'jumlah' => $p['jumlah'],
                    'sumber_data' => 'permintaan',
                    'kode_sumber' => $p['kode_sumber'],
                ]);
                $kodeSumberDiproses[] = $p['kode_sumber'];
            }

            // Update status permintaan produksi terkait ke 'Diproses'
            PermintaanProduksi::whereIn('kode_permintaan_produksi', $kodeSumberDiproses)
                ->update(['status' => 'Diproses']);
        });

        return redirect()->route('jadwal.index')->with('success', 'Jadwal produksi berhasil disimpan!');
    }
    
    public function index()
    {
        $jadwal = JadwalProduksi::with('details.produk.resep.details.bahan')->orderBy('tanggal_jadwal', 'desc')->get();

        foreach ($jadwal as $j) {
            $kebutuhan = [];
            foreach ($j->details as $detail) {
                $produk = $detail->produk;
                $jumlah = $detail->jumlah;
                if ($produk && $produk->resep) {
                    foreach ($produk->resep->details as $rdetail) {
                        $kode_bahan = $rdetail->kode_bahan;
                        $total = $jumlah * $rdetail->jumlah_kebutuhan;
                        if (!isset($kebutuhan[$kode_bahan])) {
                            $kebutuhan[$kode_bahan] = [
                                'jumlah' => 0,
                                'satuan' => $rdetail->satuan,
                            ];
                        }
                        $kebutuhan[$kode_bahan]['jumlah'] += $total;
                    }
                }
            }
            // Cek stok bahan
            $adaKurang = false;
            foreach ($kebutuhan as $kode_bahan => $b) {
                $stok = \DB::table('t_kartupersbahan')
                    ->where('kode_bahan', $kode_bahan)
                    ->selectRaw('COALESCE(SUM(masuk),0) - COALESCE(SUM(keluar),0) as saldo')
                    ->value('saldo') ?? 0;
                if ($stok < $b['jumlah']) {
                    $adaKurang = true;
                    break;
                }
            }
            $j->ada_bahan_kurang = $adaKurang;
        }

        return view('jadwal.index', compact('jadwal'));
    }

    public function show($kode)
{
    $jadwal = \App\Models\JadwalProduksi::with('details.produk.resep.details.bahan')->findOrFail($kode);

    $kebutuhan = [];

    foreach ($jadwal->details as $detail) {
        $produk = $detail->produk;
        $jumlah = $detail->jumlah;

        if ($produk && $produk->resep) {
            foreach ($produk->resep->details as $rdetail) {
                $kode_bahan = $rdetail->kode_bahan;
                $total = $jumlah * $rdetail->jumlah_kebutuhan;

                if (!isset($kebutuhan[$kode_bahan])) {
                    $kebutuhan[$kode_bahan] = [
                        'nama_bahan' => $rdetail->bahan->nama_bahan ?? $kode_bahan,
                        'jumlah' => 0,
                        'satuan' => $rdetail->satuan,
                    ];
                }

                $kebutuhan[$kode_bahan]['jumlah'] += $total;
            }
        }
    }

    // Tambahkan pengecekan stok bahan dari kartu stok (t_kartupersbahan)
    foreach ($kebutuhan as $kode_bahan => &$b) {
        $stok = \DB::table('t_kartupersbahan')
            ->where('kode_bahan', $kode_bahan)
            ->selectRaw('COALESCE(SUM(masuk),0) - COALESCE(SUM(keluar),0) as saldo')
            ->value('saldo') ?? 0;
        $b['stok'] = $stok;
        $b['status'] = $stok >= $b['jumlah'] ? 'Cukup' : 'Kurang';
    }
    unset($b);

    return view('jadwal.show', compact('jadwal', 'kebutuhan'));
}

public function destroy($kode)
{
    $jadwal = \App\Models\JadwalProduksi::findOrFail($kode);
    // Hapus detail terlebih dahulu jika ada relasi
    $jadwal->details()->delete();
    $jadwal->delete();

    return redirect()->route('jadwal.index')->with('success', 'Jadwal produksi berhasil dihapus!');
}
}
