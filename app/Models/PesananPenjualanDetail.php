<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PesananPenjualanDetail extends Model
{
    protected $table = 't_pesanan_detail';
    protected $primaryKey = 'id'; // ganti jika primary key berbeda
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'no_pesanan',
        'kode_produk',
        'jumlah',
        'harga',
        'subtotal',
        // tambahkan field lain sesuai struktur tabel
    ];

    public function pesanan()
    {
        return $this->belongsTo(PesananPenjualan::class, 'no_pesanan', 'no_pesanan');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'kode_produk', 'kode_produk');
    }
}