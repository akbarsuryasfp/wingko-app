<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenerimaanKonsinyasi extends Model
{
    protected $table = 't_penerimaankonsinyasi';
    protected $primaryKey = 'no_penerimaankonsinyasi';
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = [
        'no_penerimaankonsinyasi',
        'no_konsinyasikeluar',
        'tanggal_terima',
        'kode_consignee',
        'metode_pembayaran',
        'total_terima',
        'keterangan',
    ];

    public function consignee()
    {
        return $this->belongsTo(Consignee::class, 'kode_consignee', 'kode_consignee');
    }

    public function details()
    {
        return $this->hasMany(PenerimaanKonsinyasiDetail::class, 'no_penerimaankonsinyasi', 'no_penerimaankonsinyasi');
    }
}
