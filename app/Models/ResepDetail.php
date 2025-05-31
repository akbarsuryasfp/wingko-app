<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResepDetail extends Model
{
    protected $table = 't_resep_detail';
    protected $primaryKey = 'kode_resep_detail';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['kode_resep_detail', 'kode_resep', 'kode_bahan', 'jumlah_kebutuhan', 'satuan'];

    public function bahan()
    {
        return $this->belongsTo(Bahan::class, 'kode_bahan', 'kode_bahan');
    }

    public function resep()
    {
        return $this->belongsTo(Resep::class, 'kode_resep', 'kode_resep');
    }
}
