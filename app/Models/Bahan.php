<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bahan extends Model
{
    protected $table = 't_bahan'; // nama tabel di database
    protected $primaryKey = 'kode_bahan'; // jika primary key bukan 'id'
    public $incrementing = false; // karena Kode_Bahan bukan auto-increment
    protected $keyType = 'string'; // karena Kode_Bahan adalah string
    public $timestamps = false; // tambahkan baris ini

    protected $fillable = [
    'kode_kategori',
    'kode_bahan',
    'nama_bahan',
    'satuan',
    'stokmin',
    'frekuensi_pembelian',
    'interval',
    'jumlah_per_order'
    ];
}
