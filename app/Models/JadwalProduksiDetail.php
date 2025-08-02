<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalProduksiDetail extends Model
{
    protected $table = 't_jadwal_produksi_detail';
    protected $primaryKey = 'no_jadwal_detail';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'no_jadwal_detail',
        'no_jadwal',
        'kode_produk',
        'jumlah',
        'sumber_data',
        'no_sumber',
    ];

    public function jadwal()
    {
        return $this->belongsTo(JadwalProduksi::class, 'no_jadwal', 'no_jadwal');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'kode_produk', 'kode_produk');
    }

    // Opsional: untuk akses ke permintaan atau pesanan nanti
    public function sumberPermintaan()
    {
        return $this->belongsTo(PermintaanProduksi::class, 'no_sumber', 'no_permintaan_produksi');
    }
}
