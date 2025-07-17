<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HppTenagaKerja extends Model
{
    protected $table = 't_hpp_tenaga_kerja_detail';
    protected $primaryKey = 'no_hpp_btkl';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'no_hpp_btkl',
        'no_detail_produksi',
        'kode_karyawan',
        'jumlah_jam',
        'tarif_per_jam',
        'total_biaya_kerja',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'kode_karyawan', 'kode_karyawan');
    }

    public function produksiDetail()
    {
        return $this->belongsTo(ProduksiDetail::class, 'no_detail_produksi', 'no_detail_produksi');
    }
}
