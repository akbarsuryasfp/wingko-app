<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JurnalDetail;
use Carbon\Carbon;
use App\Helpers\JurnalHelper;

class LaporanKeuanganController extends Controller
{
    public function index(Request $request)
    {
        $periode = $request->input('periode', date('Y-m'));
        $start = Carbon::parse($periode . '-01')->startOfMonth();
        $end = Carbon::parse($periode . '-01')->endOfMonth();

        // Gunakan mapping kode akun dari JurnalHelper
        $akun = [
            'kas'        => JurnalHelper::getKodeAkun('kas_bank'),
            'piutang'    => JurnalHelper::getKodeAkun('piutang_usaha'),
            'persediaan' => JurnalHelper::getKodeAkun('persediaan_jadi'),
            'utang'      => JurnalHelper::getKodeAkun('utang_usaha'),
            'modal'      => JurnalHelper::getKodeAkun('modal_pemilik'),
            'penjualan'  => JurnalHelper::getKodeAkun('penjualan'),
            'hpp'        => JurnalHelper::getKodeAkun('hpp'),
            'beban'      => JurnalHelper::getKodeAkun('beban_operasional'),
        ];

        $saldo = [];
        foreach ($akun as $key => $kode) {
            $saldo[$key] = JurnalDetail::where('kode_akun', $kode)
                ->whereHas('jurnalUmum', function($q) use ($start, $end) {
                    $q->whereBetween('tanggal', [$start, $end]);
                })
                ->sum('debit') - JurnalDetail::where('kode_akun', $kode)
                ->whereHas('jurnalUmum', function($q) use ($start, $end) {
                    $q->whereBetween('tanggal', [$start, $end]);
                })
                ->sum('kredit');
        }

        return view('laporan.keuangan', compact('saldo', 'periode'));
    }

    public function cetak(Request $request)
    {
        $periode = $request->input('periode', date('Y-m'));
        $start = Carbon::parse($periode . '-01')->startOfMonth();
        $end = Carbon::parse($periode . '-01')->endOfMonth();

        $akun = [
            'kas'        => JurnalHelper::getKodeAkun('kas_bank'),
            'piutang'    => JurnalHelper::getKodeAkun('piutang_usaha'),
            'persediaan' => JurnalHelper::getKodeAkun('persediaan_jadi'),
            'utang'      => JurnalHelper::getKodeAkun('utang_usaha'),
            'modal'      => JurnalHelper::getKodeAkun('modal_pemilik'),
            'penjualan'  => JurnalHelper::getKodeAkun('penjualan'),
            'hpp'        => JurnalHelper::getKodeAkun('hpp'),
            'beban'      => JurnalHelper::getKodeAkun('beban_operasional'),
        ];

        $saldo = [];
        foreach ($akun as $key => $kode) {
            $saldo[$key] = JurnalDetail::where('kode_akun', $kode)
                ->whereHas('jurnalUmum', function($q) use ($start, $end) {
                    $q->whereBetween('tanggal', [$start, $end]);
                })
                ->sum('debit') - JurnalDetail::where('kode_akun', $kode)
                ->whereHas('jurnalUmum', function($q) use ($start, $end) {
                    $q->whereBetween('tanggal', [$start, $end]);
                })
                ->sum('kredit');
        }

        return view('laporan.keuangan_cetak', compact('saldo', 'periode'));
    }

    public function neraca(Request $request)
    {
        $periode = $request->input('periode', date('Y-m'));
        $start = Carbon::parse($periode . '-01')->startOfMonth();
        $end = Carbon::parse($periode . '-01')->endOfMonth();

        $akun = [
            'kas'        => JurnalHelper::getKodeAkun('kas_bank'),
            'piutang'    => JurnalHelper::getKodeAkun('piutang_usaha'),
            'persediaan' => JurnalHelper::getKodeAkun('persediaan_jadi'),
            'utang'      => JurnalHelper::getKodeAkun('utang_usaha'),
            'modal'      => JurnalHelper::getKodeAkun('modal_pemilik'),
        ];

        $saldo = [];
        foreach ($akun as $key => $kode) {
            $saldo[$key] = JurnalDetail::where('kode_akun', $kode)
                ->whereHas('jurnalUmum', function($q) use ($end) {
                    $q->where('tanggal', '<=', $end);
                })
                ->sum('debit') - JurnalDetail::where('kode_akun', $kode)
                ->whereHas('jurnalUmum', function($q) use ($end) {
                    $q->where('tanggal', '<=', $end);
                })
                ->sum('kredit');
        }

        return view('laporan.neraca', compact('saldo', 'periode'));
    }

