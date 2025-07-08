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
        'total_harga',
        'diskon',
        'total_jual',
        'metode_pembayaran',
        'total_bayar',
        'kembalian',
        'piutang',
        'status_pembayaran',
        'keterangan',
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

class PenjualanDetail extends Model
{
    protected $table = 't_penjualan_detail';
    protected $primaryKey = 'no_detailjual';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'no_detailjual',
        'no_jual',
        'kode_produk',
        'nama_produk',
        'jumlah',
        'harga_satuan',
        'subtotal',
    ];

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'no_jual', 'no_jual');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'kode_produk', 'kode_produk');
    }
}