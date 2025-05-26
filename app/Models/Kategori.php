<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    protected $table = 't_kategori'; // nama tabel di database
    protected $primaryKey = 'kode_kategori'; // primary key
    public $incrementing = false; // primary key bukan auto-increment
    protected $keyType = 'string'; // primary key tipe string
    public $timestamps = false; // jika tabel tidak pakai created_at/updated_at

    protected $fillable = [
        'kode_kategori',
        'jenis_kategori'
    ];
}