    public function labaRugi(Request $request)
    {
        $periode = $request->input('periode', date('Y-m'));
        $start = Carbon::parse($periode . '-01')->startOfMonth();
        $end = Carbon::parse($periode . '-01')->endOfMonth();

        $akun = [
            'penjualan' => JurnalHelper::getKodeAkun('penjualan'),
            'hpp'       => JurnalHelper::getKodeAkun('hpp'),
            'beban'     => JurnalHelper::getKodeAkun('beban_operasional'),
        ];

        $saldo = [];
        foreach ($akun as $key => $kode) {
            $saldo[$key] = JurnalDetail::where('kode_akun', $kode)
                ->whereHas('jurnalUmum', function($q) use ($start, $end) {
                    $q->whereBetween('tanggal', [$start, $end]);
                })
                ->sum('kredit') - JurnalDetail::where('kode_akun', $kode)
                ->whereHas('jurnalUmum', function($q) use ($start, $end) {
                    $q->whereBetween('tanggal', [$start, $end]);
                })
                ->sum('debit');
        }

        $laba_bersih = ($saldo['penjualan'] ?? 0) - ($saldo['hpp'] ?? 0) - ($saldo['beban'] ?? 0);

        return view('laporan.laba_rugi', compact('saldo', 'periode', 'laba_bersih'));
    }

    public function perubahanEkuitas(Request $request)
    {
        $periode = $request->input('periode', date('Y-m'));
        $start = Carbon::parse($periode . '-01')->startOfMonth();
        $end = Carbon::parse($periode . '-01')->endOfMonth();

        // Modal awal (s/d akhir bulan sebelumnya)
        $modal_awal = JurnalDetail::where('kode_akun', JurnalHelper::getKodeAkun('modal_pemilik'))
            ->whereHas('jurnalUmum', fn($q) => $q->where('tanggal', '<', $start))
            ->sum('kredit') - JurnalDetail::where('kode_akun', JurnalHelper::getKodeAkun('modal_pemilik'))
            ->whereHas('jurnalUmum', fn($q) => $q->where('tanggal', '<', $start))
            ->sum('debit');

        // Tambahan modal selama periode
        $tambahan_modal = JurnalDetail::where('kode_akun', JurnalHelper::getKodeAkun('modal_pemilik'))
            ->whereHas('jurnalUmum', fn($q) => $q->whereBetween('tanggal', [$start, $end]))
            ->sum('kredit');

        // Prive
        $prive = JurnalDetail::where('kode_akun', JurnalHelper::getKodeAkun('prive'))
            ->whereHas('jurnalUmum', fn($q) => $q->whereBetween('tanggal', [$start, $end]))
            ->sum('debit');

        // Laba bersih (ambil dari laba rugi)
        $penjualan = JurnalDetail::where('kode_akun', JurnalHelper::getKodeAkun('penjualan'))
            ->whereHas('jurnalUmum', fn($q) => $q->whereBetween('tanggal', [$start, $end]))
            ->sum('kredit');
        $hpp = JurnalDetail::where('kode_akun', JurnalHelper::getKodeAkun('hpp'))
            ->whereHas('jurnalUmum', fn($q) => $q->whereBetween('tanggal', [$start, $end]))
            ->sum('debit');
        $beban = JurnalDetail::where('kode_akun', JurnalHelper::getKodeAkun('beban_operasional'))
            ->whereHas('jurnalUmum', fn($q) => $q->whereBetween('tanggal', [$start, $end]))
            ->sum('debit');
        $laba_bersih = $penjualan - $hpp - $beban;

        $modal_akhir = $modal_awal + $tambahan_modal + $laba_bersih - $prive;

        return view('laporan.perubahan_ekuitas', compact('periode', 'modal_awal', 'tambahan_modal', 'laba_bersih', 'prive', 'modal_akhir'));
    }
}