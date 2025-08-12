<?php

namespace App\Helpers;

use App\Models\JurnalUmum;
use App\Models\JurnalDetail;
use App\Helpers\AkunHelper;

class JurnalHelper
{
    // Mapping kode akun aplikasi ke kode akun database (update sesuai t_akun)
    public static $akunMap = [
        'kas_bank'            => '1010', // Kas di Bank
        'kas_kecil'           => '1011', // Kas Kecil
        'piutang_usaha'       => '1020', // Piutang Usaha
        'uang_muka'           => '1025', // Uang Muka     
        'persediaan_bahan'    => '1030', // Persediaan Bahan
        'persediaan_jadi'     => '1040', // Persediaan Barang Jadi
        'aset_tetap'          => '1100', // Aset Tetap
        'tanah'               => '1110', // Tanah
        'bangunan'            => '1120', // Bangunan
        'mesin'               => '1130', // Mesin
        'akumulasi_penyusutan'=> '1140', // Akumulasi Penyusutan
        'utang_usaha'         => '2000', // Utang Usaha
        'utang_bank'          => '2010', // Utang Bank
        'utang_pajak'         => '2020', // Utang Pajak
        'modal_pemilik'       => '3000', // Modal Pemilik
        'prive'               => '3010', // Prive
        'penjualan'           => '4000', // Penjualan
        'pendapatan_lain'     => '4010', // Pendapatan Lain-lain
        'retur_pembelian'     => '4025', // Retur Pembelian
        'hpp'                 => '5000', // Harga Pokok Penjualan
        'biaya_listrik'       => '5001', // Biaya Listrik
        'beban_operasional'   => '5010', // Beban Operasional
        'beban_kerugian'      => '5011', // Beban Kerugian
        'upah'                => '5020', // Upah/BTKL
        'overhead'            => '5030', // Overhead Pabrik
        'beban_lain'          => '5040', // Beban Lain-lain
        'diskon_pembelian'    => '5050', // Diskon Pembelian
        'ongkos_kirim'      => '5060', // Ongkos Kirim
        'pajak'               => '5070', // Pajak
        // tambahkan jika ada akun baru di t_akun
    ];

    public static function getKodeAkun($key)
    {
        return AkunHelper::getIdAkun(self::$akunMap[$key] ?? '');
    }

    public static function catatJurnalHpp($no_detail, $total_hpp, $total_bahan, $total_tk, $total_overhead, $tanggal)
    {
        $keterangan = 'Produksi selesai, HPP: ' . $no_detail;

        // 1. Buat jurnal umum
        $no_jurnal = self::generateNoJurnal();
        $jurnal = JurnalUmum::create([
            'no_jurnal' => $no_jurnal,
            'tanggal' => $tanggal,
            'keterangan' => $keterangan,
            'nomor_bukti' => $no_detail,
        ]);

        // 2. Jurnal: 
        //    Debit  Persediaan Barang Jadi (total_hpp)
        //    Kredit Persediaan Bahan Baku (total_bahan)
        //    Kredit Upah/BTKL (total_tk)
        //    Kredit Overhead Pabrik (total_overhead)

        // Debit Persediaan Barang Jadi
        JurnalDetail::create([
            'no_jurnal_detail' => self::generateNoJurnalDetail($no_jurnal),
            'no_jurnal' => $no_jurnal,
            'kode_akun' => self::getKodeAkun('persediaan_jadi'),
            'debit' => $total_hpp,
            'kredit' => 0,
        ]);
        // Kredit Persediaan Bahan Baku
        JurnalDetail::create([
            'no_jurnal_detail' => self::generateNoJurnalDetail($no_jurnal),
            'no_jurnal' => $no_jurnal,
            'kode_akun' => self::getKodeAkun('persediaan_bahan'),
            'debit' => 0,
            'kredit' => $total_bahan,
        ]);
        // Kredit Upah/BTKL
        JurnalDetail::create([
            'no_jurnal_detail' => self::generateNoJurnalDetail($no_jurnal),
            'no_jurnal' => $no_jurnal,
            'kode_akun' => self::getKodeAkun('upah'),
            'debit' => 0,
            'kredit' => $total_tk,
        ]);
        // Kredit Overhead Pabrik
        JurnalDetail::create([
            'no_jurnal_detail' => self::generateNoJurnalDetail($no_jurnal),
            'no_jurnal' => $no_jurnal,
            'kode_akun' => self::getKodeAkun('overhead'),
            'debit' => 0,
            'kredit' => $total_overhead,
        ]);
    }

    public static function generateNoJurnal($i = 1)
    {
        return 'JU-' . date('YmdHis') . '-' . ($i);
    }

    public static function generateNoJurnalDetail($no_jurnal)
    {
        $prefix = 'JD-' . date('YmdHis');
        // Cari urutan terakhir untuk no_jurnal ini
        $last = \App\Models\JurnalDetail::where('no_jurnal', $no_jurnal)
            ->where('no_jurnal_detail', 'like', $prefix . '-%')
            ->orderByDesc('no_jurnal_detail')
            ->first();

        if ($last) {
            $lastNo = intval(substr($last->no_jurnal_detail, strrpos($last->no_jurnal_detail, '-') + 1));
            $nextNo = $lastNo + 1;
        } else {
            $nextNo = 1;
        }

        return $prefix . '-' . $nextNo;
    }
}