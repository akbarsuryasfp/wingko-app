<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProduksiDetail extends Model
{
    protected $table = 't_produksi_detail';
    protected $primaryKey = 'kode_produksi_detail';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'kode_produksi_detail',
        'no_produksi',
        'kode_produk',
        'jumlah_unit',
        'expired',
    ];

    public function produksi()
    {
        return $this->belongsTo(Produksi::class, 'no_produksi', 'no_produksi');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'kode_produk', 'kode_produk');
    }
}
