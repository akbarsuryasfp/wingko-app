<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Dikosongkan/dinonaktifkan sementara agar tidak error
        // Schema::table('t_konsinyasikeluar', function (Blueprint $table) {
        //     $table->string('no_surat')->nullable()->after('no_konsinyasi');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_konsinyasikeluar', function (Blueprint $table) {
            $table->dropColumn('no_surat');
        });
    }
};
