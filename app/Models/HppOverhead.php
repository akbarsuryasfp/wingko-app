<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HppOverhead extends Model
{
    protected $table = 't_hpp_overhead_detail';
    protected $primaryKey = 'no_hpp_bop';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'no_hpp_bop',
        'no_detail_produksi',
        'kode_bop',
        'biaya_bop',
    ];

    public function overhead()
    {
        return $this->belongsTo(BOP::class, 'kode_bop', 'kode_bop');
    }

    public function produksiDetail()
    {
        return $this->belongsTo(ProduksiDetail::class, 'no_detail_produksi', 'no_detail_produksi');
    }
}
