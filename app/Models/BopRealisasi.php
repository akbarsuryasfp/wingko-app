<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BopRealisasi extends Model
{
    protected $table = 't_bop_realisasi';
    protected $guarded = [];
    public $timestamps = false;

    public function bop()
    {
        return $this->belongsTo(BOP::class, 'kode_bop', 'kode_bop');
    }
}