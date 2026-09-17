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
//    function untuk cetak sertifikat siji siji
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

        // Ambil snapshot historis dari hafalan_santris untuk tahun ajaran ini
        $snapshot = HafalanSantri::where('santri_id', $santri->id)
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->with('jenjang')
            ->first();

        $mkls = $snapshot?->mkls ?? $santri->mkls;
        $tkt = $snapshot?->tkt ?? $santri->tkt;
        $jenjangNama = $snapshot?->jenjang?->madin ?? $santri->madin?->madin;

        $mustahiq = Mustahiq::where('mkls', $mkls)
            ->where('mbag', $santri->mbag)
            ->where('tkt', $tkt)
            ->where('jk', $santri->jk)
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->value('nama_mustahiq') ?? '-';

        $students = [
            [
                'santri' => $santri,
                'mustahiq' => $mustahiq,
                'tahun_ajaran_nama' => $tahunAjaranNama,
                'tahun_ajaran_id' => $tahunAjaranId,
                'mkls' => $mkls,
                'tkt' => $tkt,
                'mbag' => $santri->mbag,
                'jenjang_nama' => $jenjangNama,
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
            'pageTitle',
            'tahunAjaranId'
        ));
    }

    // function gae nyetak kolektif
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
                $query->where(function ($q) use ($tkt, $tahunAjaranId) {
                    $q->whereExists(function ($sub) use ($tkt, $tahunAjaranId) {
                        $sub->selectRaw(1)
                            ->from('hafalan_santris')
                            ->whereColumn('santri_id', 'bukuinduk.id')
                            ->where('tahun_ajaran_id', $tahunAjaranId)
                            ->where('tkt', $tkt);
                    })->orWhere(function ($fallback) use ($tkt, $tahunAjaranId) {
                        $fallback->whereNotExists(function ($sub) use ($tahunAjaranId) {
                            $sub->selectRaw(1)
                                ->from('hafalan_santris')
                                ->whereColumn('santri_id', 'bukuinduk.id')
                                ->where('tahun_ajaran_id', $tahunAjaranId);
                        })->where('tkt', $tkt);
                    });
                });
            }
            if (!empty($mkls)) {
                $query->where(function ($q) use ($mkls, $tahunAjaranId) {
                    $q->whereExists(function ($sub) use ($mkls, $tahunAjaranId) {
                        $sub->selectRaw(1)
                            ->from('hafalan_santris')
                            ->whereColumn('santri_id', 'bukuinduk.id')
                            ->where('tahun_ajaran_id', $tahunAjaranId)
                            ->where('mkls', $mkls);
                    })->orWhere(function ($fallback) use ($mkls, $tahunAjaranId) {
                        $fallback->whereNotExists(function ($sub) use ($tahunAjaranId) {
                            $sub->selectRaw(1)
                                ->from('hafalan_santris')
                                ->whereColumn('santri_id', 'bukuinduk.id')
                                ->where('tahun_ajaran_id', $tahunAjaranId);
                        })->where('mkls', $mkls);
                    });
                });
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
                $snapshot = HafalanSantri::where('santri_id', $santri->id)
                    ->where('tahun_ajaran_id', $tahunAjaranId)
                    ->with('jenjang')
                    ->first();

                $mkls = $snapshot?->mkls ?? $santri->mkls;
                $tkt = $snapshot?->tkt ?? $santri->tkt;
                $jenjangNama = $snapshot?->jenjang?->madin ?? $santri->madin?->madin;

                $cacheKey = "{$mkls}_{$santri->mbag}_{$tkt}_{$santri->jk}_{$tahunAjaranId}";
                if (!isset($mustahiqCache[$cacheKey])) {
                    $mustahiqCache[$cacheKey] = Mustahiq::where('mkls', $mkls)
                        ->where('mbag', $santri->mbag)
                        ->where('tkt', $tkt)
                        ->where('jk', $santri->jk)
                        ->where('tahun_ajaran_id', $tahunAjaranId)
                        ->value('nama_mustahiq') ?? '-';
                }

                $students[] = [
                    'santri' => $santri,
                    'mustahiq' => $mustahiqCache[$cacheKey],
                    'tahun_ajaran_nama' => $tahunAjaranNama,
                    'tahun_ajaran_id' => $tahunAjaranId,
                    'mkls' => $mkls,
                    'tkt' => $tkt,
                    'mbag' => $santri->mbag,
                    'jenjang_nama' => $jenjangNama,
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
            'pageTitle',
            'tahunAjaranId'
        ));
    }

    // freview sertifikat
    public function previewSetting(Request $request, int $settingId)
    {
        $setting = SettingSertifikat::findOrFail($settingId);
        $tahunAjaranId = $setting->tahun_ajaran_id ?: TahunAjaran::getAktif()?->id;
        $tahunAjaran = TahunAjaran::find($tahunAjaranId);
        $tahunAjaranNama = $tahunAjaran ? $tahunAjaran->nama_tahun_ajaran : (date('Y') . '/' . (date('Y') + 1));

       
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
                'tahun_ajaran_id' => $tahunAjaranId,
                'mkls' => $sampleSantri->mkls,
                'tkt' => $sampleSantri->tkt,
                'mbag' => $sampleSantri->mbag,
                'jenjang_nama' => $sampleSantri->madin?->madin ?? 'ULA',
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
            'pageTitle',
            'tahunAjaranId'
        ));
    }

    public static function isSantriTuntas(bukuinduk $santri, ?int $tahunAjaranId = null): bool
    {
        $taId = $tahunAjaranId ?: TahunAjaran::getAktif()?->id;
        if (!$taId) return false;

        // Ambil snapshot mkls & tkt dari hafalan_santris untuk tahun ajaran ini
        $snapshot = HafalanSantri::where('santri_id', $santri->id)
            ->where('tahun_ajaran_id', $taId)
            ->first();

        $mkls = $snapshot?->mkls ?? $santri->mkls;
        $tkt = $snapshot?->tkt ?? $santri->tkt;

        // Cek target hafalan kelas santri pada snapshot tersebut
        $targetWajibCount = DataHafalan::where('tkt', $tkt)
            ->where('mkls', $mkls)
            ->where('kriteria', 'Wajib')
            ->count();

        $targetSunnahCount = DataHafalan::where('tkt', $tkt)
            ->where('mkls', $mkls)
            ->where('kriteria', 'Sunnah')
            ->count();

        // Jika tidak ada target hafalan sama sekali
        if ($targetWajibCount === 0 && $targetSunnahCount === 0) {
            return false;
        }

        // Cek hafalan yang diselesaikan santri di tahun ajaran ini
        $hafalanSantriIds = HafalanSantri::where('santri_id', $santri->id)
            ->where('tahun_ajaran_id', $taId)
            ->where('tkt', $tkt)
            ->where('mkls', $mkls)
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
