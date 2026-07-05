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
        // 1. Hapus data lama (fresh start)
        DB::table('mustahiqs')->truncate();

        Schema::table('mustahiqs', function (Blueprint $table) {
            // 2. Drop unique constraint lama
            $table->dropUnique(['mkls', 'mbag', 'tkt']);

            // 3. Tambah kolom tahun_ajaran_id
            $table->unsignedBigInteger('tahun_ajaran_id')->after('tkt');

            // 4. Tambah kolom jk (1=Putra, 2=Putri)
            $table->tinyInteger('jk')->after('tahun_ajaran_id');

            // 5. Buat unique constraint baru
            // Satu kelas+bagian+jenjang+jk hanya boleh punya 1 mustahiq per tahun ajaran
            $table->unique(
                ['mkls', 'mbag', 'tkt', 'jk', 'tahun_ajaran_id'],
                'mustahiq_kelas_jk_tahun_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mustahiqs', function (Blueprint $table) {
            $table->dropUnique('mustahiq_kelas_jk_tahun_unique');
            $table->dropColumn(['tahun_ajaran_id', 'jk']);

            // Restore unique constraint lama
            $table->unique(['mkls', 'mbag', 'tkt']);
        });
    }
};
