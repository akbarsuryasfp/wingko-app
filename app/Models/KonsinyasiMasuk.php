<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KonsinyasiMasukDetail extends Model
{
    protected $table = 't_konsinyasimasuk_detail';
    protected $primaryKey = 'id'; // Ganti jika ada primary key lain
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'no_surattitipjual', 'kode_produk', 'jumlah_stok', 'harga_titip', 'subtotal'
    ];
}

class KonsinyasiMasuk extends Model
{
    protected $table = 't_konsinyasimasuk';
    protected $primaryKey = 'no_surattitipjual';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'no_surattitipjual', 'kode_consignor', 'tanggal_titip', 'total_titip', 'keterangan'
    ];

    public function consignor()
    {
        return $this->belongsTo(Consignor::class, 'kode_consignor', 'kode_consignor');
    }

    public function details()
    {
        return $this->hasMany(KonsinyasiMasukDetail::class, 'no_konsinyasimasuk', 'no_konsinyasimasuk');
    }
}