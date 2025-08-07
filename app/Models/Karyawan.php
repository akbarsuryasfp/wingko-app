<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    protected $table = 't_karyawan';
    protected $primaryKey = 'kode_karyawan';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false; // Tambahkan baris ini

    protected $fillable = [
        'kode_karyawan', 'nama', 'jabatan', 'departemen', 'gaji', 'tanggal_masuk', 'alamat', 'no_telepon'
    ];
}
