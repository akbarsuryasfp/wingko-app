<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consignor extends Model
{
    protected $table = 't_consignor'; // nama tabel di database
    protected $primaryKey = 'kode_consignor'; // primary key
    public $incrementing = false; // kode_consignor bukan auto-increment
    protected $keyType = 'string'; // tipe data string
    public $timestamps = false; // jika tidak ada created_at/updated_at

    protected $fillable = [
        'kode_consignor',
        'nama_consignor',
        'alamat',
        'no_telp',
        'keterangan'
    ];
}