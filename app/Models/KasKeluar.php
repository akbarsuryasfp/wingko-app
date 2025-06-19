<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kaskeluar extends Model
{
    protected $table = 't_kaskeluar';
    protected $primaryKey = 'no_BKK';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_jurnal', 'no_BKK', 'tanggal', 'no_referensi', 'jenis_kas', 'kode_akun', 'jumlah', 'penerima', 'keterangan'
    ];
}