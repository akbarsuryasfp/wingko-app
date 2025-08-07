<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalProduksi extends Model
{
    protected $table = 't_jadwal_produksi';
    protected $primaryKey = 'no_jadwal';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'no_jadwal',
        'tanggal_jadwal',
        'keterangan',
    ];

    public function details()
    {
        return $this->hasMany(JadwalProduksiDetail::class, 'no_jadwal', 'no_jadwal');
    }

}
