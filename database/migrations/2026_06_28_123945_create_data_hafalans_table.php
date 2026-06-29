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
        Schema::create('data_hafalans', function (Blueprint $table) {
            $table->id();

            // Kelas (1,2,3,4,5,6)
            $table->tinyInteger('mkls');

            // Jenjang Madin (1=ULA, 2=WUSTHO, 3=ULYA, dst)
            $table->tinyInteger('tkt');

            // Nama Hafalan (contoh: Juz Amma, Surat Al-Baqarah, dll)
            $table->string('nama_hafalan', 150);

            // Keterangan tambahan (opsional)
            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_hafalans');
    }
};
