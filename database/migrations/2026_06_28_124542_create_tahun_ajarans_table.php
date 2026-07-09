<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('tahun_ajarans', function (Blueprint $table) {
            $table->id();

            
            $table->string('nama_tahun_ajaran', 50);

            
            $table->boolean('is_aktif')->default(false);

            $table->timestamps();
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('tahun_ajarans');
    }
};
