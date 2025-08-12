<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lokasi extends Model
{
    protected $table = 't_lokasi';
    protected $primaryKey = 'kode_lokasi';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'kode_lokasi',
        'nama_lokasi',
        'alamat',
        'telepon',
        'latitude',
        'longitude',
    ];
}