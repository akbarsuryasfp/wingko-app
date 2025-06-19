<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hutang extends Model
{
    protected $table = 't_utang';
    protected $primaryKey = 'no_utang';
    public $incrementing = false;
    protected $fillable = [
        'no_utang', 'no_pembelian', 'kode_supplier', 'total_tagihan', 'total_bayar', 'sisa_utang', 'status'
    ];
}
