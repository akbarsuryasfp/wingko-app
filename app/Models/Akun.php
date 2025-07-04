<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Akun extends Model
{
    protected $table = 't_akun';
    protected $primaryKey = 'kode_akun';
    public $timestamps = false;
    protected $fillable = ['kode_akun', 'nama_akun', 'id_kategori', 'tipe_laporan'];

    public function jurnalDetails()
    {
        return $this->hasMany(\App\Models\JurnalDetail::class, 'kode_akun', 'kode_akun');
    }
}