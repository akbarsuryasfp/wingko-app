<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    protected $table = 't_pelanggan'; // nama tabel di database
    protected $primaryKey = 'kode_pelanggan'; // primary key
    public $incrementing = false; // kode_pelanggan bukan auto-increment
    protected $keyType = 'string'; // tipe data string
    public $timestamps = false; // jika tidak ada created_at/updated_at

    protected $fillable = [
        'kode_pelanggan',
        'nama_pelanggan',
        'alamat',
        'no_telp'
    ];
}