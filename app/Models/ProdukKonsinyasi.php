<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProdukKonsinyasi extends Model
{
    protected $table = 't_produk_konsinyasi'; // pastikan nama tabel sesuai di database

    protected $primaryKey = 'kode_produk';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'kode_produk',
        'nama_produk',
        'satuan',
        'kode_consignor',
        'keterangan',
        // tambahkan field lain jika ada
    ];

    public function consignor()
    {
        return $this->belongsTo(Consignor::class, 'kode_consignor', 'kode_consignor');
    }
}