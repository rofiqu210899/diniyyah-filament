<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('setting_sertifikats', function (Blueprint $table) {
            if (!Schema::hasColumn('setting_sertifikats', 'nomor_start_sequence')) {
                $table->integer('nomor_start_sequence')->default(1);
            }
            if (!Schema::hasColumn('setting_sertifikats', 'nomor_digit_padding')) {
                $table->integer('nomor_digit_padding')->default(0); // 0 = tanpa nol di depan (1, 2, 649), 3 = 001, 002
            }
            if (!Schema::hasColumn('setting_sertifikats', 'nomor_format_template')) {
                $table->string('nomor_format_template')->nullable()->default('51.2/[nomor]/E.24/MADINA/II/2026');
            }
        });
    }

    public function down(): void
    {
        Schema::table('setting_sertifikats', function (Blueprint $table) {
            $table->dropColumn([
                'nomor_start_sequence',
                'nomor_digit_padding',
                'nomor_format_template',
            ]);
        });
    }
};
