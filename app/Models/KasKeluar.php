<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KasKeluar extends Model
{
    protected $table = 't_jurnal_umum';
    protected $primaryKey = 'id_jurnal';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'id_jurnal', 'tanggal', 'keterangan', 'nomor_bukti'
    ];
}