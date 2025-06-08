<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JurnalDetail extends Model
{
    protected $table = 't_jurnal_detail';
    protected $primaryKey = 'id_detail';
    public $timestamps = false;
    protected $fillable = ['id_jurnal', 'id_akun', 'debit', 'kredit'];
}