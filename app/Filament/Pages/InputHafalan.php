<?php

namespace App\Filament\Pages;

use App\Models\bukuinduk;
use App\Models\DataHafalan;
use App\Models\HafalanSantri;
use App\Models\Mustahiq;
use App\Models\TahunAjaran;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

use Filament\Forms\Components\View;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Components\Grid;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class InputHafalan extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Input Hafalan';
    protected static string $view = 'filament.pages.input-hafalan';

    public ?array $data = [];

    // Data hafalan yang ditampilkan sebagai checkbox
    public array $hafalanList = [];

    // Hafalan yang sudah dicentang (ID)
    public array $hafalanChecked = [];

    // ID tahun ajaran yang sedang dipilih di tab hafalan
    public ?int $selectedTahunAjaranId = null;

    // Mode edit uncek (toggle untuk admin)
    public bool $uncekMode = false;

    public function mount(): void
    {
        $tahunAjaran = TahunAjaran::getAktif();
        $this->selectedTahunAjaranId = $tahunAjaran?->id;

        $this->form->fill([
            'tahun_ajaran' => $tahunAjaran?->label_lengkap ?? 'Belum ada tahun ajaran aktif',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([

                Grid::make(3)
                    ->schema([
                        TextInput::make('tahun_ajaran')
                            ->label('Tahun Ajaran Aktif')
                            ->disabled()
                            ->columnSpan(1),

                        Select::make('santri_id')
                            ->label('Cari NIS / Nama Santri')
                            ->searchable()
                            ->live()
                            ->columnSpan(2)

                            ->getSearchResultsUsing(function (string $search): array {
                                return bukuinduk::query()
                                    ->where('noin', 'like', "%{$search}%")
                                    ->orWhere('nm', 'like', "%{$search}%")
                                    ->limit(20)
                                    ->get()
                                    ->mapWithKeys(fn($item) => [
                                        $item->id => "{$item->noin} - {$item->nm}"
                                    ])
                                    ->toArray();
                            })

                            ->getOptionLabelUsing(function ($value): ?string {

                                $santri = bukuinduk::find($value);

                                if (!$santri) {
                                    return null;
                                }

                                return "{$santri->noin} - {$santri->nm}";
                            })

                            ->afterStateUpdated(function ($state, Set $set) {

                                if (!$state) {
                                    $set('mustahiq_nama', null);
                                    $this->hafalanList = [];
                                    $this->hafalanChecked = [];
                                    $this->uncekMode = false;
                                    return;
                                }

                                $santri = bukuinduk::with('madin', 'unitSekolah', 'Funkelurahan', 'Funkecamatan', 'Funkabupaten', 'Funprovinsi')->find($state);

                                if (!$santri) {
                                    $set('mustahiq_nama', null);
                                    $this->hafalanList = [];
                                    $this->hafalanChecked = [];
                                    $this->uncekMode = false;
                                    return;
                                }

                                $set('noin', $santri->noin);

                                $set('nm', $santri->nm);

                                $bulanIndo = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];

                                $namaBulan = $bulanIndo[(int) $santri->bln] ?? $santri->bln;

                                $set('ttl', "{$santri->tl}, {$santri->tlhr} {$namaBulan} {$santri->th}");

                                $set('nayah', $santri->nayah);

                                $set('unit', $santri->unitSekolah?->unit);

                                $set('alamat', "{$santri->Funkelurahan?->nama_kel}, {$santri->Funkecamatan?->nama_kec}, {$santri->Funkabupaten?->nama_kabkot}, {$santri->Funprovinsi?->nama_prov}");

                                $set('kelas', " {$santri->mkls} {$santri->mbag} {$santri->madin?->madin}");

                                // Jenis kelamin dari buku induk
                                $jkLabel = match ((int) $santri->jk) {
                                    1 => 'Putra',
                                    2 => 'Putri',
                                    default => '-',
                                };
                                $set('jk_label', $jkLabel);

                                // Cari mustahiq berdasarkan kelas santri + jk + tahun ajaran aktif
                                $tahunAjaranAktif = TahunAjaran::getAktif();
                                $mustahiq = Mustahiq::where('mkls', $santri->mkls)
                                    ->where('mbag', $santri->mbag)
                                    ->where('tkt', $santri->tkt)
                                    ->where('jk', $santri->jk)
                                    ->when($tahunAjaranAktif, fn ($q) => $q->where('tahun_ajaran_id', $tahunAjaranAktif->id))
                                    ->first();

                                $set('mustahiq_nama', $mustahiq?->nama_mustahiq ?? '-');

                                // Reset uncek mode
                                $this->uncekMode = false;

                                // Reset tahun ajaran ke aktif
                                $tahunAjaran = TahunAjaran::getAktif();
                                $this->selectedTahunAjaranId = $tahunAjaran?->id;

                                // Load data hafalan berdasarkan kelas diniyyah santri
                                $this->loadHafalanSantri($santri);
                            }),
                    ]),

                // ===========================
                // LAYOUT: IDENTITAS (KIRI) & INPUT HAFALAN (KANAN)
                // ===========================

                Grid::make(5)
                    ->hidden(fn(Get $get) => blank($get('santri_id')))
                    ->schema([

                        // Card Identitas (Kiri - 2 kolom dari 5)
                        Section::make('Detail Biodata Santri')
                            ->icon('heroicon-o-user')
                            ->columnSpan(2)
                            ->schema([
                                TextInput::make('noin')->label('NIS')->disabled(),
                                TextInput::make('nm')->label('Nama')->disabled(),
                                TextInput::make('jk_label')->label('Jenis Kelamin')->disabled(),
                                TextInput::make('kelas')->label('Kelas Madin')->disabled(),
                                TextInput::make('mustahiq_nama')->label('Mustahiq')->disabled(),
                                TextInput::make('nayah')->label('Nama Ayah')->disabled(),
                                TextInput::make('unit')->label('Unit Sekolah')->disabled(),
                                TextInput::make('ttl')->label('Tempat dan Tanggal Lahir')->disabled(),
                                Textarea::make('alamat')->label('Alamat')->disabled()->rows(2),
                            ]),

                        // Card Input Hafalan (Kanan - 3 kolom dari 5)
                        Section::make('Input Hafalan')
                            ->icon('heroicon-o-book-open')
                            ->columnSpan(3)
                            ->schema([
                                View::make('filament.components.hafalan-list')
                            ]),

                    ]),

            ]);
    }

    /**
     * Ganti tahun ajaran yang dipilih dan reload data hafalan
     */
    public function changeTahunAjaran(int $tahunAjaranId): void
    {
        $this->selectedTahunAjaranId = $tahunAjaranId;
        $this->uncekMode = false;

        $santriId = $this->data['santri_id'] ?? null;
        if (!$santriId) return;

        $santri = bukuinduk::find($santriId);
        if (!$santri) return;

        $this->loadHafalanSantri($santri);
    }

    /**
     * Load data hafalan berdasarkan kelas diniyyah santri
     */
    public function loadHafalanSantri($santri): void
    {
        $tahunAjaranAktif = TahunAjaran::getAktif();
        $isAktif = $tahunAjaranAktif && $this->selectedTahunAjaranId === $tahunAjaranAktif->id;

        // Cek apakah sudah ada record hafalan di tahun ajaran yang dipilih
        $existingRecord = HafalanSantri::where('santri_id', $santri->id)
            ->where('tahun_ajaran_id', $this->selectedTahunAjaranId)
            ->first();

        if ($existingRecord) {
            // Sudah ada record → kunci kelas sesuai record yang tersimpan
            // (mencegah mixing kelas dalam satu tahun ajaran)
            $mkls = $existingRecord->mkls;
            $tkt = $existingRecord->tkt;
        } elseif ($isAktif) {
            // Tahun ajaran aktif, belum ada record → pakai kelas saat ini dari bukuinduk
            $mkls = $santri->mkls;
            $tkt = $santri->tkt;
        } else {
            // Tahun ajaran lama, tidak ada record → kosongkan
            $this->hafalanList = [];
            $this->hafalanChecked = [];
            return;
        }

        // Ambil data hafalan berdasarkan kelas diniyyah
        $this->hafalanList = DataHafalan::where('mkls', $mkls)
            ->where('tkt', $tkt)
            ->orderBy('kriteria') // Wajib dulu
            ->orderBy('nama_hafalan')
            ->get()
            ->toArray();

        // Ambil hafalan yang sudah dicentang untuk tahun ajaran yang dipilih
        $this->hafalanChecked = [];

        if ($this->selectedTahunAjaranId) {
            $this->hafalanChecked = HafalanSantri::where('santri_id', $santri->id)
                ->where('tahun_ajaran_id', $this->selectedTahunAjaranId)
                ->pluck('data_hafalan_id')
                ->map(fn($id) => (int) $id)
                ->toArray();
        }
    }

    /**
     * Set hafalan sebagai tuntas (centang) — dipanggil dari Blade
     */
    public function setHafalanTuntas(int $dataHafalanId): void
    {
        $santriId = $this->data['santri_id'] ?? null;

        // Hanya bisa centang pada tahun ajaran aktif
        $tahunAjaran = TahunAjaran::getAktif();

        if (!$santriId || !$tahunAjaran) {
            Notification::make()
                ->title('Tahun ajaran belum diset aktif')
                ->danger()
                ->send();
            return;
        }

        // Pastikan sedang di tahun ajaran aktif
        if ($this->selectedTahunAjaranId !== $tahunAjaran->id) {
            Notification::make()
                ->title('Tidak bisa mengubah data')
                ->body('Anda hanya bisa mengubah hafalan pada tahun ajaran yang sedang aktif.')
                ->danger()
                ->send();
            return;
        }

        $santri = bukuinduk::find($santriId);
        if (!$santri) return;

        // Cek item yang ditoggle
        $targetHafalan = DataHafalan::find($dataHafalanId);
        if (!$targetHafalan) return;

        // Cek apakah sudah ada
        $existing = HafalanSantri::where('santri_id', $santriId)
            ->where('data_hafalan_id', $dataHafalanId)
            ->where('tahun_ajaran_id', $tahunAjaran->id)
            ->first();

        if ($existing) {
            Notification::make()
                ->title('Hafalan sudah ditandai tuntas')
                ->warning()
                ->send();
            return;
        }

        // VALIDASI: Jika memilih Sunnah, pastikan semua yang Wajib di kelas tersebut sudah selesai (tercentang)
        if ($targetHafalan->kriteria === 'Sunnah') {
            $allWajibIds = collect($this->hafalanList)
                ->where('kriteria', 'Wajib')
                ->pluck('id')
                ->toArray();

            // Check if all Wajib IDs exist in $this->hafalanChecked
            $completedWajibCount = count(array_intersect($allWajibIds, $this->hafalanChecked));
            $totalWajibCount = count($allWajibIds);

            if ($completedWajibCount < $totalWajibCount) {
                Notification::make()
                    ->title('Hafalan Wajib belum selesai')
                    ->body('Semua hafalan wajib di kelas ini harus diselesaikan terlebih dahulu.')
                    ->danger()
                    ->send();
                return;
            }
        }

        // Check: simpan record baru beserta data historis
        HafalanSantri::create([
            'santri_id' => $santriId,
            'data_hafalan_id' => $dataHafalanId,
            'tahun_ajaran_id' => $tahunAjaran->id,
            'mkls' => $santri->mkls,
            'tkt' => $santri->tkt,
            'kls' => $santri->kls,
            'unit' => $santri->unit,
        ]);

        $this->hafalanChecked[] = $dataHafalanId;

        Notification::make()
            ->title('Hafalan tersimpan')
            ->success()
            ->send();

        // RELOAD STATE
        $this->loadHafalanSantri($santri);
    }

    /**
     * Batalkan hafalan tuntas (uncek) — hanya untuk admin
     */
    public function batalkanHafalan(int $dataHafalanId): void
    {
        $santriId = $this->data['santri_id'] ?? null;

        // Hanya bisa uncek pada tahun ajaran aktif
        $tahunAjaran = TahunAjaran::getAktif();

        if (!$santriId || !$tahunAjaran) {
            Notification::make()
                ->title('Tahun ajaran belum diset aktif')
                ->danger()
                ->send();
            return;
        }

        // Pastikan sedang di tahun ajaran aktif
        if ($this->selectedTahunAjaranId !== $tahunAjaran->id) {
            Notification::make()
                ->title('Tidak bisa mengubah data')
                ->body('Anda hanya bisa mengubah hafalan pada tahun ajaran yang sedang aktif.')
                ->danger()
                ->send();
            return;
        }

        $santri = bukuinduk::find($santriId);
        if (!$santri) return;

        $existing = HafalanSantri::where('santri_id', $santriId)
            ->where('data_hafalan_id', $dataHafalanId)
            ->where('tahun_ajaran_id', $tahunAjaran->id)
            ->first();

        if (!$existing) {
            return;
        }

        $existing->delete();

        $this->hafalanChecked = array_values(
            array_diff($this->hafalanChecked, [$dataHafalanId])
        );

        Notification::make()
            ->title('Hafalan dibatalkan')
            ->warning()
            ->send();

        // RELOAD STATE
        $this->loadHafalanSantri($santri);
    }

    /**
     * Toggle mode uncek
     */
    public function toggleUncekMode(): void
    {
        $this->uncekMode = !$this->uncekMode;
    }
}
