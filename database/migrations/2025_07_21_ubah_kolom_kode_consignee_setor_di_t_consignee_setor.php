<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('t_consignee_setor', function (Blueprint $table) {
            $table->string('kode_consignee_setor', 30)->change();
        });
    }
    public function down()
    {
        Schema::table('t_consignee_setor', function (Blueprint $table) {
            $table->string('kode_consignee_setor', 10)->change(); // Kembalikan ke ukuran awal jika perlu
        });
    }
};
