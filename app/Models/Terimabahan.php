<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TerimaBahan extends Model
{
    protected $table = 't_terimabahan';
    protected $primaryKey = 'no_terima_bahan';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'no_terima_bahan', 'no_order_beli', 'tanggal_terima', 'kode_supplier'
    ];

    // Relasi manual ke detail tanpa model terpisah
    public function details()
    {
        return DB::table('t_terimab_detail')
            ->where('no_terima_bahan', $this->no_terima_bahan)
            ->get();
    }
    protected $dates = ['tanggal_terima']; 
// atau pada Laravel versi lebih baru
protected $casts = [
    'tanggal_terima' => 'date',
];
}