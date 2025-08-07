<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BOP extends Model
{
    protected $table = 't_bop';
    protected $primaryKey = 'kode_bop';
    protected $guarded = [];
    public $timestamps = false;
    
}
