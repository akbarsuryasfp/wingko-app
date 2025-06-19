<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{
    protected $table = 't_pembelian';
    protected $primaryKey = 'id'; // ganti jika primary key bukan 'id'
    public $timestamps = false; // set true jika ada kolom created_at/updated_at

    protected $fillable = [
    'no_pembelian',
    'tanggal_pembelian',
    'no_terima_bahan',
    'kode_supplier',
    'total_harga',
    'diskon',
    'ongkir',
    'total_pembelian',
    'total_bayar',
    'hutang',
    'created_at',
    ];

    public function terimabahan()
    {
        return $this->belongsTo(TerimaBahan::class, 'no_terima_bahan', 'no_terima_bahan');
    }

}