<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturConsignorDetail extends Model
{
    protected $table = 't_returconsignor_detail';
    public $timestamps = false;
    protected $guarded = [];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'kode_produk', 'kode_produk');
    }
}
