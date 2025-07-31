<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalProduksi extends Model
{
    protected $table = 't_jadwal_produksi';
    protected $primaryKey = 'no_jadwal';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'no_jadwal',
        'tanggal_jadwal',
        'keterangan',
    ];

    public function details()
    {
        return $this->hasMany(JadwalProduksiDetail::class, 'no_jadwal', 'no_jadwal');
    }

        public function show($kode)
    {
        $jadwal = \App\Models\JadwalProduksi::with('details.produk.resep.details.bahan')->findOrFail($kode);

        // Hitung estimasi bahan
        $kebutuhan = [];

        foreach ($jadwal->details as $detail) {
            $produk = $detail->produk;
            $jumlah = $detail->jumlah;

            if ($produk && $produk->resep) {
                foreach ($produk->resep->details as $rdetail) {
                    $kode_bahan = $rdetail->kode_bahan;
                    $total = $jumlah * $rdetail->jumlah_kebutuhan;

                    if (!isset($kebutuhan[$kode_bahan])) {
                        $kebutuhan[$kode_bahan] = [
                            'nama_bahan' => $rdetail->bahan->nama_bahan ?? $kode_bahan,
                            'jumlah' => 0,
                            'satuan' => $rdetail->satuan,
                        ];
                    }

                    $kebutuhan[$kode_bahan]['jumlah'] += $total;
                }
            }
        }

        return view('jadwal.show', compact('jadwal', 'kebutuhan'));
    }


}
