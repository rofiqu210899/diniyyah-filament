<?php

namespace App\Console\Commands;

use App\Models\TahunAjaran;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class DeactivateTahunAjaran extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'tahunajaran:deactivate';

    /**
     * The console command description.
     */
    protected $description = 'Nonaktifkan tahun ajaran yang aktif secara otomatis di akhir bulan Juni (tanggal 30 Juni)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = Carbon::today();

        // Cek apakah hari ini adalah 30 Juni
        if ($today->month !== 6 || $today->day !== 30) {
            $this->info('Bukan tanggal 30 Juni. Tidak ada perubahan.');
            return self::SUCCESS;
        }

        $aktif = TahunAjaran::where('is_aktif', true)->first();

        if (!$aktif) {
            $this->info('Tidak ada tahun ajaran yang aktif.');
            return self::SUCCESS;
        }

        $aktif->update(['is_aktif' => false]);

        $this->info("Tahun ajaran \"{$aktif->nama_tahun_ajaran}\" telah dinonaktifkan secara otomatis (30 Juni {$today->year}).");

        return self::SUCCESS;
    }
}
