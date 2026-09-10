<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setting_sertifikats', function (Blueprint $table) {
            $table->id();
            $table->string('nama_setting')->default('Default Sertifikat');
            $table->foreignId('tahun_ajaran_id')->nullable()->constrained('tahun_ajarans')->nullOnDelete();

            // Ukuran Kertas & Tata Letak (Word-like Page Setup)
            $table->string('paper_size')->default('A4'); // A4, F4, Letter, Legal, Custom
            $table->decimal('custom_width', 8, 2)->nullable()->default(297); // in mm
            $table->decimal('custom_height', 8, 2)->nullable()->default(210); // in mm
            $table->string('orientation')->default('landscape'); // landscape, portrait

            // Margin Kertas (mm)
            $table->decimal('margin_top', 6, 2)->default(15);
            $table->decimal('margin_right', 6, 2)->default(15);
            $table->decimal('margin_bottom', 6, 2)->default(15);
            $table->decimal('margin_left', 6, 2)->default(15);

            // Tipografi & Spasi (Typography & Paragraph Spacing)
            $table->string('font_family')->default('Times New Roman'); // Times New Roman, Arial, Georgia, Garamond, Scheherazade, Playfair Display, Cinzel, Merriweather, Roboto
            $table->integer('font_size_header')->default(16); // pt
            $table->integer('font_size_title')->default(24); // pt
            $table->integer('font_size_subtitle')->default(14); // pt
            $table->integer('font_size_nama')->default(22); // pt
            $table->integer('font_size_body')->default(12); // pt
            $table->integer('font_size_footer')->default(11); // pt
            $table->decimal('line_spacing', 4, 2)->default(1.15); // 1.0, 1.15, 1.5, 2.0
            $table->integer('paragraph_spacing_before')->default(4); // pt
            $table->integer('paragraph_spacing_after')->default(8); // pt

            // Bingkai & Desain (Frame/Border & Background)
            $table->string('frame_border_style')->default('classic_gold'); // classic_gold, islamic_green, double_border, modern_minimalist, custom_image, none
            $table->string('frame_background_image')->nullable();
            $table->string('bg_color')->default('#ffffff');
            $table->string('accent_color')->default('#198754');
            $table->boolean('show_watermark')->default(false);
            $table->string('watermark_image')->nullable();

            // Kop & Identitas Lembaga
            $table->string('kop_header_type')->default('text'); // text, image, none
            $table->string('kop_image_path')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('instansi_nama')->default('MADRASAH DINIYYAH AL-AMIRIYYAH');
            $table->string('instansi_subnama')->nullable()->default('Pondok Pesantren Darussalam Blokagung');
            $table->text('instansi_alamat')->nullable();

            // Format Nomor & Teks Sertifikat
            $table->string('nomor_prefix')->nullable()->default('MD.01/SY-TFZ/');
            $table->string('nomor_suffix')->nullable()->default('/VI/2026');
            $table->string('judul_sertifikat')->default('SYAHADAH HAFALAN');
            $table->string('subjudul_sertifikat')->nullable()->default('Tuntas Hafalan Wajib & Sunnah');
            $table->text('teks_pembuka')->nullable();
            $table->text('teks_keterangan')->nullable();

            // Penandatangan & Tempat Tanggal
            $table->string('tempat_terbit')->default('Blokagung');
            $table->date('tanggal_terbit')->nullable();
            $table->boolean('use_current_date')->default(true);

            // Penandatangan Kiri (Wali Kelas / Mustahiq)
            $table->boolean('show_ttd_kiri')->default(true);
            $table->string('ttd_kiri_jabatan')->nullable()->default('Wali Kelas / Mustahiq');
            $table->string('ttd_kiri_nama')->nullable()->default('[mustahiq]');
            $table->string('ttd_kiri_nip')->nullable();
            $table->string('ttd_kiri_image_path')->nullable();

            // Penandatangan Kanan (Kepala Madrasah)
            $table->boolean('show_ttd_kanan')->default(true);
            $table->string('ttd_kanan_jabatan')->nullable()->default('Kepala Madrasah Diniyyah');
            $table->string('ttd_kanan_nama')->nullable()->default('Ust. H. Syafii, S.Pd.I');
            $table->string('ttd_kanan_nip')->nullable();
            $table->string('ttd_kanan_image_path')->nullable();
            $table->string('stempel_image_path')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setting_sertifikats');
    }
};
