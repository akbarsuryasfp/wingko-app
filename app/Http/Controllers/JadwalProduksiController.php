<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JadwalProduksi;
use App\Models\JadwalProduksiDetail;
use App\Models\PermintaanProduksi;
use Illuminate\Support\Facades\DB;

class JadwalProduksiController extends Controller
{
    public function create()
    {
        $permintaan = PermintaanProduksi::with('details.produk')->where('status', 'Diproses')->get();
        return view('jadwal.create', compact('permintaan'));
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

            foreach ($request->produk as $i => $p) {
                JadwalProduksiDetail::create([
                    'kode_jadwal_detail' => $kodeJadwal . '-' . str_pad($i + 1, 2, '0', STR_PAD_LEFT),
                    'kode_jadwal' => $kodeJadwal,
                    'kode_produk' => $p['kode_produk'],
                    'jumlah' => $p['jumlah'],
                    'sumber_data' => 'permintaan',
                    'kode_sumber' => $p['kode_sumber'],
                ]);
            }
        });

        return redirect()->route('jadwal.index')->with('success', 'Jadwal produksi berhasil disimpan!');
    }
    
    public function index()
    {
        $jadwal = JadwalProduksi::with('details.produk')->orderBy('tanggal_jadwal', 'desc')->get();
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

    // Tambahkan pengecekan stok bahan dari tabel t_bahan
    foreach ($kebutuhan as $kode_bahan => &$b) {
        $stok = \App\Models\Bahan::where('kode_bahan', $kode_bahan)->value('stok') ?? 0;
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
