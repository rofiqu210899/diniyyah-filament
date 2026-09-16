<?php

namespace App\Http\Controllers;

use App\Models\bukuinduk;
use App\Models\DataHafalan;
use App\Models\HafalanSantri;
use App\Models\Mustahiq;
use App\Models\SettingSertifikat;
use App\Models\TahunAjaran;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class SertifikatController extends Controller
{
    /**
     * Cetak Sertifikat Satuan
     */
    public function printSingle(Request $request, int $santriId)
    {
        $santri = bukuinduk::with(['madin', 'unitSekolah', 'Funkelurahan', 'Funkecamatan', 'Funkabupaten', 'Funprovinsi'])
            ->findOrFail($santriId);

        $tahunAjaranId = (int) $request->input('tahun_ajaran_id', TahunAjaran::getAktif()?->id);
        $tahunAjaran = TahunAjaran::find($tahunAjaranId) ?? TahunAjaran::getAktif();
        $tahunAjaranNama = $tahunAjaran ? $tahunAjaran->nama_tahun_ajaran : '-';

        $settingId = $request->input('setting_id');
        $setting = $settingId ? SettingSertifikat::find($settingId) : SettingSertifikat::getAktifSetting($tahunAjaranId);
        if (!$setting) {
            $setting = SettingSertifikat::getAktifSetting();
        }

        $mustahiq = Mustahiq::where('mkls', $santri->mkls)
            ->where('mbag', $santri->mbag)
            ->where('tkt', $santri->tkt)
            ->where('jk', $santri->jk)
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->value('nama_mustahiq') ?? '-';

        $students = [
            [
                'santri' => $santri,
                'mustahiq' => $mustahiq,
                'tahun_ajaran_nama' => $tahunAjaranNama,
            ]
        ];

        [$paperWidthMm, $paperHeightMm] = $setting->getPaperDimensions();
        $cssPaperSize = $setting->getCssPaperSize();

        $pageTitle = 'Sertifikat - ' . $santri->nm;

        return view('sertifikat.print', compact(
            'students',
            'setting',
            'paperWidthMm',
            'paperHeightMm',
            'cssPaperSize',
            'pageTitle'
        ));
    }

    /**
     * Cetak Sertifikat Kolektif
     */
    public function printCollective(Request $request)
    {
        $tahunAjaranId = (int) $request->input('tahun_ajaran_id', TahunAjaran::getAktif()?->id);
        $tahunAjaran = TahunAjaran::find($tahunAjaranId) ?? TahunAjaran::getAktif();
        $tahunAjaranNama = $tahunAjaran ? $tahunAjaran->nama_tahun_ajaran : '-';

        $settingId = $request->input('setting_id');
        $setting = $settingId ? SettingSertifikat::find($settingId) : SettingSertifikat::getAktifSetting($tahunAjaranId);
        if (!$setting) {
            $setting = SettingSertifikat::getAktifSetting();
        }

        $idsParam = $request->input('ids');
        $tkt = $request->input('tkt');
        $mkls = $request->input('mkls');
        $mbag = $request->input('mbag');
        $jk = $request->input('jk');

        $query = bukuinduk::with(['madin', 'unitSekolah', 'Funkelurahan', 'Funkecamatan', 'Funkabupaten', 'Funprovinsi'])
            ->where('deleted', 0);

        if (!empty($idsParam)) {
            $ids = array_filter(explode(',', (string) $idsParam));
            $query->whereIn('id', $ids);
        } else {
            if (!empty($tkt)) {
                $query->where('tkt', $tkt);
            }
            if (!empty($mkls)) {
                $query->where('mkls', $mkls);
            }
            if (!empty($mbag)) {
                $query->where('mbag', $mbag);
            }
            if (!empty($jk)) {
                $query->where('jk', $jk);
            }
        }

        $rawStudents = $query->orderBy('mkls')->orderBy('mbag')->orderBy('nm')->get();

        // Saring santri yang sudah tuntas Wajib & Sunnah
        $students = [];
        $mustahiqCache = [];

        foreach ($rawStudents as $santri) {
            // Cek kriteria tuntas
            if ($this->isSantriTuntas($santri, $tahunAjaranId)) {
                $cacheKey = "{$santri->mkls}_{$santri->mbag}_{$santri->tkt}_{$santri->jk}_{$tahunAjaranId}";
                if (!isset($mustahiqCache[$cacheKey])) {
                    $mustahiqCache[$cacheKey] = Mustahiq::where('mkls', $santri->mkls)
                        ->where('mbag', $santri->mbag)
                        ->where('tkt', $santri->tkt)
                        ->where('jk', $santri->jk)
                        ->where('tahun_ajaran_id', $tahunAjaranId)
                        ->value('nama_mustahiq') ?? '-';
                }

                $students[] = [
                    'santri' => $santri,
                    'mustahiq' => $mustahiqCache[$cacheKey],
                    'tahun_ajaran_nama' => $tahunAjaranNama,
                ];
            }
        }

        if (empty($students)) {
            return response('<h3>Tidak ada data santri tuntas yang dapat dicetak sesuai filter ini.</h3><a href="javascript:history.back()">Kembali</a>', 404);
        }

        [$paperWidthMm, $paperHeightMm] = $setting->getPaperDimensions();
        $cssPaperSize = $setting->getCssPaperSize();
        $pageTitle = 'Cetak Sertifikat Kolektif - ' . count($students) . ' Santri';

        return view('sertifikat.print', compact(
            'students',
            'setting',
            'paperWidthMm',
            'paperHeightMm',
            'cssPaperSize',
            'pageTitle'
        ));
    }

    /**
     * Pratinjau Desain Pengaturan Sertifikat
     */
    public function previewSetting(Request $request, int $settingId)
    {
        $setting = SettingSertifikat::findOrFail($settingId);
        $tahunAjaranId = $setting->tahun_ajaran_id ?: TahunAjaran::getAktif()?->id;
        $tahunAjaran = TahunAjaran::find($tahunAjaranId);
        $tahunAjaranNama = $tahunAjaran ? $tahunAjaran->nama_tahun_ajaran : (date('Y') . '/' . (date('Y') + 1));

        // Buat mock/dummy santri jika database kosong, atau ambil santri contoh
        $sampleSantri = bukuinduk::with(['madin', 'unitSekolah', 'Funkelurahan', 'Funkecamatan', 'Funkabupaten', 'Funprovinsi'])->first();

        if (!$sampleSantri) {
            $sampleSantri = new bukuinduk([
                'noin' => '2024001',
                'nm' => 'AHMAD MUHAMMAD AL-FATIH',
                'jk' => 1,
                'mkls' => 1,
                'mbag' => 'A',
                'nayah' => 'H. Abdullah',
            ]);
        }

        $students = [
            [
                'santri' => $sampleSantri,
                'mustahiq' => 'Ust. M. Ridwan, S.Pd',
                'tahun_ajaran_nama' => $tahunAjaranNama,
            ]
        ];

        [$paperWidthMm, $paperHeightMm] = $setting->getPaperDimensions();
        $cssPaperSize = $setting->getCssPaperSize();
        $pageTitle = 'Pratinjau Desain: ' . $setting->nama_setting;

        return view('sertifikat.print', compact(
            'students',
            'setting',
            'paperWidthMm',
            'paperHeightMm',
            'cssPaperSize',
            'pageTitle'
        ));
    }

    /**
     * Helper evaluasi apakah santri tuntas Wajib & Sunnah
     */
    public static function isSantriTuntas(bukuinduk $santri, ?int $tahunAjaranId = null): bool
    {
        $taId = $tahunAjaranId ?: TahunAjaran::getAktif()?->id;
        if (!$taId) return false;

        // Cek target hafalan kelas santri
        $targetWajibCount = DataHafalan::where('tkt', $santri->tkt)
            ->where('mkls', $santri->mkls)
            ->where('kriteria', 'Wajib')
            ->count();

        $targetSunnahCount = DataHafalan::where('tkt', $santri->tkt)
            ->where('mkls', $santri->mkls)
            ->where('kriteria', 'Sunnah')
            ->count();

        // Jika tidak ada target hafalan sama sekali
        if ($targetWajibCount === 0 && $targetSunnahCount === 0) {
            return false;
        }

        // Cek hafalan yang diselesaikan santri di tahun ajaran ini
        $hafalanSantriIds = HafalanSantri::where('santri_id', $santri->id)
            ->where('tahun_ajaran_id', $taId)
            ->where('tkt', $santri->tkt)
            ->where('mkls', $santri->mkls)
            ->pluck('data_hafalan_id')
            ->toArray();

        if (empty($hafalanSantriIds)) {
            return false;
        }

        // Hitung wajib yang diselesaikan
        $completedWajibCount = 0;
        if ($targetWajibCount > 0) {
            $completedWajibCount = DataHafalan::whereIn('id', $hafalanSantriIds)
                ->where('kriteria', 'Wajib')
                ->count();
            if ($completedWajibCount < $targetWajibCount) {
                return false;
            }
        }

        // Hitung sunnah yang diselesaikan (minimal 1 selesai)
        $completedSunnahCount = 0;
        if ($targetSunnahCount > 0) {
            $completedSunnahCount = DataHafalan::whereIn('id', $hafalanSantriIds)
                ->where('kriteria', 'Sunnah')
                ->count();
            if ($completedSunnahCount < 1) {
                return false;
            }
        }

        return true;
    }
}
