<?php

namespace App\Helpers;

use App\Models\Akun;

class AkunHelper
{
    public static function getIdAkun($kode_akun)
    {
        return Akun::where('kode_akun', $kode_akun)->value('kode_akun');
    }
}