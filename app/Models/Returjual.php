<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Returjual extends Model
{
    protected $table = 't_returjual';
    protected $primaryKey = 'no_returjual';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'no_returjual',
        'no_jual',
        'tanggal_returjual',
        'kode_pelanggan',
        'jenis_retur',
        'total_nilai_retur',
        'keterangan',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(\App\Models\Pelanggan::class, 'kode_pelanggan', 'kode_pelanggan');
    }

    public function penjualan()
    {
        return $this->belongsTo(\App\Models\Penjualan::class, 'no_jual', 'no_jual');
    }

    public function details()
    {
        // Perbaiki foreign key dari 'no_returjal' menjadi 'no_returjual'
        return $this->hasMany(ReturjualDetail::class, 'no_returjual', 'no_returjual');
    }

    public function getDetailPenjualan($no_jual)
    {
        $details = \DB::table('t_penjualan_detail')
            ->join('t_produk', 't_penjualan_detail.kode_produk', '=', 't_produk.kode_produk')
            ->where('t_penjualan_detail.no_jual', $no_jual)
            ->select('t_penjualan_detail.*', 't_produk.nama_produk')
            ->get();

        return response()->json(['details' => $details]);
    }
}

class ReturjualDetail extends Model
{
    protected $table = 't_returjual_detail';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'no_returjual',
        'kode_produk',
        'jumlah_retur',
        'harga_satuan',
        'alasan',
        'subtotal',
    ];

    public function returjual()
    {
        return $this->belongsTo(Returjual::class, 'no_returjual', 'no_returjual');
    }
    public function produk()
    {
        return $this->belongsTo(\App\Models\Produk::class, 'kode_produk', 'kode_produk');
    }
}