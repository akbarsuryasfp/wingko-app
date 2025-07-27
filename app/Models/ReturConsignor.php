<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturConsignor extends Model
{
    use HasFactory;

    protected $table = 't_returconsignor';
    protected $primaryKey = 'no_returconsignor';
    public $incrementing = false;
    public $timestamps = false;
    protected $guarded = [];

    public function details()
    {
        return $this->hasMany(ReturConsignorDetail::class, 'no_returconsignor', 'no_returconsignor');
    }

    public function konsinyasimasuk()
    {
        return $this->belongsTo(KonsinyasiMasuk::class, 'no_konsinyasimasuk', 'no_konsinyasimasuk');
    }

    public function consignor()
    {
        return $this->belongsTo(Consignor::class, 'kode_consignor', 'kode_consignor');
    }
}
