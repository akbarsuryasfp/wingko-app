<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


    class KonsinyasiMasuk extends Model
{
    protected $table = 't_konsinyasimasuk';
    protected $primaryKey = 'no_konsinyasimasuk';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'no_konsinyasimasuk',
        'no_surattitipjual',
        'kode_consignor',
        'tanggal_masuk',
        'total_titip',
        'keterangan',
    ];

    public function details()
    {
        return $this->hasMany(KonsinyasiMasukDetail::class, 'no_konsinyasimasuk', 'no_konsinyasimasuk');
    }

    public function consignor()
    {
        return $this->belongsTo(Consignor::class, 'kode_consignor', 'kode_consignor');
    }

    public function konsinyasiMasuk()
    {
        return $this->belongsTo(KonsinyasiMasuk::class, 'no_surattitipjual', 'no_surattitipjual');
    }
}

// Tambahkan kelas model baru untuk KonsinyasiMasuk di file terpisah (KonsinyasiMasuk.php)
