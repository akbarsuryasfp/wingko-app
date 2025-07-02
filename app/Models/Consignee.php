<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consignee extends Model
{
    protected $table = 't_consignee'; // nama tabel di database
    protected $primaryKey = 'kode_consignee'; // primary key
    public $incrementing = false; // kode_consignee bukan auto-increment
    protected $keyType = 'string'; // tipe data string
    public $timestamps = false; // jika tidak ada created_at/updated_at

    protected $fillable = [
        'kode_consignee',
        'nama_consignee',
        'alamat',
        'no_telp'
    ];
}