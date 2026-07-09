<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('mustahiqs', function (Blueprint $table) {
            $table->id();

            
            $table->string('nama_mustahiq', 100)->nullable();

            
            $table->tinyInteger('mkls')->nullable();

            
            $table->string('mbag', 2)->nullable();

            
            
            
            
            $table->tinyInteger('tkt')->nullable();

            $table->timestamps();

            
            $table->unique([
                'mkls',
                'mbag',
                'tkt'
            ]);
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('mustahiqs');
    }
};
