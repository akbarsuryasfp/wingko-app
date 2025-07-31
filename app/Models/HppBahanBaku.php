<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HppBahanBaku extends Model
{
    protected $table = 't_hpp_bahan_baku_detail';
    protected $primaryKey = 'no_hpp_bahan';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'no_hpp_bahan',
        'no_detail_produksi',
        'kode_bahan',
        'jumlah_bahan',
        'harga_bahan',  
        'total_bahan',
    ];

    public function produksiDetail()
    {
        return $this->belongsTo(ProduksiDetail::class, 'no_detail_produksi', 'no_detail_produksi');
    }

    public function bahan()
    {
        return $this->belongsTo(Bahan::class, 'kode_bahan', 'kode_bahan');
    }
}
