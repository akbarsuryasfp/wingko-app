<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JurnalDetail extends Model
{
    protected $table = 't_jurnal_detail';
    protected $primaryKey = 'no_jurnal_detail';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $fillable = ['no_jurnal_detail', 'no_jurnal', 'kode_akun', 'debit', 'kredit', 'jenis_jurnal'];
}