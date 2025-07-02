<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PesananPenjualan extends Model
{
    protected $table = 't_pesanan';
    protected $primaryKey = 'no_pesanan';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    // Perbaiki field agar sesuai dengan t_pesanan
    protected $fillable = [
        'no_pesanan',
        'tanggal_pesanan',
        'kode_pelanggan',
        'total_pesanan',
        'keterangan',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'kode_pelanggan', 'kode_pelanggan');
    }

    // Relasi ke detail pesanan, join ke produk untuk dapat nama_produk
    public function details()
    {
        return $this->hasMany(PesananPenjualanDetail::class, 'no_pesanan', 'no_pesanan');
    }
}