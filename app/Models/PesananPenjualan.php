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

    protected $fillable = [
        'no_pesanan',
        'tanggal_pesanan',
        'kode_pelanggan',
        'total',
        'status_pembayaran',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'kode_pelanggan', 'kode_pelanggan');
    }

    public function details()
    {
        return $this->hasMany(PesananPenjualanDetail::class, 'no_pesanan', 'no_pesanan');
    }
}