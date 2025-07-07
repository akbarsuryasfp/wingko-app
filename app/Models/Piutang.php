<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Piutang extends Model
{
    protected $table = 't_piutang';
    protected $primaryKey = 'no_piutang';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'no_piutang',
        'no_jual',
        'kode_pelanggan',
        'total_tagihan',
        'sisa_piutang',
        'total_bayar',
        'status_piutang',
        'tanggal_jatuh_tempo',
    ];

    public function index()
    {
        $penjualan = DB::table('t_penjualan')
            ->where('status_pembayaran', '!=', 'lunas')
            ->orderBy('no_jual')
            ->get();

        $piutangs = [];
        $no = 1;
        foreach ($penjualan as $p) {
            $piutangs[] = (object)[
                'no_piutang' => 'PI' . str_pad($no++, 6, '0', STR_PAD_LEFT),
                'no_jual' => $p->no_jual,
                'kode_pelanggan' => $p->kode_pelanggan,
                'total_tagihan' => $p->total_jual,
                'sisa_piutang' => $p->piutang,
                'total_bayar' => $p->total_bayar ?? 0,
                'status_piutang' => $p->status_pembayaran,
                'tanggal_jatuh_tempo' => $p->tanggal_jatuh_tempo ?? '',
            ];
        }

        return view('piutang.index', compact('piutangs'));
    }
}