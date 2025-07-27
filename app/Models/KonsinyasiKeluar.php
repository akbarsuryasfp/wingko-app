<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KonsinyasiKeluar extends Model
{
    protected $table = 't_konsinyasikeluar';
    protected $primaryKey = 'no_konsinyasikeluar';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    
    protected $fillable = [
        'kode_setor', 'tanggal_setor', 'kode_consignee', 'total_setor', 'keterangan', 'no_suratpengiriman'
    ];

    public function consignee()
    {
        return $this->belongsTo(Consignee::class, 'kode_consignee', 'kode_consignee');
    }

    public function details()
    {
        return $this->hasMany(KonsinyasiKeluarDetail::class, 'no_konsinyasikeluar', 'no_konsinyasikeluar');
    }

    // KonsinyasiKeluarDetail logic
    public function produk()
    {
        return $this->belongsTo(ProdukKonsinyasi::class, 'kode_produk', 'kode_produk');
    }
}
