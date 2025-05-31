<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produksi extends Model
{
    protected $table = 't_produksi';
    protected $primaryKey = 'no_produksi';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'no_produksi',
        'tanggal_produksi',
        'keterangan',
    ];

    public function details()
    {
        return $this->hasMany(ProduksiDetail::class, 'no_produksi', 'no_produksi');
    }
}
