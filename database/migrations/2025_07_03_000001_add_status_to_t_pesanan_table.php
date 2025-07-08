<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('t_pesanan', function (Blueprint $table) {
            if (!Schema::hasColumn('t_pesanan', 'status')) {
                $table->string('status', 20)->default('Belum Diambil')->after('keterangan');
            }
        });
    }

    public function down()
    {
        Schema::table('t_pesanan', function (Blueprint $table) {
            if (Schema::hasColumn('t_pesanan', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
