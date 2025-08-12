<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JurnalUmum extends Model
{
    protected $table = 't_jurnal_umum';
    protected $primaryKey = 'no_jurnal';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $fillable = ['no_jurnal', 'tanggal', 'keterangan', 'nomor_bukti'];


    public function details()
    {
        return $this->hasMany(\App\Models\JurnalDetail::class, 'no_jurnal', 'no_jurnal');
    }
}