<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penyesuaian extends Model
{
    protected $table = 't_penyesuaian';
    protected $primaryKey = 'no_penyesuaian';
    public $incrementing = false; // karena primary key bukan auto increment
    protected $keyType = 'string';

    protected $fillable = [
        'no_penyesuaian',
        'tanggal',
        'keterangan',
    ];
}