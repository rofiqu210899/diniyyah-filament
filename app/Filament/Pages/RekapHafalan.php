<?php

namespace App\Filament\Pages;

use App\Models\bukuinduk;
use App\Models\DataHafalan;
use App\Models\HafalanSantri;
use App\Models\jurusan;
use App\Models\kelas;
use App\Models\Madin;
use App\Models\TahunAjaran;
use App\Models\unit;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use App\Exports\RekapHafalanTableExport;
use Maatwebsite\Excel\Facades\Excel;

class RekapHafalan extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Hafalan';
    protected static ?string $title = 'Rekapitulasi Hafalan';
    protected static string $view = 'filament.pages.rekap-hafalan';
    protected static ?string $navigationGroup = 'Laporan';

    public ?array $data = [];

    /** Data hasil rekap untuk tabel */
    public array $hasilRekap = [];

    /** Header kolom hafalan */
    public array $headerHafalan = [];

    /** Kontrol tampilan card hasil */
    public bool $showHasil = false;

    /** Label info filter yang sedang aktif */
    public string $filterLabel = '';

    /** Jenis pendidikan yang sedang aktif (untuk conditional Blade) */
    public string $jenisPendidikan = '';

    public function mount(): void
    {
        $aktif = TahunAjaran::getAktif();
        $this->form->fill([
            'tahun_ajaran_id' => $aktif?->id,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Section::make('Filter Rekap Hafalan')
                    ->description('Pilih filter untuk menampilkan rekapan hafalan santri.')
                    ->icon('heroicon-o-funnel')
                    ->schema([

                        Select::make('tahun_ajaran_id')
                            ->label('Tahun Ajaran')
                            ->options(
                                TahunAjaran::orderByDesc('id')
                                    ->pluck('nama_tahun_ajaran', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(1),

                        Select::make('jenis_pendidikan')
                            ->label('Jenis Pendidikan')
                            ->options([
                                'kurikulum' => 'Kurikulum',
                                'madin' => 'Madin',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                // Reset field terkait saat jenis pendidikan berubah
                                $set('unit_id', null);
                                $set('jurusan_id', null);
                                $set('kelas_id', null);
                                $set('bagian', null);
                                $set('madin_mkls', null);
                                $set('madin_mbag', null);
                                $set('madin_tkt', null);
                                $set('madin_jk', null);
                                $this->showHasil = false;
                            })
                            ->columnSpan(1),

                        // ========================================
                        // FILTER KURIKULUM
                        // ========================================

                        Select::make('unit_id')
                            ->label('Unit')
                            ->options(
                                unit::orderBy('urut')->pluck('unit', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('jurusan_id', null);
                                $set('kelas_id', null);
                                $set('bagian', null);
                                $this->showHasil = false;
                            })
                            ->visible(fn(Get $get) => $get('jenis_pendidikan') === 'kurikulum')
                            ->columnSpan(1),

                        Select::make('jurusan_id')
                            ->label('Jurusan')
                            ->options(function (Get $get) {
                                $unitId = $get('unit_id');
                                if (!$unitId) return [];
                                return jurusan::where('unit', $unitId)
                                    ->where('jurusan', '!=', '-')
                                    ->pluck('jurusan', 'id')
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->visible(function (Get $get) {
                                $unitId = $get('unit_id');
                                if (!$unitId) return false;
                                if ($get('jenis_pendidikan') !== 'kurikulum') return false;
                                return jurusan::where('unit', $unitId)
                                    ->where('jurusan', '!=', '-')
                                    ->exists();
                            })
                            ->columnSpan(1),

                        Select::make('kelas_id')
                            ->label('Kelas')
                            ->options(function (Get $get) {
                                $unitId = $get('unit_id');
                                if (!$unitId) return [];
                                return kelas::where('idunit', $unitId)
                                    ->pluck('nmkls', 'idkls')
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->visible(fn(Get $get) => $get('jenis_pendidikan') === 'kurikulum' && $get('unit_id'))
                            ->columnSpan(1),

                        Select::make('bagian')
                            ->label('Bagian')
                            ->options(function (Get $get) {
                                $unitId = (int) $get('unit_id');
                                if (!$unitId) return [];

                                // Unit 1, 2, 3, 20, 21 → A-Z
                                if (in_array($unitId, [1, 2, 3, 20, 21])) {
                                    return array_combine(range('A', 'Z'), range('A', 'Z'));
                                }

                                // Unit 4, 5, 6 → 1-10
                                if (in_array($unitId, [4, 5, 6])) {
                                    $nums = range(1, 10);
                                    return array_combine($nums, $nums);
                                }

                                return [];
                            })
                            ->searchable()
                            ->preload()
                            ->visible(function (Get $get) {
                                if ($get('jenis_pendidikan') !== 'kurikulum') return false;
                                $unitId = (int) $get('unit_id');
                                if (!$unitId) return false;
                                // Unit 7, 18, 19, 22 → tidak ada bagian
                                return !in_array($unitId, [7, 18, 19, 22]);
                            })
                            ->columnSpan(1),

                        // ========================================
                        // FILTER MADIN
                        // ========================================

                        Select::make('madin_mkls')
                            ->label('Kelas Madin')
                            ->options([
                                1 => 'Kelas 1',
                                2 => 'Kelas 2',
                                3 => 'Kelas 3',
                                4 => 'Kelas 4',
                                5 => 'Kelas 5',
                                6 => 'Kelas 6',
                            ])
                            ->required()
                            ->visible(fn(Get $get) => $get('jenis_pendidikan') === 'madin')
                            ->columnSpan(1),

                        Select::make('madin_mbag')
                            ->label('Bagian Madin')
                            ->options(array_combine(range('A', 'Z'), range('A', 'Z')))
                            ->searchable()
                            ->required()
                            ->visible(fn(Get $get) => $get('jenis_pendidikan') === 'madin')
                            ->columnSpan(1),

                        Select::make('madin_tkt')
                            ->label('Jenjang / Tingkat')
                            ->options(
                                Madin::whereNotIn('id', [4, 5, 6]) // exclude non-standard
                                    ->pluck('madin', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->visible(fn(Get $get) => $get('jenis_pendidikan') === 'madin')
                            ->columnSpan(1),

                        Select::make('madin_jk')
                            ->label('Jenis Kelamin')
                            ->options([
                                1 => 'Putra',
                                2 => 'Putri',
                            ])
                            ->required()
                            ->visible(fn(Get $get) => $get('jenis_pendidikan') === 'madin')
                            ->columnSpan(1),

                    ])
                    ->columns(3),
            ]);
    }

    /**
     * Cari dan tampilkan data rekap hafalan
     */
    public function cariRekap(): void
    {
        $this->validate();

        $jenis = $this->data['jenis_pendidikan'] ?? null;
        $tahunAjaranId = $this->data['tahun_ajaran_id'] ?? null;

        if (!$jenis || !$tahunAjaranId) {
            Notification::make()
                ->title('Filter belum lengkap')
                ->body('Silakan lengkapi semua filter yang diperlukan.')
                ->danger()
                ->send();
            return;
        }

        $this->jenisPendidikan = $jenis;

        if ($jenis === 'kurikulum') {
            $this->cariRekapKurikulum($tahunAjaranId);
        } else {
            $this->cariRekapMadin($tahunAjaranId);
        }
    }

    /**
     * Rekap mode Kurikulum:
     * Filter santri berdasarkan unit + kelas kurikulum dari snapshot hafalan_santris
     * + combine dari bukuinduk untuk santri tanpa setoran.
     */
    protected function cariRekapKurikulum(int $tahunAjaranId): void
    {
        $unitId = $this->data['unit_id'] ?? null;
        $kelasId = $this->data['kelas_id'] ?? null;
        $jurusanId = $this->data['jurusan_id'] ?? null;
        $bagian = $this->data['bagian'] ?? null;

        if (!$unitId || !$kelasId) {
            Notification::make()
                ->title('Filter belum lengkap')
                ->body('Unit dan Kelas wajib diisi.')
                ->danger()
                ->send();
            return;
        }

        // 1. Ambil santri yang sudah punya setoran di tahun ajaran ini (snapshot historis)
        $hafalanQuery = HafalanSantri::where('tahun_ajaran_id', $tahunAjaranId)
            ->where('unit', $unitId)
            ->where('kls', $kelasId);

        $santriIdsFromHafalan = (clone $hafalanQuery)
            ->distinct()
            ->pluck('santri_id')
            ->toArray();

        // 2. Ambil santri dari bukuinduk yang belum punya setoran tapi masuk kelas ini
        $bukuindukQuery = bukuinduk::with(['Funkelas', 'unitSekolah'])
            ->where('unit', $unitId)
            ->where('kls', $kelasId)
            ->where('deleted', 0);

        if ($jurusanId) {
            $bukuindukQuery->where('jur', $jurusanId);
        }
        if ($bagian) {
            $bukuindukQuery->where('bag', $bagian);
        }

        $santriDariBukuinduk = $bukuindukQuery
            ->whereNotIn('id', $santriIdsFromHafalan)
            ->get();

        // 3. Ambil data lengkap santri dari hafalan
        $santriDariHafalan = bukuinduk::with(['Funkelas', 'unitSekolah'])->whereIn('id', $santriIdsFromHafalan)->get();

        // Jika jurusan/bagian dipilih, filter santri dari hafalan juga
        if ($jurusanId) {
            $santriDariHafalan = $santriDariHafalan->filter(fn($s) => $s->jur == $jurusanId);
        }
        if ($bagian) {
            $santriDariHafalan = $santriDariHafalan->filter(fn($s) => $s->bag == $bagian);
        }

        // 4. Gabungkan
        $semuaSantri = $santriDariHafalan->merge($santriDariBukuinduk)->unique('id')->sortBy('nm');

        if ($semuaSantri->isEmpty()) {
            $this->showHasil = true;
            $this->hasilRekap = [];
            $this->headerHafalan = [];
            $this->buildFilterLabel();
            Notification::make()
                ->title('Data tidak ditemukan')
                ->body('Tidak ada santri yang sesuai dengan filter.')
                ->warning()
                ->send();
            return;
        }

        // 5. Ambil data hafalan berdasarkan capaian tertinggi santri
        $this->buildHeaderAndResult($semuaSantri, $tahunAjaranId);
        $this->buildFilterLabel();
    }

    /**
     * Rekap mode Madin:
     * Filter santri berdasarkan mkls, mbag, tkt dari bukuinduk
     * + combine dari hafalan_santris historis
     */
    protected function cariRekapMadin(int $tahunAjaranId): void
    {
        $mkls = $this->data['madin_mkls'] ?? null;
        $mbag = $this->data['madin_mbag'] ?? null;
        $tkt = $this->data['madin_tkt'] ?? null;
        $jk = $this->data['madin_jk'] ?? null;

        if (!$mkls || !$mbag || !$tkt || !$jk) {
            Notification::make()
                ->title('Filter belum lengkap')
                ->body('Kelas, Bagian, Tingkat, dan Jenis Kelamin Madin wajib diisi.')
                ->danger()
                ->send();
            return;
        }

        // 1. Santri dari bukuinduk (kelas terkini)
        $santriDariBukuinduk = bukuinduk::with(['Funkelas', 'unitSekolah'])
            ->where('mkls', $mkls)
            ->where('mbag', $mbag)
            ->where('tkt', $tkt)
            ->where('jk', $jk)
            ->get();

        $santriIdsDariBukuinduk = $santriDariBukuinduk->pluck('id')->toArray();

        // 2. Santri yang pernah setor di kelas ini (historis) tapi sudah pindah
        //    ke tingkat/kelas BERBEDA (bukan sekedar pindah bagian dalam mkls/tkt yang sama).
        //    Santri yang masih di mkls+tkt sama tapi beda mbag (misal G→H)
        //    hanya muncul di bagian terbaru mereka (dari bukuinduk).
        $santriIdsInSameMklsTkt = bukuinduk::where('mkls', $mkls)
            ->where('tkt', $tkt)
            ->where('jk', $jk)
            ->pluck('id')
            ->toArray();

        $santriIdsFromHafalan = HafalanSantri::where('tahun_ajaran_id', $tahunAjaranId)
            ->where('mkls', $mkls)
            ->where('tkt', $tkt)
            ->whereNotIn('santri_id', $santriIdsInSameMklsTkt)
            ->distinct()
            ->pluck('santri_id')
            ->toArray();

        $santriDariHafalan = bukuinduk::with(['Funkelas', 'unitSekolah'])->whereIn('id', $santriIdsFromHafalan)->get();

        // 3. Gabungkan
        $semuaSantri = $santriDariBukuinduk->merge($santriDariHafalan)->unique('id')->sortBy('nm');

        if ($semuaSantri->isEmpty()) {
            $this->showHasil = true;
            $this->hasilRekap = [];
            $this->headerHafalan = [];
            $this->buildFilterLabel();
            Notification::make()
                ->title('Data tidak ditemukan')
                ->body('Tidak ada santri yang sesuai dengan filter.')
                ->warning()
                ->send();
            return;
        }

        // 4. Ambil data hafalan berdasarkan capaian tertinggi santri
        $this->buildHeaderAndResult($semuaSantri, $tahunAjaranId);
        $this->buildFilterLabel();
    }

    protected function buildHeaderAndResult($semuaSantri, int $tahunAjaranId): void
    {
        // Ambil semua pencapaian hafalan santri sekaligus (avoid N+1)
        $santriIds = $semuaSantri->pluck('id')->toArray();
        $hafalanTuntas = HafalanSantri::where('tahun_ajaran_id', $tahunAjaranId)
            ->whereIn('santri_id', $santriIds)
            ->get()
            ->groupBy('santri_id');

        if ($this->jenisPendidikan === 'madin') {
            // Jika filter Madin, sesuaikan header dengan tingkat diniyyah yang dipilih
            $maxTkt = (int) ($this->data['madin_tkt'] ?? 1);
            $maxMkls = (int) ($this->data['madin_mkls'] ?? 1);
        } else {
            // Cari capaian tertinggi (max tkt dan max mkls) secara dinamis untuk kurikulum
            $maxTkt = 1;
            $maxMkls = 1;

            // 1. Dari data kelas berjalan santri (bukuinduk)
            foreach ($semuaSantri as $santri) {
                $sTkt = (int) $santri->tkt;
                $sMkls = (int) $santri->mkls;

                if ($sTkt > $maxTkt) {
                    $maxTkt = $sTkt;
                    $maxMkls = $sMkls;
                } elseif ($sTkt === $maxTkt) {
                    if ($sMkls > $maxMkls) {
                        $maxMkls = $sMkls;
                    }
                }
            }

            // 2. Dari data historis setoran hafalan santri
            foreach ($hafalanTuntas as $sId => $setorans) {
                foreach ($setorans as $setoran) {
                    $hTkt = (int) $setoran->tkt;
                    $hMkls = (int) $setoran->mkls;

                    if ($hTkt > $maxTkt) {
                        $maxTkt = $hTkt;
                        $maxMkls = $hMkls;
                    } elseif ($hTkt === $maxTkt) {
                        if ($hMkls > $maxMkls) {
                            $maxMkls = $hMkls;
                        }
                    }
                }
            }
        }

        // Ambil semua data hafalan berdasarkan capaian tertinggi
        $dataHafalan = DataHafalan::where(function ($query) use ($maxTkt, $maxMkls) {
            $query->where('tkt', '<', $maxTkt)
                ->orWhere(function ($q) use ($maxTkt, $maxMkls) {
                    $q->where('tkt', $maxTkt)
                        ->where('mkls', '<=', $maxMkls);
                });
        })
            ->orderBy('tkt')
            ->orderBy('mkls')
            ->orderBy('kriteria') // Wajib dulu
            ->orderBy('nama_hafalan')
            ->get();

        $this->headerHafalan = $dataHafalan->map(function ($h) {
            $madin = Madin::find($h->tkt);
            return [
                'id' => $h->id,
                'nama_hafalan' => $h->nama_hafalan,
                'kriteria' => $h->kriteria,
                'mkls' => $h->mkls,
                'tkt' => $h->tkt,
                'tkt_label' => $madin?->madin ?? '-',
            ];
        })->toArray();

        // Eager-load relasi madin untuk label
        $madinCache = Madin::pluck('madin', 'id')->toArray();

        // Build data tabel
        $this->hasilRekap = $semuaSantri->values()->map(function ($santri) use ($hafalanTuntas, $dataHafalan, $madinCache) {
            $tuntasIds = [];
            if (isset($hafalanTuntas[$santri->id])) {
                $tuntasIds = $hafalanTuntas[$santri->id]->pluck('data_hafalan_id')->map(fn($id) => (int) $id)->toArray();
            }

            $hafalanStatus = [];
            foreach ($dataHafalan as $hafalan) {
                $hafalanStatus[$hafalan->id] = in_array($hafalan->id, $tuntasIds);
            }

            // Build madin label: "2 G Ula"
            $madinLabel = $santri->mkls . ' ' . ($santri->mbag ?? '') . ' ' . ($madinCache[$santri->tkt] ?? '-');

            // Build kurikulum label: "X A SMA"
            $unitName = $santri->unitSekolah?->unit ?? '';
            $kelasName = $santri->Funkelas?->nmkls ?? '';
            $bagName = $santri->bag ?? '';
            $kurikulumLabel = trim("{$kelasName} {$bagName} {$unitName}");
            if (empty($kurikulumLabel)) {
                $kurikulumLabel = '-';
            }

            return [
                'id' => $santri->id,
                'noin' => $santri->noin,
                'nama' => $santri->nm,
                'tkt' => $santri->tkt,
                'madin_label' => trim($madinLabel),
                'kurikulum_label' => $kurikulumLabel,
                'hafalan' => $hafalanStatus,
            ];
        })->toArray();

        $this->showHasil = true;
    }

    /**
     * Build label filter aktif untuk ditampilkan di header card hasil
     */
    protected function buildFilterLabel(): void
    {
        $tahunAjaran = TahunAjaran::find($this->data['tahun_ajaran_id'] ?? 0);
        $jenis = $this->data['jenis_pendidikan'] ?? '';

        $label = ($tahunAjaran?->nama_tahun_ajaran ?? '-');

        if ($jenis === 'kurikulum') {
            $unitModel = unit::find($this->data['unit_id'] ?? 0);
            $kelasModel = kelas::where('idkls', $this->data['kelas_id'] ?? 0)->first();
            $label .= ' | ' . ($unitModel?->unit ?? '-');
            $label .= ' | Kelas ' . ($kelasModel?->nmkls ?? '-');
            if (!empty($this->data['bagian'])) {
                $label .= ' ' . $this->data['bagian'];
            }
            if (!empty($this->data['jurusan_id'])) {
                $jur = jurusan::find($this->data['jurusan_id']);
                $label .= ' | ' . ($jur?->jurusan ?? '');
            }
        } else {
            $madinModel = Madin::find($this->data['madin_tkt'] ?? 0);
            $jkLabel = match ((int) ($this->data['madin_jk'] ?? 0)) {
                1 => 'Putra',
                2 => 'Putri',
                default => '-',
            };
            $label .= ' | ' . ($madinModel?->madin ?? '-');
            $label .= ' | Kelas ' . ($this->data['madin_mkls'] ?? '-');
            $label .= ($this->data['madin_mbag'] ?? '');
            $label .= ' | ' . $jkLabel;
        }

        $this->filterLabel = $label;
    }

    /**
     * Download current table report to Excel
     */
    public function downloadExcel()
    {
        if (empty($this->hasilRekap)) {
            Notification::make()
                ->title('Tidak ada data')
                ->body('Silakan cari data terlebih dahulu sebelum mengunduh.')
                ->warning()
                ->send();
            return null;
        }

        $filename = 'Rekap_Hafalan_' . str_replace(['/', ' ', '|', '-'], '_', $this->filterLabel) . '.xlsx';

        Notification::make()
            ->title('Mengunduh rekapitulasi...')
            ->success()
            ->send();

        return Excel::download(
            new RekapHafalanTableExport(
                $this->hasilRekap,
                $this->headerHafalan,
                $this->filterLabel,
                $this->jenisPendidikan
            ),
            $filename
        );
    }
}
