<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsetTetap extends Model
{
    protected $table = 't_aset_tetap';
    protected $primaryKey = 'kode_aset_tetap';
    public $incrementing = false;
    protected $fillable = [
        'kode_aset_tetap', 'nama_aset', 'tanggal_beli', 'harga_perolehan',
        'umur_ekonomis', 'nilai_sisa', 'keterangan'
    ];
}