<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Drop foreign key constraint di tabel lain
        DB::statement('ALTER TABLE t_jualkonsinyasimasuk_detail DROP FOREIGN KEY fk_detail_konsinyasimasuk');
        // 2. Samakan tipe kolom di kedua tabel (varchar(20) NOT NULL)
        Schema::table('t_konsinyasimasuk_detail', function (Blueprint $table) {
            $table->string('no_detailkonsinyasimasuk', 20)->nullable(false)->change();
        });
        Schema::table('t_jualkonsinyasimasuk_detail', function (Blueprint $table) {
            $table->string('no_detailkonsinyasimasuk', 20)->nullable(false)->change();
        });
        // 3. Pastikan primary key tetap ada di t_konsinyasimasuk_detail
        DB::statement('ALTER TABLE t_konsinyasimasuk_detail DROP PRIMARY KEY');
        DB::statement('ALTER TABLE t_konsinyasimasuk_detail ADD PRIMARY KEY (no_detailkonsinyasimasuk)');
        // 4. Tambahkan kembali foreign key constraint
        DB::statement('ALTER TABLE t_jualkonsinyasimasuk_detail ADD CONSTRAINT fk_detail_konsinyasimasuk FOREIGN KEY (no_detailkonsinyasimasuk) REFERENCES t_konsinyasimasuk_detail(no_detailkonsinyasimasuk)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback: drop foreign key, kembalikan tipe kolom jika perlu
        DB::statement('ALTER TABLE t_jualkonsinyasimasuk_detail DROP FOREIGN KEY fk_detail_konsinyasimasuk');
        // Tidak mengubah tipe kolom ke sebelumnya agar aman
    }
};
