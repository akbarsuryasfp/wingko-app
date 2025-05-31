<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    protected $table = 't_penjualan';
    protected $primaryKey = 'no_jual';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'no_jual',
        'tanggal_jual',
        'kode_pelanggan',
        'total',
        'metode_pembayaran',
        'status_pembayaran',
        'keterangan',
        'kode_user',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'kode_pelanggan', 'kode_pelanggan');
    }

    public function details()
    {
        return $this->hasMany(PenjualanDetail::class, 'no_jual', 'no_jual');
    }
}