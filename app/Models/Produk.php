<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    protected $table = 't_produk'; // nama tabel di database
    protected $primaryKey = 'kode_produk'; // primary key
    public $incrementing = false; // primary key bukan auto-increment
    protected $keyType = 'string'; // primary key tipe string
    public $timestamps = false; // jika tabel tidak pakai created_at/updated_at

    protected $fillable = [
        'kode_produk',
        'kode_kategori',
        'nama_produk',
        'satuan',
        'stokmin'
    ];

    public function resep()
    {
        return $this->hasOne(Resep::class, 'kode_produk', 'kode_produk');
    }
}
