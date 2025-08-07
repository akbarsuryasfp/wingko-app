<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_consignee_setor', function (Blueprint $table) {
            $table->string('kode_consignee', 20);
            $table->string('kode_produk', 20);
            $table->integer('jumlah_setor')->default(0);
            $table->timestamps();

            $table->primary(['kode_consignee', 'kode_produk']);
            $table->index('kode_consignee');
            $table->index('kode_produk');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_consignee_setor');
    }
};
