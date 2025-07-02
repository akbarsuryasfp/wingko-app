<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BopTransaksi extends Model
{
    protected $table = 't_bop_transaksi';
    protected $guarded = [];
    public $timestamps = false;

    public function bop()
    {
        return $this->belongsTo(BOP::class, 'kode_bop', 'kode_bop');
    }
}