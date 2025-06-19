<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kartustok extends Model
{
    protected $table = 't_bahan'; // Jika ingin model ini mewakili bahan, sesuaikan jika tabel lain
    protected $primaryKey = 'kode_bahan';
    public $incrementing = false;
    public $timestamps = false;
}