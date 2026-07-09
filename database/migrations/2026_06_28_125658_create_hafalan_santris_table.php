<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('hafalan_santris', function (Blueprint $table) {
            $table->id();

            
            $table->unsignedBigInteger('santri_id');

            
            $table->unsignedBigInteger('data_hafalan_id');

            
            $table->unsignedBigInteger('tahun_ajaran_id');

            
            $table->tinyInteger('mkls')->nullable(); 
            $table->tinyInteger('tkt')->nullable();  
            $table->string('kls', 50)->nullable();   
            $table->string('unit', 50)->nullable();  

            $table->timestamps();

            
            $table->unique(['santri_id', 'data_hafalan_id', 'tahun_ajaran_id'], 'hafalan_santri_unique');
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('hafalan_santris');
    }
};
