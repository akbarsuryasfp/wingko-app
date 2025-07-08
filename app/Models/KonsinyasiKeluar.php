<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KonsinyasiKeluar extends Model
{
    protected $table = 't_konsinyasikeluar';
    protected $fillable = [
        'kode_setor', 'tanggal_setor', 'kode_consignee', 'total_setor'
    ];

    public function consignee()
    {
        return $this->belongsTo(Consignee::class, 'kode_consignee', 'kode_consignee');
    }

    public function details()
    {
        return $this->hasMany(KonsinyasiKeluar::class, 'konsinyasikeluar_id');
    }

    // KonsinyasiKeluarDetail logic
    public function produk()
    {
        return $this->belongsTo(ProdukKonsinyasi::class, 'kode_produk', 'kode_produk');
    }
}
