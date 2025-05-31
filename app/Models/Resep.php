<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resep extends Model
{
    protected $table = 't_resep';
    protected $primaryKey = 'kode_resep';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['kode_resep', 'kode_produk', 'keterangan'];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'kode_produk', 'kode_produk');
    }

    public function details()
    {
        return $this->hasMany(ResepDetail::class, 'kode_resep', 'kode_resep');
    }
    
}
