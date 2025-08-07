<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenerimaanKonsinyasiDetail extends Model
{
    protected $table = 't_penerimaankonsinyasi_detail';
    protected $primaryKey = 'no_detailpenerimaankonsinyasi';
    public $incrementing = false;
    public $timestamps = false;
    protected $keyType = 'string';

    protected $fillable = [
        'no_detailpenerimaankonsinyasi',
        'no_penerimaankonsinyasi',
        'kode_produk',
        'jumlah_setor',
        'jumlah_terjual',
        'satuan',
        'harga_satuan',
        'subtotal',
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'kode_produk', 'kode_produk');
    }
}
