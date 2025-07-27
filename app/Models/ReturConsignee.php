<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturConsignee extends Model
{
    use HasFactory;

    protected $table = 't_returconsignee';
    protected $primaryKey = 'no_returconsignee';
    public $incrementing = false;
    public $timestamps = false;
    protected $guarded = [];

    // Relasi ke detail retur consignee
    public function details()
    {
        return $this->hasMany(ReturConsigneeDetail::class, 'no_returconsignee', 'no_returconsignee');
    }

    // Relasi ke konsinyasi masuk
    public function konsinyasimasuk()
    {
        return $this->belongsTo(KonsinyasiMasuk::class, 'no_konsinyasimasuk', 'no_konsinyasimasuk');
    }

    // Relasi ke consignee
    public function consignee()
    {
        return $this->belongsTo(Consignee::class, 'kode_consignee', 'kode_consignee');
    }

    // Relasi ke konsinyasi keluar (jika diperlukan)
    public function konsinyasikeluar()
    {
        return $this->belongsTo(KonsinyasiKeluar::class, 'no_konsinyasikeluar', 'no_konsinyasikeluar');
    }
}
