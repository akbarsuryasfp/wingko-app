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

    // Relasi ke detail order
    public function details()
    {
        return $this->hasMany(OrderDetail::class, 'no_order_beli', 'no_order_beli');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'kode_supplier', 'kode_supplier');
    }
}

// Model detail order (masih dalam file yang sama)
class OrderDetail extends Model
{
    protected $table = 't_order_detail';
    protected $primaryKey = 'no_orderdetail';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'no_orderdetail',
        'no_order_beli',
        'kode_bahan',
        'harga_beli',
        'jumlah_beli',
        'total',
    ];

    public function order()
    {
        return $this->belongsTo(OrderBeli::class, 'no_order_beli', 'no_order_beli');
    }


public function bahan()
{
     return $this->belongsTo(Bahan::class, 'kode_bahan', 'kode_bahan');
} }