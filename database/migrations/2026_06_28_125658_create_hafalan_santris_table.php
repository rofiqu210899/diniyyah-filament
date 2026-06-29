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
        Schema::create('hafalan_santris', function (Blueprint $table) {
            $table->id();

            // Santri (bukuinduk)
            $table->unsignedBigInteger('santri_id');

            // Data Hafalan yang diselesaikan
            $table->unsignedBigInteger('data_hafalan_id');

            // Tahun Ajaran
            $table->unsignedBigInteger('tahun_ajaran_id');

            // Catatan Historis saat menghafal
            $table->tinyInteger('mkls')->nullable(); // Kelas Diniyyah (1,2,3,4,5,6)
            $table->tinyInteger('tkt')->nullable();  // Jenjang Diniyyah (1,2,3)
            $table->string('kls', 50)->nullable();   // Kelas Kurikulum formal (kls)
            $table->string('unit', 50)->nullable();  // Unit Sekolah saat menghafal

            $table->timestamps();

            // Satu santri hanya bisa menyelesaikan satu hafalan per tahun ajaran
            $table->unique(['santri_id', 'data_hafalan_id', 'tahun_ajaran_id'], 'hafalan_santri_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hafalan_santris');
    }
};
