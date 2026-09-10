<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class SettingSertifikat extends Model
{
    use HasFactory, LogsActivity;

    protected $guarded = [];

    protected $casts = [
        'use_current_date' => 'boolean',
        'show_foto_box' => 'boolean',
        'show_ttd_1' => 'boolean',
        'show_ttd_2' => 'boolean',
        'show_ttd_3' => 'boolean',
        'show_ttd_kiri' => 'boolean',
        'show_ttd_kanan' => 'boolean',
        'show_watermark' => 'boolean',
        'tanggal_terbit' => 'date',
        'margin_top' => 'float',
        'margin_right' => 'float',
        'margin_bottom' => 'float',
        'margin_left' => 'float',
        'line_spacing' => 'float',
        'custom_width' => 'float',
        'custom_height' => 'float',
        'nomor_start_sequence' => 'integer',
        'nomor_digit_padding' => 'integer',
    ];

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    /**
     * Dapatkan setting sertifikat aktif/default
     */
    public static function getAktifSetting(?int $tahunAjaranId = null): self
    {
        if ($tahunAjaranId) {
            $setting = static::where('tahun_ajaran_id', $tahunAjaranId)->first();
            if ($setting) return $setting;
        }

        $aktifTA = TahunAjaran::getAktif();
        if ($aktifTA) {
            $setting = static::where('tahun_ajaran_id', $aktifTA->id)->first();
            if ($setting) return $setting;
        }

        $first = static::first();
        if ($first) return $first;

        // Buat default jika belum ada sama sekali
        return static::create([
            'nama_setting' => 'Format Tanda Penghargaan',
            'paper_size' => 'F4',
            'custom_width' => 215,
            'custom_height' => 330,
            'orientation' => 'portrait',
            'margin_top' => 50,
            'margin_right' => 25,
            'margin_bottom' => 20,
            'margin_left' => 25,
            'font_family' => 'Times New Roman',
            'font_size_header' => 14,
            'font_size_title' => 20,
            'font_size_subtitle' => 11,
            'font_size_nama' => 12,
            'font_size_body' => 12,
            'font_size_footer' => 11,
            'line_spacing' => 1.2,
            'paragraph_spacing_before' => 3,
            'paragraph_spacing_after' => 6,
            'frame_border_style' => 'none',
            'kop_header_type' => 'none',
            'judul_sertifikat' => 'Tanda Penghargaan',
            'subjudul_sertifikat' => null,
            'nomor_format_template' => '51.2/[nomor]/E.24/MADINA/II/2026',
            'nomor_start_sequence' => 649,
            'nomor_digit_padding' => 0,
            'atas_prestasinya' => 'Hafal [hafalan]',
            'tempat_terbit' => 'Blokagung',
            'tanggal_hijriah' => '6 Romadhon 1447 H.',
            'use_current_date' => true,
            'show_foto_box' => true,
            'foto_box_label' => 'Foto 3x4',
            'show_ttd_1' => true,
            'ttd_1_jabatan' => 'PKM. Muhafadhoh',
            'ttd_1_nama' => 'ANDIKO DWI SAPUTRA, S.T.T',
            'label_mengetahui' => 'Mengetahui,',
            'show_ttd_2' => true,
            'ttd_2_jabatan' => 'Kabid. Pendidikan dan Pengajaran',
            'ttd_2_nama' => 'DR. KH. ABDUL KHOLIQ SYAFA\'AT, MA.',
            'show_ttd_3' => true,
            'ttd_3_jabatan' => 'Kepala Madrasah',
            'ttd_3_nama' => 'INDY NAJMU TSAQIB, S.Pd.I',
        ]);
    }

    /**
     * Format nomor sertifikat otomatis per urutan santri
     */
    public function formatNomorSertifikat(int $index = 0, array $replacements = []): string
    {
        $startSeq = $this->nomor_start_sequence ?: 1;
        $currentNumber = $startSeq + $index;

        if ($this->nomor_digit_padding && $this->nomor_digit_padding > 0) {
            $formattedNum = sprintf('%0' . (int)$this->nomor_digit_padding . 'd', $currentNumber);
        } else {
            $formattedNum = (string)$currentNumber;
        }

        $template = $this->nomor_format_template;
        if (empty($template)) {
            $template = ($this->nomor_prefix ?? '51.2/[nomor]/E.24/MADINA/II/2026') . ($this->nomor_suffix ?? '');
        }

        // Jika template tidak memiliki placeholder [nomor] maupun [urut], sisipkan nomor
        if (!str_contains($template, '[nomor]') && !str_contains($template, '[urut]')) {
            // Jika ada prefix atau format lama
            if (!empty($this->nomor_prefix)) {
                $template = $this->nomor_prefix . '[nomor]' . ($this->nomor_suffix ?? '');
            }
        }

        $romanMonths = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'];
        $currentMonthRoman = $romanMonths[(int)date('n')] ?? 'II';
        $currentYear = date('Y');

        $allReplacements = array_merge([
            '[nomor]' => $formattedNum,
            '[urut]' => $formattedNum,
            '[bulan_romawi]' => $currentMonthRoman,
            '[tahun]' => $currentYear,
        ], $replacements);

        return str_replace(array_keys($allReplacements), array_values($allReplacements), $template);
    }

    /**
     * Dapatkan ukuran kertas dalam mm / point untuk DomPDF atau CSS
     */
    public function getPaperDimensions(): array
    {
        $isLandscape = $this->orientation === 'landscape';

        $sizes = [
            'A4' => [210, 297],
            'F4' => [215, 330],
            'Folio' => [215, 330],
            'Letter' => [215.9, 279.4],
            'Legal' => [215.9, 355.6],
        ];

        if ($this->paper_size === 'Custom' && $this->custom_width && $this->custom_height) {
            $w = (float) $this->custom_width;
            $h = (float) $this->custom_height;
        } else {
            [$w, $h] = $sizes[$this->paper_size] ?? $sizes['F4'];
        }

        if ($isLandscape) {
            return [max($w, $h), min($w, $h)];
        }

        return [min($w, $h), max($w, $h)];
    }

    /**
     * Format CSS ukuran kertas (cth: '215mm 330mm')
     */
    public function getCssPaperSize(): string
    {
        [$w, $h] = $this->getPaperDimensions();
        return "{$w}mm {$h}mm";
    }
}
