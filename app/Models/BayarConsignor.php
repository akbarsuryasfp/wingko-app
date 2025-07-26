<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BayarConsignor extends Model
{
    protected $table = 't_pembayaranconsignor';
    protected $primaryKey = 'no_bayarconsignor';
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = [
        'no_bayarconsignor', 'tanggal_bayar', 'kode_consignor', 'total_bayar', 'keterangan'
    ];

    public function details()
    {
        return $this->hasMany(BayarConsignorDetail::class, 'no_bayarconsignor', 'no_bayarconsignor');
    }
    public function consignor()
    {
        return $this->belongsTo(Consignor::class, 'kode_consignor', 'kode_consignor');
    }
}
