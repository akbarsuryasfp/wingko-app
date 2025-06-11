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
        $jurnal = JurnalUmum::create([
            'tanggal' => $tanggal,
            'keterangan' => $keterangan,
            'nomor_bukti' => 'AUTO-HPP-' . $no_detail,
        ]);

        // 2. Mapping id_akun otomatis
        $id_akun_persediaan_jadi = AkunHelper::getIdAkun('105');
        $id_akun_bahan = AkunHelper::getIdAkun('103');
        $id_akun_upah = AkunHelper::getIdAkun('503');
        $id_akun_overhead = AkunHelper::getIdAkun('504');

        // 3. Buat jurnal detail
        JurnalDetail::create([
            'id_jurnal' => $jurnal->id_jurnal,
            'id_akun' => $id_akun_persediaan_jadi,
            'debit' => $total_hpp,
            'kredit' => 0,
        ]);
        JurnalDetail::create([
            'id_jurnal' => $jurnal->id_jurnal,
            'id_akun' => $id_akun_bahan,
            'debit' => 0,
            'kredit' => $total_bahan,
        ]);
        JurnalDetail::create([
            'id_jurnal' => $jurnal->id_jurnal,
            'id_akun' => $id_akun_upah,
            'debit' => 0,
            'kredit' => $total_tk,
        ]);
        JurnalDetail::create([
            'id_jurnal' => $jurnal->id_jurnal,
            'id_akun' => $id_akun_overhead,
            'debit' => 0,
            'kredit' => $total_overhead,
        ]);
    }
}