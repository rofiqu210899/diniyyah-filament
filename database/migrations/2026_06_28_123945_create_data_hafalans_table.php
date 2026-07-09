<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('data_hafalans', function (Blueprint $table) {
            $table->id();

            
            $table->tinyInteger('mkls');

            
            $table->tinyInteger('tkt');

            
            $table->string('nama_hafalan', 150);

            
            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('data_hafalans');
    }
};
