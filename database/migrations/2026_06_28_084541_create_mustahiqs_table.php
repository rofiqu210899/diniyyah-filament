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
        Schema::create('mustahiqs', function (Blueprint $table) {
            $table->id();

            // Nama Mustahiq
            $table->string('nama_mustahiq', 100)->nullable();

            // Kelas (1,2,3,4,5,6)
            $table->tinyInteger('mkls')->nullable();

            // Bagian (A,B,C,...)
            $table->string('mbag', 2)->nullable();

            // Jenjang Madin
            // 1 = ULA
            // 2 = WUSTHO
            // 3 = ULYA
            $table->tinyInteger('tkt')->nullable();

            $table->timestamps();

            // Satu kelas hanya memiliki satu mustahiq
            $table->unique([
                'mkls',
                'mbag',
                'tkt'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mustahiqs');
    }
};
