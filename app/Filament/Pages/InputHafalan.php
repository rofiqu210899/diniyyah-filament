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

    
    public array $hafalanList = [];

    
    public array $hafalanChecked = [];

    
    public ?int $selectedTahunAjaranId = null;

    
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

                                
                                $jkLabel = match ((int) $santri->jk) {
                                    1 => 'Putra',
                                    2 => 'Putri',
                                    default => '-',
                                };
                                $set('jk_label', $jkLabel);

                                
                                $tahunAjaranAktif = TahunAjaran::getAktif();
                                $mustahiq = Mustahiq::where('mkls', $santri->mkls)
                                    ->where('mbag', $santri->mbag)
                                    ->where('tkt', $santri->tkt)
                                    ->where('jk', $santri->jk)
                                    ->when($tahunAjaranAktif, fn ($q) => $q->where('tahun_ajaran_id', $tahunAjaranAktif->id))
                                    ->first();

                                $set('mustahiq_nama', $mustahiq?->nama_mustahiq ?? '-');

                                
                                $this->uncekMode = false;

                                
                                $tahunAjaran = TahunAjaran::getAktif();
                                $this->selectedTahunAjaranId = $tahunAjaran?->id;

                                
                                $this->loadHafalanSantri($santri);
                            }),
                    ]),

                
                
                

                Grid::make(5)
                    ->hidden(fn(Get $get) => blank($get('santri_id')))
                    ->schema([

                        
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

                        
                        Section::make('Input Hafalan')
                            ->icon('heroicon-o-book-open')
                            ->columnSpan(3)
                            ->schema([
                                View::make('filament.components.hafalan-list')
                            ]),

                    ]),

            ]);
    }

    
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

    
    public function loadHafalanSantri($santri): void
    {
        $tahunAjaranAktif = TahunAjaran::getAktif();
        $isAktif = $tahunAjaranAktif && $this->selectedTahunAjaranId === $tahunAjaranAktif->id;

        
        $existingRecord = HafalanSantri::where('santri_id', $santri->id)
            ->where('tahun_ajaran_id', $this->selectedTahunAjaranId)
            ->first();

        if ($existingRecord) {
            
            
            $mkls = $existingRecord->mkls;
            $tkt = $existingRecord->tkt;
        } elseif ($isAktif) {
            
            $mkls = $santri->mkls;
            $tkt = $santri->tkt;
        } else {
            
            $this->hafalanList = [];
            $this->hafalanChecked = [];
            return;
        }

        
        $this->hafalanList = DataHafalan::where('mkls', $mkls)
            ->where('tkt', $tkt)
            ->orderBy('kriteria') 
            ->orderBy('nama_hafalan')
            ->get()
            ->toArray();

        
        $this->hafalanChecked = [];

        if ($this->selectedTahunAjaranId) {
            $this->hafalanChecked = HafalanSantri::where('santri_id', $santri->id)
                ->where('tahun_ajaran_id', $this->selectedTahunAjaranId)
                ->pluck('data_hafalan_id')
                ->map(fn($id) => (int) $id)
                ->toArray();
        }
    }

    
    public function setHafalanTuntas(int $dataHafalanId): void
    {
        $santriId = $this->data['santri_id'] ?? null;

        
        $tahunAjaran = TahunAjaran::getAktif();

        if (!$santriId || !$tahunAjaran) {
            Notification::make()
                ->title('Tahun ajaran belum diset aktif')
                ->danger()
                ->send();
            return;
        }

        
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

        
        $targetHafalan = DataHafalan::find($dataHafalanId);
        if (!$targetHafalan) return;

        
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

        
        if (in_array($targetHafalan->kriteria, ['Sunnah', 'Wisuda'])) {
            $allWajibIds = collect($this->hafalanList)
                ->where('kriteria', 'Wajib')
                ->pluck('id')
                ->toArray();

            
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

        
        $this->loadHafalanSantri($santri);
    }

    
    public function batalkanHafalan(int $dataHafalanId): void
    {
        $santriId = $this->data['santri_id'] ?? null;

        
        $tahunAjaran = TahunAjaran::getAktif();

        if (!$santriId || !$tahunAjaran) {
            Notification::make()
                ->title('Tahun ajaran belum diset aktif')
                ->danger()
                ->send();
            return;
        }

        
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

        
        $this->loadHafalanSantri($santri);
    }

    
    public function toggleUncekMode(): void
    {
        $this->uncekMode = !$this->uncekMode;
    }
}
