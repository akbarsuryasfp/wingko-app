<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderBeli extends Model
{
    protected $table = 't_order_beli'; // tabel utama
    protected $primaryKey = 'no_order_beli';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'no_order_beli',
        'tanggal_order',
        'kode_supplier',
        'total_order',
        'status', // jika ada kolom status
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'kode_supplier', 'kode_supplier');
    }

}