<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BayarConsignorDetail extends Model
{
    protected $table = 't_pembayaranconsignor_detail';
    protected $primaryKey = 'no_detailbayarconsignor';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'no_detailbayarconsignor',
        'no_bayarconsignor',
        'kode_produk',
        'jumlah_terjual',
        'harga_satuan',
        'subtotal'
    ];
    
    public function produk()
    {
        return $this->belongsTo(ProdukKonsinyasi::class, 'kode_produk', 'kode_produk');
    }
}