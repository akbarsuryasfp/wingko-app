<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KonsinyasiMasukDetail extends Model
{
    protected $table = 't_konsinyasimasuk_detail';
    public $timestamps = false;
    protected $fillable = [
        'no_konsinyasimasuk',
        'kode_produk',
        'jumlah_stok',
        'harga_titip',
        'harga_jual',
        'subtotal',
    ];

    public function header()
    {
        return $this->belongsTo(KonsinyasiMasuk::class, 'no_konsinyasimasuk', 'no_konsinyasimasuk');
    }

    public function produk()
    {
        return $this->belongsTo(ProdukKonsinyasi::class, 'kode_produk', 'kode_produk');
    }
}
