<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 't_supplier'; // nama tabel di database
    protected $primaryKey = 'kode_supplier'; // primary key
    public $incrementing = false; // primary key bukan auto-increment
    protected $keyType = 'string'; // primary key tipe string
    public $timestamps = false; // jika tabel tidak pakai created_at/updated_at

    protected $fillable = [
        'kode_supplier',
        'nama_supplier',
        'alamat',
        'no_telp',
        'rekening',
        'keterangan'
    ];
}