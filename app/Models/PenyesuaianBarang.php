<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class PenyesuaianBarang extends Model
{
    protected $table = 't_penyesuaian';
    protected $primaryKey = 'no_penyesuaian';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['no_penyesuaian', 'tanggal', 'keterangan'];

    // Ambil detail sebagai array (tanpa model detail)
    public function detailItems()
    {
        return DB::table('t_penyesuaian_detail')
            ->where('no_penyesuaian', $this->no_penyesuaian)
            ->get();
    }
}