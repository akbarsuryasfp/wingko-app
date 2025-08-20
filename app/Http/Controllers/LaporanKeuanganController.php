<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JurnalDetail;
use Carbon\Carbon;
use App\Helpers\JurnalHelper;
use App\Models\AsetTetap;
use PDF; // Tambahkan di atas class

class LaporanKeuanganController extends Controller
{
    public function index(Request $request)
    {
        $periode = $request->input('periode', date('Y-m'));
        $start = Carbon::parse($periode . '-01')->startOfMonth();
        $end = Carbon::parse($periode . '-01')->endOfMonth();

        // Mapping akun lebih lengkap
        $akun = [
            'kas_bank'         => JurnalHelper::getKodeAkun('kas_bank'),
            'kas_kecil'        => JurnalHelper::getKodeAkun('kas_kecil'),
            'piutang_usaha'    => JurnalHelper::getKodeAkun('piutang_usaha'),
            'persediaan_bahan' => JurnalHelper::getKodeAkun('persediaan_bahan'),
            'persediaan_jadi'  => JurnalHelper::getKodeAkun('persediaan_jadi'),
            'utang_usaha'      => JurnalHelper::getKodeAkun('utang_usaha'),
            'utang_bank'       => JurnalHelper::getKodeAkun('utang_bank'),
            'utang_pajak'      => JurnalHelper::getKodeAkun('utang_pajak'),
            'modal_pemilik'    => JurnalHelper::getKodeAkun('modal_pemilik'),
            'prive'            => JurnalHelper::getKodeAkun('prive'),
            'penjualan'        => JurnalHelper::getKodeAkun('penjualan'),
            'retur_penjualan'  => JurnalHelper::getKodeAkun('retur_penjualan'),
            'pendapatan_lain'  => JurnalHelper::getKodeAkun('pendapatan_lain'),
            'retur_pembelian'  => JurnalHelper::getKodeAkun('retur_pembelian'),
            'hpp'              => JurnalHelper::getKodeAkun('hpp'),
            'biaya_listrik'    => JurnalHelper::getKodeAkun('biaya_listrik'),
            'beban_operasional'=> JurnalHelper::getKodeAkun('beban_operasional'),
            'beban_kerugian'   => JurnalHelper::getKodeAkun('beban_kerugian'),
            'beban_penyusutan_bangunan' => JurnalHelper::getKodeAkun('beban_penyusutan_bangunan'),
            'beban_penyusutan_mesin'    => JurnalHelper::getKodeAkun('beban_penyusutan_mesin'),
            'upah'             => JurnalHelper::getKodeAkun('upah'),
            'overhead'         => JurnalHelper::getKodeAkun('overhead'),
            'beban_lain'       => JurnalHelper::getKodeAkun('beban_lain'),
            'diskon_pembelian' => JurnalHelper::getKodeAkun('diskon_pembelian'),
            'ongkos_kirim'     => JurnalHelper::getKodeAkun('ongkos_kirim'),
            'diskon_penjualan' => JurnalHelper::getKodeAkun('diskon_penjualan'),
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

        // Mapping akun sama seperti index
        $akun = [
            'kas_bank'         => JurnalHelper::getKodeAkun('kas_bank'),
            'kas_kecil'        => JurnalHelper::getKodeAkun('kas_kecil'),
            'piutang_usaha'    => JurnalHelper::getKodeAkun('piutang_usaha'),
            'persediaan_bahan' => JurnalHelper::getKodeAkun('persediaan_bahan'),
            'persediaan_jadi'  => JurnalHelper::getKodeAkun('persediaan_jadi'),
            'utang_usaha'      => JurnalHelper::getKodeAkun('utang_usaha'),
            'utang_bank'       => JurnalHelper::getKodeAkun('utang_bank'),
            'utang_pajak'      => JurnalHelper::getKodeAkun('utang_pajak'),
            'modal_pemilik'    => JurnalHelper::getKodeAkun('modal_pemilik'),
            'prive'            => JurnalHelper::getKodeAkun('prive'),
            'penjualan'        => JurnalHelper::getKodeAkun('penjualan'),
            'retur_penjualan'  => JurnalHelper::getKodeAkun('retur_penjualan'),
            'pendapatan_lain'  => JurnalHelper::getKodeAkun('pendapatan_lain'),
            'retur_pembelian'  => JurnalHelper::getKodeAkun('retur_pembelian'),
            'hpp'              => JurnalHelper::getKodeAkun('hpp'),
            'biaya_listrik'    => JurnalHelper::getKodeAkun('biaya_listrik'),
            'beban_operasional'=> JurnalHelper::getKodeAkun('beban_operasional'),
            'beban_kerugian'   => JurnalHelper::getKodeAkun('beban_kerugian'),
            'beban_penyusutan_bangunan' => JurnalHelper::getKodeAkun('beban_penyusutan_bangunan'),
            'beban_penyusutan_mesin'    => JurnalHelper::getKodeAkun('beban_penyusutan_mesin'),
            'upah'             => JurnalHelper::getKodeAkun('upah'),
            'overhead'         => JurnalHelper::getKodeAkun('overhead'),
            'beban_lain'       => JurnalHelper::getKodeAkun('beban_lain'),
            'diskon_pembelian' => JurnalHelper::getKodeAkun('diskon_pembelian'),
            'ongkos_kirim'     => JurnalHelper::getKodeAkun('ongkos_kirim'),
            'diskon_penjualan' => JurnalHelper::getKodeAkun('diskon_penjualan'),
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
        $end = \Carbon\Carbon::parse($periode . '-01')->endOfMonth();

        // Mapping akun neraca lengkap
        $akunKeys = [
            'kas_bank'              => 'kas_bank',
            'kas_kecil'             => 'kas_kecil',
            'piutang_usaha'         => 'piutang_usaha',
            'uang_muka'             => 'uang_muka',
            'persediaan_bahan'      => 'persediaan_bahan',
            'persediaan_jadi'       => 'persediaan_jadi',
            'aset_tetap'            => 'aset_tetap',
            'tanah'                 => 'tanah',
            'bangunan'              => 'bangunan',
            'mesin'                 => 'mesin',
            'akumulasi_penyusutan_bangunan' => 'akumulasi_penyusutan_bangunan',
            'akumulasi_penyusutan_mesin'    => 'akumulasi_penyusutan_mesin',
            'utang_usaha'           => 'utang_usaha',
            'utang_bank'            => 'utang_bank',
            'utang_pajak'           => 'utang_pajak',
            'modal_pemilik'         => 'modal_pemilik',
            'prive'                 => 'prive',
        ];

        $saldo = [];
        foreach ($akunKeys as $key => $akunKey) {
            $kodeAkun = \App\Helpers\JurnalHelper::getKodeAkun($akunKey);
            $debit = \App\Models\JurnalDetail::where('kode_akun', $kodeAkun)
                ->whereHas('jurnalUmum', function($q) use ($end) {
                    $q->where('tanggal', '<=', $end);
                })
                ->sum('debit');
            $kredit = \App\Models\JurnalDetail::where('kode_akun', $kodeAkun)
                ->whereHas('jurnalUmum', function($q) use ($end) {
                    $q->where('tanggal', '<=', $end);
                })
                ->sum('kredit');
            // Saldo kredit untuk akun tertentu
            if (in_array($akunKey, [
                'akumulasi_penyusutan_bangunan',
                'akumulasi_penyusutan_mesin',
                'prive',
                'utang_usaha',
                'utang_bank',
                'utang_pajak',
                'modal_pemilik'
            ])) {
                $saldo[$key . '_' . \App\Helpers\JurnalHelper::$akunMap[$akunKey]] = $kredit - $debit;
            } else {
                $saldo[$key . '_' . \App\Helpers\JurnalHelper::$akunMap[$akunKey]] = $debit - $kredit;
            }
        }

        // Query aset tetap
        $asetTetap = AsetTetap::selectRaw('tipe_aset, SUM(harga_perolehan) as total')
            ->groupBy('tipe_aset')
            ->get()
            ->pluck('total', 'tipe_aset')
            ->toArray();

        $saldo['tanah_1110'] = $asetTetap['tanah'] ?? 0;
        $saldo['bangunan_1120'] = $asetTetap['bangunan'] ?? 0;
        $saldo['mesin_1130'] = $asetTetap['mesin'] ?? 0;

        return view('laporan.neraca', compact('saldo', 'periode'));
    }

    public function neracaPdf(Request $request)
    {
        $periode = $request->input('periode', date('Y-m'));
        $end = \Carbon\Carbon::parse($periode . '-01')->endOfMonth();

        // Copy logic saldo dari neraca()
        $akunKeys = [
            'kas_bank'              => 'kas_bank',
            'kas_kecil'             => 'kas_kecil',
            'piutang_usaha'         => 'piutang_usaha',
            'uang_muka'             => 'uang_muka',
            'persediaan_bahan'      => 'persediaan_bahan',
            'persediaan_jadi'       => 'persediaan_jadi',
            'aset_tetap'            => 'aset_tetap',
            'tanah'                 => 'tanah',
            'bangunan'              => 'bangunan',
            'mesin'                 => 'mesin',
            'akumulasi_penyusutan_bangunan' => 'akumulasi_penyusutan_bangunan',
            'akumulasi_penyusutan_mesin'    => 'akumulasi_penyusutan_mesin',
            'utang_usaha'           => 'utang_usaha',
            'utang_bank'            => 'utang_bank',
            'utang_pajak'           => 'utang_pajak',
            'modal_pemilik'         => 'modal_pemilik',
            'prive'                 => 'prive',
        ];

        $saldo = [];
        foreach ($akunKeys as $key => $akunKey) {
            $kodeAkun = \App\Helpers\JurnalHelper::getKodeAkun($akunKey);
            $debit = \App\Models\JurnalDetail::where('kode_akun', $kodeAkun)
                ->whereHas('jurnalUmum', function($q) use ($end) {
                    $q->where('tanggal', '<=', $end);
                })
                ->sum('debit');
            $kredit = \App\Models\JurnalDetail::where('kode_akun', $kodeAkun)
                ->whereHas('jurnalUmum', function($q) use ($end) {
                    $q->where('tanggal', '<=', $end);
                })
                ->sum('kredit');
            if (in_array($akunKey, [
                'akumulasi_penyusutan_bangunan',
                'akumulasi_penyusutan_mesin',
                'prive',
                'utang_usaha',
                'utang_bank',
                'utang_pajak',
                'modal_pemilik'
            ])) {
                $saldo[$key . '_' . \App\Helpers\JurnalHelper::$akunMap[$akunKey]] = $kredit - $debit;
            } else {
                $saldo[$key . '_' . \App\Helpers\JurnalHelper::$akunMap[$akunKey]] = $debit - $kredit;
            }
        }

        $asetTetap = \App\Models\AsetTetap::selectRaw('tipe_aset, SUM(harga_perolehan) as total')
            ->groupBy('tipe_aset')
            ->get()
            ->pluck('total', 'tipe_aset')
            ->toArray();

        $saldo['tanah_1110'] = $asetTetap['tanah'] ?? 0;
        $saldo['bangunan_1120'] = $asetTetap['bangunan'] ?? 0;
        $saldo['mesin_1130'] = $asetTetap['mesin'] ?? 0;

        $pdf = PDF::loadView('laporan.neraca_pdf', compact('saldo', 'periode'));
        return $pdf->stream('neraca-' . $periode . '.pdf');
    }

    public function labaRugi(Request $request)
    {
        $periode = $request->input('periode', date('Y-m'));
        $start = \Carbon\Carbon::parse($periode . '-01')->startOfMonth();
        $end = \Carbon\Carbon::parse($periode . '-01')->endOfMonth();

        $getSaldo = function($kodeAkun, $tipe = 'pendapatan') use ($start, $end) {
            $query = \DB::table('t_jurnal_detail')
                ->join('t_jurnal_umum', 't_jurnal_detail.no_jurnal', '=', 't_jurnal_umum.no_jurnal')
                ->where('t_jurnal_detail.kode_akun', $kodeAkun)
                ->whereBetween('t_jurnal_umum.tanggal', [$start, $end]);
            $debit = $query->sum('debit');
            $kredit = $query->sum('kredit');
            return $tipe === 'pendapatan' ? ($kredit - $debit) : ($debit - $kredit);
        };

        // Mapping kode akun sesuai t_akun
        $penjualan         = $getSaldo(4000, 'pendapatan');
        $pendapatan_lain   = $getSaldo(4010, 'pendapatan');
        $retur_penjualan   = $getSaldo(4001, 'pendapatan');
        $diskon_penjualan  = $getSaldo(5070, 'pendapatan');
        $retur_pembelian   = $getSaldo(4025, 'pendapatan');

        $hpp               = $getSaldo(5000, 'beban');

        $kodeBeban = [
            5001 => 'Biaya Listrik',
            5010 => 'Beban Operasional',
            5011 => 'Beban Kerugian',
            5012 => 'Beban Penyusutan Bangunan',
            5013 => 'Beban Penyusutan Mesin',
            5020 => 'Beban Gaji',
            5030 => 'Beban Overhead Pabrik',
            5040 => 'Beban Lain-lain',
            5050 => 'Diskon Pembelian',
            5060 => 'Ongkir Pembelian',
        ];
        $list_beban = [];
        $beban_operasional = 0;
        foreach ($kodeBeban as $kode => $nama) {
            $saldo = $getSaldo($kode, 'beban');
            if ($saldo != 0) {
                $list_beban[] = ['nama' => $nama, 'saldo' => $saldo];
                $beban_operasional += $saldo;
            }
        }

        $pendapatan_bersih = $penjualan + $pendapatan_lain - $retur_penjualan - $diskon_penjualan;
        $laba_kotor        = $pendapatan_bersih - $hpp;
        $laba_usaha        = $laba_kotor - $beban_operasional;
        $laba_bersih       = $laba_usaha;

        return view('laporan.laba_rugi', compact(
            'periode',
            'penjualan',
            'pendapatan_lain',
            'retur_penjualan',
            'diskon_penjualan',
            'retur_pembelian',
            'pendapatan_bersih',
            'hpp',
            'laba_kotor',
            'list_beban',
            'beban_operasional',
            'laba_usaha',
            'laba_bersih'
        ));
    }

    public function labaRugiPdf(Request $request)
    {
        $periode = $request->input('periode', date('Y-m'));
        $start = \Carbon\Carbon::parse($periode . '-01')->startOfMonth();
        $end = \Carbon\Carbon::parse($periode . '-01')->endOfMonth();

        $getSaldo = function($kodeAkun, $tipe = 'pendapatan') use ($start, $end) {
            $query = \App\Models\JurnalDetail::where('kode_akun', $kodeAkun)
                ->whereHas('jurnalUmum', function($q) use ($start, $end) {
                    $q->whereBetween('tanggal', [$start, $end]);
                });
            $debit = $query->sum('debit');
            $kredit = $query->sum('kredit');
            return $tipe == 'pendapatan' ? ($kredit - $debit) : ($debit - $kredit);
        };

        $penjualan         = $getSaldo(4000, 'pendapatan');
        $pendapatan_lain   = $getSaldo(4010, 'pendapatan');
        $retur_penjualan   = $getSaldo(4001, 'pendapatan');
        $diskon_penjualan  = $getSaldo(5070, 'pendapatan');
        $retur_pembelian   = $getSaldo(4025, 'pendapatan');
        $hpp               = $getSaldo(5000, 'beban');

        // Samakan kode beban dengan view
        $kodeBeban = [
            5001 => 'Biaya Listrik',
            5010 => 'Beban Operasional',
            5011 => 'Beban Kerugian',
            5012 => 'Beban Penyusutan Bangunan',
            5013 => 'Beban Penyusutan Mesin',
            5020 => 'Beban Gaji',
            5030 => 'Beban Overhead Pabrik',
            5040 => 'Beban Lain-lain',
            5050 => 'Diskon Pembelian',
            5060 => 'Ongkir Pembelian',
        ];
        $list_beban = [];
        $beban_operasional = 0;
        foreach ($kodeBeban as $kode => $nama) {
            $saldo = $getSaldo($kode, 'beban');
            if ($saldo != 0) {
                $list_beban[] = ['nama' => $nama, 'saldo' => $saldo];
                $beban_operasional += $saldo;
            }
        }

        $pendapatan_bersih = $penjualan + $pendapatan_lain - $retur_penjualan - $diskon_penjualan;
        $laba_kotor        = $pendapatan_bersih - $hpp;
        $laba_usaha        = $laba_kotor + $beban_operasional;
        $laba_bersih       = $laba_usaha;

        $pdf = \PDF::loadView('laporan.laba_rugi_pdf', compact(
            'periode',
            'penjualan',
            'pendapatan_lain',
            'retur_penjualan',
            'diskon_penjualan',
            'retur_pembelian',
            'pendapatan_bersih',
            'hpp',
            'laba_kotor',
            'list_beban',
            'beban_operasional',
            'laba_usaha',
            'laba_bersih'
        ));
        return $pdf->stream('laba-rugi-' . $periode . '.pdf');
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

    public function hppPenjualan(Request $request)
    {
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));

        // Persediaan Awal Barang Jadi (akhir bulan sebelumnya)
        $bulanSebelumnya = $bulan == 1 ? 12 : $bulan - 1;
        $tahunSebelumnya = $bulan == 1 ? $tahun - 1 : $tahun;
        $persediaan_awal_jadi = \DB::table('t_kartupersproduk')
            ->whereDate('tanggal', '<=', date('Y-m-t', strtotime("$tahunSebelumnya-$bulanSebelumnya-01")))
            ->sum(\DB::raw('(masuk - keluar) * hpp'));

        // Harga Pokok Produksi: SUM(hpp_per_produk * jumlah_unit) bulan berjalan
        $harga_pokok_produksi = \DB::table('t_hpp_per_produk')
            ->join('t_produksi_detail', 't_hpp_per_produk.no_detail_produksi', '=', 't_produksi_detail.no_detail_produksi')
            ->join('t_produksi', 't_produksi_detail.no_produksi', '=', 't_produksi.no_produksi')
            ->whereMonth('t_produksi.tanggal_produksi', $bulan)
            ->whereYear('t_produksi.tanggal_produksi', $tahun)
            ->sum(\DB::raw('t_hpp_per_produk.hpp_per_produk * t_produksi_detail.jumlah_unit'));

        // Persediaan Akhir Barang Jadi (akhir bulan ini)
        $persediaan_akhir_jadi = \DB::table('t_kartupersproduk')
            ->whereDate('tanggal', '<=', date('Y-m-t', strtotime("$tahun-$bulan-01")))
            ->sum(\DB::raw('(masuk - keluar) * hpp'));

        // Hitung HPP Penjualan
        $hpp_penjualan = ($persediaan_awal_jadi ?? 0) + ($harga_pokok_produksi ?? 0) - ($persediaan_akhir_jadi ?? 0);

        return view('laporan.hpp_penjualan', compact(
            'bulan', 'tahun', 'persediaan_awal_jadi', 'harga_pokok_produksi', 'persediaan_akhir_jadi', 'hpp_penjualan'
        ));
    }

    public function labaBersihTahunBerjalan(Request $request)
    {
        // Hitung laba bersih tahun berjalan
        $periode = $request->input('periode', date('Y-m'));
        $bulan = \Carbon\Carbon::parse($periode . '-01')->month;
        $tahun = \Carbon\Carbon::parse($periode . '-01')->year;
        $start = \Carbon\Carbon::parse($periode . '-01')->startOfMonth();
        $end = \Carbon\Carbon::parse($periode . '-01')->endOfMonth();

        $getSaldo = function($kodeAkun, $tipe = 'pendapatan') use ($start, $end) {
            $query = \App\Models\JurnalDetail::where('kode_akun', $kodeAkun)
                ->whereHas('jurnalUmum', function($q) use ($start, $end) {
                    $q->whereBetween('tanggal', [$start, $end]);
                });
            $debit = $query->sum('debit');
            $kredit = $query->sum('kredit');
            return $tipe == 'pendapatan' ? ($kredit - $debit) : ($debit - $kredit);
        };

        $penjualan         = $getSaldo(4000, 'pendapatan');
        $pendapatan_lain   = $getSaldo(4010, 'pendapatan');
        $retur_penjualan   = $getSaldo(4001, 'pendapatan');
        $diskon_penjualan  = $getSaldo(5070, 'pendapatan');
        $hpp               = $getSaldo(5000, 'beban');
        $kodeBeban = [
            5001,5010,5011,5012,5013,5020,5030,5040,5050,5060
        ];
        $beban_operasional = 0;
        foreach ($kodeBeban as $kode) {
            $beban_operasional += $getSaldo($kode, 'beban');
        }
        $pendapatan_bersih = $penjualan + $pendapatan_lain - $retur_penjualan - $diskon_penjualan;
        $laba_kotor        = $pendapatan_bersih - $hpp;
        $laba_bersih       = $laba_kotor - $beban_operasional;

        // Tambahkan laba bersih ke modal pemilik
        if(isset($saldo['modal_pemilik_3000'])) {
            $saldo['modal_pemilik_3000'] += $laba_bersih;
        }

        return view('laporan.laba_bersih_tahun_berjalan', compact('periode', 'laba_bersih'));
    }
}