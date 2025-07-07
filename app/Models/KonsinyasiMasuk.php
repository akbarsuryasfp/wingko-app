<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KonsinyasiMasuk extends Model
{
    protected $table = 't_konsinyasimasuk';
    protected $primaryKey = 'no_surattitipjual';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'no_surattitipjual', 'kode_consignor', 'tanggal_titip', 'total_titip', 'keterangan'
    ];

    public function consignor()
    {
        return $this->belongsTo(\App\Models\Consignor::class, 'kode_consignor', 'kode_consignor');
    }

    public function details()
    {
        return $this->hasMany(\App\Models\KonsinyasiMasukDetail::class, 'no_surattitipjual', 'no_surattitipjual');
    }
}