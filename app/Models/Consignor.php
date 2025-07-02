<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consignor extends Model
{
    protected $table = 't_consignor';
    protected $primaryKey = 'kode_consignor';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'kode_consignor',
        'nama_consignor',
        'alamat',
        'no_telp',
        'rekening',
        'keterangan',
    ];

    /**
     * Relasi ke produk konsinyasi.
     */
    public function produkKonsinyasi()
    {
        return $this->hasMany(\App\Models\ProdukKonsinyasi::class, 'kode_consignor', 'kode_consignor');
    }
}