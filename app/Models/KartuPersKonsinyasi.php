<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KartuPersKonsinyasi extends Model
{
	protected $table = 't_kartuperskonsinyasi';
	protected $primaryKey = 'id';
	public $timestamps = false;

	protected $fillable = [
		'no_transaksi',
		'kode_produk',
		'tanggal',
		'masuk',
		'keluar',
		'sisa',
		'harga_konsinyasi',
		'lokasi',
		'keterangan',
	];

	public function produk()
	{
		return $this->belongsTo(ProdukKonsinyasi::class, 'kode_produk', 'kode_produk');
	}
}
