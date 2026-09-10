<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('setting_sertifikats', function (Blueprint $table) {
            if (!Schema::hasColumn('setting_sertifikats', 'tanggal_hijriah')) {
                $table->string('tanggal_hijriah')->nullable()->default('6 Romadhon 1447 H.');
            }
            if (!Schema::hasColumn('setting_sertifikats', 'atas_prestasinya')) {
                $table->text('atas_prestasinya')->nullable();
            }
            if (!Schema::hasColumn('setting_sertifikats', 'show_foto_box')) {
                $table->boolean('show_foto_box')->default(true);
            }
            if (!Schema::hasColumn('setting_sertifikats', 'foto_box_label')) {
                $table->string('foto_box_label')->default('Foto 3x4');
            }
            if (!Schema::hasColumn('setting_sertifikats', 'label_mengetahui')) {
                $table->string('label_mengetahui')->default('Mengetahui,');
            }

            // TTD 1 (Atas Kanan / PKM Muhafadhoh)
            if (!Schema::hasColumn('setting_sertifikats', 'show_ttd_1')) {
                $table->boolean('show_ttd_1')->default(true);
                $table->string('ttd_1_jabatan')->nullable()->default('PKM. Muhafadhoh');
                $table->string('ttd_1_nama')->nullable()->default('ANDIKO DWI SAPUTRA, S.T.T');
                $table->string('ttd_1_nip')->nullable();
                $table->string('ttd_1_image_path')->nullable();
            }

            // TTD 2 (Bawah Kiri / Kabid Pendidikan)
            if (!Schema::hasColumn('setting_sertifikats', 'show_ttd_2')) {
                $table->boolean('show_ttd_2')->default(true);
                $table->string('ttd_2_jabatan')->nullable()->default('Kabid. Pendidikan dan Pengajaran');
                $table->string('ttd_2_nama')->nullable()->default('DR. KH. ABDUL KHOLIQ SYAFAAT, MA.');
                $table->string('ttd_2_nip')->nullable();
                $table->string('ttd_2_image_path')->nullable();
            }

            // TTD 3 (Bawah Kanan / Kepala Madrasah)
            if (!Schema::hasColumn('setting_sertifikats', 'show_ttd_3')) {
                $table->boolean('show_ttd_3')->default(true);
                $table->string('ttd_3_jabatan')->nullable()->default('Kepala Madrasah');
                $table->string('ttd_3_nama')->nullable()->default('INDY NAJMU TSAQIB, S.Pd.I');
                $table->string('ttd_3_nip')->nullable();
                $table->string('ttd_3_image_path')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('setting_sertifikats', function (Blueprint $table) {
            $table->dropColumn([
                'tanggal_hijriah',
                'atas_prestasinya',
                'show_foto_box',
                'foto_box_label',
                'label_mengetahui',
                'show_ttd_1',
                'ttd_1_jabatan',
                'ttd_1_nama',
                'ttd_1_nip',
                'ttd_1_image_path',
                'show_ttd_2',
                'ttd_2_jabatan',
                'ttd_2_nama',
                'ttd_2_nip',
                'ttd_2_image_path',
                'show_ttd_3',
                'ttd_3_jabatan',
                'ttd_3_nama',
                'ttd_3_nip',
                'ttd_3_image_path',
            ]);
        });
    }
};
