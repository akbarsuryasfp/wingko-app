<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HppPerProduk extends Model
{
    protected $table = 't_hpp_per_produk'; // pastikan sesuai nama tabel di database
    protected $primaryKey = 'no_hpp_per_produk'; // sesuaikan jika ada
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $guarded = [];

    protected $fillable = [
        'id',
        'no_detail_produksi',
        'kode_produk',
        'total_bahan',
        'total_tenaga_kerja',
        'total_overhead',
        'total_hpp',
        'hpp_per_produk',
        'tanggal_input',
    ];
}
