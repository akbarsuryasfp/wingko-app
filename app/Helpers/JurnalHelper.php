<?php

namespace App\Helpers;

use App\Models\JurnalUmum;
use App\Models\JurnalDetail;
use App\Helpers\AkunHelper;

class JurnalHelper
{
    public static function catatJurnalHpp($no_detail, $total_hpp, $total_bahan, $total_tk, $total_overhead)
    {
        $tanggal = now()->toDateString();
        $keterangan = 'Produksi selesai, HPP: ' . $no_detail;

        // 1. Buat jurnal umum
        $no_jurnal = self::generateNoJurnal();
        $jurnal = JurnalUmum::create([
            'no_jurnal' => $no_jurnal,
            'tanggal' => $tanggal,
            'keterangan' => $keterangan,
            'nomor_bukti' => 'AUTO-HPP-' . $no_detail,
            // 'jenis_jurnal' => 'umum', // jika ingin set default, sesuaikan jika perlu
        ]);

        // 2. Mapping kode akun otomatis
        $kode_akun_persediaan_jadi = AkunHelper::getIdAkun('105');
        $kode_akun_bahan = AkunHelper::getIdAkun('103');
        $kode_akun_upah = AkunHelper::getIdAkun('503');
        $kode_akun_overhead = AkunHelper::getIdAkun('504');

        // 3. Buat jurnal detail
        JurnalDetail::create([
            'no_jurnal_detail' => self::generateNoJurnalDetail(),
            'no_jurnal' => $no_jurnal,
            'kode_akun' => $kode_akun_persediaan_jadi,
            'debit' => $total_hpp,
            'kredit' => 0,
        ]);
        JurnalDetail::create([
            'no_jurnal_detail' => self::generateNoJurnalDetail(),
            'no_jurnal' => $no_jurnal,
            'kode_akun' => $kode_akun_bahan,
            'debit' => 0,
            'kredit' => $total_bahan,
        ]);
        JurnalDetail::create([
            'no_jurnal_detail' => self::generateNoJurnalDetail(),
            'no_jurnal' => $no_jurnal,
            'kode_akun' => $kode_akun_upah,
            'debit' => 0,
            'kredit' => $total_tk,
        ]);
        JurnalDetail::create([
            'no_jurnal_detail' => self::generateNoJurnalDetail(),
            'no_jurnal' => $no_jurnal,
            'kode_akun' => $kode_akun_overhead,
            'debit' => 0,
            'kredit' => $total_overhead,
        ]);
    }

    public static function generateNoJurnal()
    {
        return 'JU-' . date('YmdHis') . '-' . rand(100,999);
    }

    public static function generateNoJurnalDetail()
    {
        return 'JD-' . date('YmdHis') . '-' . rand(100,999);
    }
}