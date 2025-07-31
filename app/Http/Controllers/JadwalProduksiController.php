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

        // Ambil semua no_pesanan yang sudah dipakai di jadwal produksi
        $pesananDipakai = JadwalProduksiDetail::where('sumber_data', 'pesanan')
            ->pluck('no_sumber')
            ->unique()
            ->toArray();

        // Filter pesanan yang belum dipakai
        $pesanan = \App\Models\PesananPenjualan::with('details.produk', 'pelanggan')
            ->whereNotIn('no_pesanan', $pesananDipakai)
            ->get();

        $setorKonsinyasi = \DB::table('t_consignee_setor')
            ->join('t_consignee', 't_consignee.kode_consignee', '=', 't_consignee_setor.kode_consignee')
            ->join('t_produk', 't_produk.kode_produk', '=', 't_consignee_setor.kode_produk')
            ->select('t_consignee_setor.*', 't_consignee.nama_consignee', 't_produk.nama_produk')
            ->get();

        $selectedPermintaan = null;
        $selectedPesanan = null;

        // Tanggal jadwal default: hari ini
        $tanggalJadwal = now()->format('Y-m-d');
        $hari = [
            'Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'
        ];
        $bulan = [
            'Januari','Februari','Maret','April','Mei','Juni',
            'Juli','Agustus','September','Oktober','November','Desember'
        ];
        $tgl = now();
        $keterangan = 'Jadwal Produksi Hari ' . $hari[$tgl->dayOfWeek] . ', ' . $tgl->day . ' ' . $bulan[$tgl->month - 1] . ' ' . $tgl->year;

        return view('jadwal.create', compact(
            'permintaan', 'pesanan', 'setorKonsinyasi',
            'selectedPermintaan', 'selectedPesanan',
            'tanggalJadwal', 'keterangan'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal_jadwal' => 'required|date',
            'keterangan' => 'nullable|string',
            'produk.*.kode_produk' => 'required|exists:t_produk,kode_produk',
            'produk.*.jumlah' => 'required|integer|min:1',
            'produk.*.no_sumber' => 'required',
            'produk.*.tipe_sumber' => 'required', // tambahkan validasi tipe_sumber
        ]);

        DB::transaction(function () use ($request) {
            $kodeJadwal = 'JD' . now()->format('YmdHis');

            JadwalProduksi::create([
                'no_jadwal' => $kodeJadwal,
                'tanggal_jadwal' => $request->tanggal_jadwal,
                'keterangan' => $request->keterangan,
            ]);

            $kodeSumberDiproses = [];

            foreach ($request->produk as $i => $p) {
                JadwalProduksiDetail::create([
                    'no_jadwal_detail' => $kodeJadwal . '-' . str_pad($i + 1, 2, '0', STR_PAD_LEFT),
                    'no_jadwal' => $kodeJadwal,
                    'kode_produk' => $p['kode_produk'],
                    'jumlah' => $p['jumlah'],
                    'sumber_data' => $p['tipe_sumber'], // gunakan tipe_sumber dari form
                    'no_sumber' => $p['no_sumber'],
                ]);
                $kodeSumberDiproses[] = $p['no_sumber'];
            }

            // Update status permintaan produksi terkait ke 'Diproses'
            PermintaanProduksi::whereIn('no_permintaan_produksi', $kodeSumberDiproses)
                ->update(['status' => 'Diproses']);
        });

        return redirect()->route('jadwal.index')->with('success', 'Jadwal produksi berhasil disimpan!');
    }
    
    public function index()
    {
        // Ambil semua jadwal
        $jadwal = JadwalProduksi::with('details.produk')->get();

        foreach ($jadwal as $j) {
            // Cek apakah sudah diproses produksi
            $j->sudah_diproses = \DB::table('t_produksi')->where('no_jadwal', $j->no_jadwal)->exists();

            // Hanya cek bahan jika BELUM diproses
            if (!$j->sudah_diproses) {
                $adaBahanKurang = false;
                $kebutuhan = [];
                foreach ($j->details as $detail) {
                    $produk = $detail->produk;
                    $jumlah = $detail->jumlah;
                    if ($produk && $produk->resep) {
                        foreach ($produk->resep->details as $rdetail) {
                            $kode_bahan = $rdetail->kode_bahan;
                            $total = $jumlah * $rdetail->jumlah_kebutuhan;
                            if (!isset($kebutuhan[$kode_bahan])) {
                                $kebutuhan[$kode_bahan] = 0;
                            }
                            $kebutuhan[$kode_bahan] += $total;
                        }
                    }
                }
                foreach ($kebutuhan as $kode_bahan => $jumlah) {
                    $stok = \DB::table('t_kartupersbahan')
                        ->where('kode_bahan', $kode_bahan)
                        ->selectRaw('COALESCE(SUM(masuk),0) - COALESCE(SUM(keluar),0) as saldo')
                        ->value('saldo') ?? 0;
                    if ($stok < $jumlah) {
                        $adaBahanKurang = true;
                        break;
                    }
                }
                $j->ada_bahan_kurang = $adaBahanKurang;
            } else {
                // Jika sudah diproses, status bahan tidak dicek
                $j->ada_bahan_kurang = null;
            }
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
    DB::transaction(function () use ($kode) {
        $jadwal = \App\Models\JadwalProduksi::with('details')->findOrFail($kode);

        // Ambil semua no_sumber dari detail yang tipe_sumber = permintaan
        $permintaanDipakai = $jadwal->details
            ->where('sumber_data', 'permintaan')
            ->pluck('no_sumber')
            ->unique()
            ->toArray();

        // Update status permintaan produksi terkait ke 'Menunggu'
        if (!empty($permintaanDipakai)) {
            \App\Models\PermintaanProduksi::whereIn('no_permintaan_produksi', $permintaanDipakai)
                ->update(['status' => 'Menunggu']);
        }

        // Hapus detail terlebih dahulu jika ada relasi
        $jadwal->details()->delete();
        $jadwal->delete();
    });

    return redirect()->route('jadwal.index')->with('success', 'Jadwal produksi berhasil dihapus!');
}
}
