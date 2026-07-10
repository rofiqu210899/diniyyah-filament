<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class TahunAjaran extends Model
{
    use HasFactory, LogsActivity;

    protected $guarded = [];

    protected $casts = [
        'is_aktif' => 'boolean',
    ];

    /**
     * Set tahun ajaran ini sebagai aktif,
     * dan nonaktifkan semua yang lain.
     */
    public function setAktif(): void
    {
        // Nonaktifkan semua tahun ajaran
        static::where('is_aktif', true)->update(['is_aktif' => false]);

        // Aktifkan yang ini
        $this->update(['is_aktif' => true]);
    }

    /**
     * Ambil tahun ajaran yang sedang aktif.
     */
    public static function getAktif(): ?self
    {
        return static::where('is_aktif', true)->first();
    }

    /**
     * Label lengkap: "2025/2026 - Ganjil"
     */
    public function getLabelLengkapAttribute(): string
    {
        return "{$this->nama_tahun_ajaran}";
    }
}
