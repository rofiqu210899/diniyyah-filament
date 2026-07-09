<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    
    public function up(): void
    {
        
        DB::table('mustahiqs')->truncate();

        Schema::table('mustahiqs', function (Blueprint $table) {
            
            $table->dropUnique(['mkls', 'mbag', 'tkt']);

            
            $table->unsignedBigInteger('tahun_ajaran_id')->after('tkt');

            
            $table->tinyInteger('jk')->after('tahun_ajaran_id');

            
            
            $table->unique(
                ['mkls', 'mbag', 'tkt', 'jk', 'tahun_ajaran_id'],
                'mustahiq_kelas_jk_tahun_unique'
            );
        });
    }

    
    public function down(): void
    {
        Schema::table('mustahiqs', function (Blueprint $table) {
            $table->dropUnique('mustahiq_kelas_jk_tahun_unique');
            $table->dropColumn(['tahun_ajaran_id', 'jk']);

            
            $table->unique(['mkls', 'mbag', 'tkt']);
        });
    }
};
