<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermintaanProduksiDetail extends Model
{
    // Nama tabel dalam database
    protected $table = 't_permintaan_produksi_detail';

    // Primary key
    protected $primaryKey = 'no_detail_permintaan_produksi';

    // Tidak menggunakan timestamps
    public $timestamps = false;

    // Kolom yang dapat diisi
    protected $fillable = [
        'no_detail_permintaan_produksi',
        'no_permintaan_produksi',
        'kode_produk',
        'unit',
    ];

    // Relasi ke master permintaan produksi
    public function permintaanProduksi()
    {
        return $this->belongsTo(PermintaanProduksi::class, 'no_permintaan_produksi', 'no_permintaan_produksi');
    }

    // Relasi ke produk
    public function produk()
    {
        return $this->belongsTo(Produk::class, 'kode_produk', 'kode_produk');
    }
}
