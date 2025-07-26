<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KonsinyasiKeluarDetail extends Model
{
    protected $table = 't_konsinyasikeluar_detail';
    protected $primaryKey = 'no_detailkonsinyasikeluar';
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = [
        'no_detailkonsinyasikeluar',
        'no_konsinyasikeluar',
        'kode_produk',
        'jumlah_setor',
        'satuan',
        'harga_setor',
        'subtotal',
        'konsinyasikeluar_id', // jika ada kolom ini
    ];

    public function header()
    {
        return $this->belongsTo(KonsinyasiKeluar::class, 'no_konsinyasikeluar', 'no_konsinyasikeluar');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'kode_produk', 'kode_produk');
    }
}
