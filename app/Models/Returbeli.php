<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturBeli extends Model
{
    protected $table = 't_returbeli';
    protected $primaryKey = 'no_retur_beli';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'no_retur_beli',
        'no_pembelian',
        'tanggal_retur_beli',
        'kode_supplier',
        'total_retur',
        'keterangan',
    ];

    // Relasi ke detail retur
    public function detail()
    {
        return $this->hasMany(ReturBeliDetail::class, 'no_retur_beli', 'no_retur_beli');
    }

    // Relasi ke supplier
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'kode_supplier', 'kode_supplier');
    }

    // Relasi ke pembelian
    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class, 'no_pembelian', 'no_pembelian');
    }
    public function retur()
        {
            return $this->belongsTo(ReturBeli::class, 'no_retur_beli', 'no_retur_beli');
        }

    public function bahan()
        {
            return $this->belongsTo(Bahan::class, 'kode_bahan', 'kode_bahan');
        }
    }
