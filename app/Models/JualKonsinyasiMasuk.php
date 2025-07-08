<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JualKonsinyasiMasukDetail extends Model
{
    protected $table = 't_jualkonsinyasimasuk_detail';
    protected $primaryKey = 'no_detailjualkonsinyasi'; // sesuai database
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'no_detailjualkonsinyasi', 'no_jualkonsinyasi', 'kode_produk', 'jumlah', 'harga_jual', 'subtotal'
    ];
}

class JualKonsinyasiMasuk extends Model
{
    protected $table = 't_jualkonsinyasimasuk';
    protected $primaryKey = 'no_jualkonsinyasi';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'no_jualkonsinyasi', 'tanggal_jual', 'kode_consignor', 'total_jual', 'keterangan', 'no_konsinyasimasuk'
    ];

    public function consignor()
    {
        return $this->belongsTo(Consignor::class, 'kode_consignor', 'kode_consignor');
    }

    public function details()
    {
        return $this->hasMany(JualKonsinyasiMasukDetail::class, 'no_jualkonsinyasi', 'no_jualkonsinyasi');
    }
}

// Perhatian: pastikan di controller JualKonsinyasiMasukController,
// gunakan orderBy('no_jualkonsinyasi') bukan orderBy('no_jualkonsinyasimasuk')
