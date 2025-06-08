<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JurnalUmum extends Model
{
    protected $table = 't_jurnal_umum';
    protected $primaryKey = 'id_jurnal';
    public $timestamps = false;
    protected $fillable = ['tanggal', 'keterangan', 'nomor_bukti'];
}