<?php

namespace App\Listeners;

use App\Models\TahunAjaran;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Carbon;

class CheckTahunAjaranOnLogin
{
    /**
     * Handle the event.
     *
     * Setiap kali user login, cek apakah sudah melewati 30 Juni.
     * Jika sudah, nonaktifkan tahun ajaran yang masih aktif.
     */
    public function handle(Login $event): void
    {
        $today = Carbon::today();

        // Jika sudah melewati 30 Juni (Juli ke atas), nonaktifkan tahun ajaran aktif
        if ($today->month < 7) {
            return;
        }

        $aktif = TahunAjaran::where('is_aktif', true)->first();

        if (!$aktif) {
            return;
        }

        // Parse tahun akhir dari nama tahun ajaran (format: "2025-2026" → 2026)
        // Nonaktifkan jika kita sudah melewati 30 Juni tahun akhir
        $parts = explode('-', $aktif->nama_tahun_ajaran);
        $tahunAkhir = isset($parts[1]) ? (int) trim($parts[1]) : null;

        // Jika tahun akhir cocok dengan tahun sekarang dan sudah lewat Juni → nonaktifkan
        if ($tahunAkhir && $tahunAkhir <= $today->year) {
            $aktif->update(['is_aktif' => false]);
        }
    }
}
