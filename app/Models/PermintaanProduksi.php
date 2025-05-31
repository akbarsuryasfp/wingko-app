<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PermintaanProduksi extends Model
{
    // Nama tabel dalam database
    protected $table = 't_permintaan_produksi';

    // Primary key-nya (jika bukan 'id')
    protected $primaryKey = 'kode_permintaan_produksi';

    // Tidak pakai timestamps (created_at, updated_at)
    public $timestamps = false;

    // Kolom yang bisa diisi (fillable)
    protected $fillable = [
        'kode_permintaan_produksi',
        'tanggal',
        'keterangan',
        'status'
    ];

    // Relasi ke detail (satu permintaan memiliki banyak detail produk)
    public function details(): HasMany
    {
        return $this->hasMany(PermintaanProduksiDetail::class, 'kode_permintaan_produksi', 'kode_permintaan_produksi');
    }

    public $incrementing = false;
    protected $keyType = 'string';

    public static function withStok($kebutuhan)
    {
        foreach ($kebutuhan as &$b) {
            // Ganti 'bahan' dan 'kode_bahan' sesuai dengan struktur tabel Anda
            $stok = \DB::table('bahan')->where('kode_bahan', $b['kode_bahan'])->value('stok') ?? 0;
            $b['stok'] = $stok;
            $b['status'] = $stok >= $b['jumlah'] ? 'Cukup' : 'Kurang';
        }
        unset($b);

        return $kebutuhan;
    }
}
