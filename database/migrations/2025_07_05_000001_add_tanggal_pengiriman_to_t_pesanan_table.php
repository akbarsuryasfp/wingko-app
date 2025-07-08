<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('t_pesanan', function (Blueprint $table) {
            if (!Schema::hasColumn('t_pesanan', 'tanggal_pengiriman')) {
                $table->date('tanggal_pengiriman')->nullable()->after('tanggal_pesanan');
            }
        });
    }

    public function down()
    {
        Schema::table('t_pesanan', function (Blueprint $table) {
            if (Schema::hasColumn('t_pesanan', 'tanggal_pengiriman')) {
                $table->dropColumn('tanggal_pengiriman');
            }
        });
    }
};
