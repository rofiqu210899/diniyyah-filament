<?php

namespace App\Filament\Pages;

use App\Exports\RekapPertingkatanExport;
use App\Models\TahunAjaran;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Facades\Excel;

class Rekapitulasi extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-down';
    protected static ?string $navigationLabel = 'Ahad Legi';
    protected static ?string $title = 'Rekapitulasi Ahad Legi';
    protected static string $view = 'filament.pages.rekapitulasi';
    protected static ?string $navigationGroup = 'Laporan';

    public ?array $data = [];

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
                Section::make('Filter Rekapitulasi')
                    ->description('Silakan pilih tahun ajaran untuk data rekapitulasi yang ingin diunduh.')
                    ->schema([
                        Select::make('tahun_ajaran_id')
                            ->label('Tahun Ajaran')
                            ->options(
                                TahunAjaran::orderByDesc('id')
                                    ->pluck('nama_tahun_ajaran', 'id')
                            )
                            ->searchable()
                            ->required()
                            ->preload(),
                    ])
                    ->columns(1),
            ]);
    }

    /**
     * Unduh laporan rekapitulasi per tingkatan
     */
    public function downloadRekapPertingkatan()
    {
        $this->validate();

        $tahunAjaranId = $this->data['tahun_ajaran_id'];
        $tahunAjaran = TahunAjaran::find($tahunAjaranId);

        if (!$tahunAjaran) {
            Notification::make()
                ->title('Tahun ajaran tidak ditemukan')
                ->danger()
                ->send();
            return null;
        }

        $filename = 'Rekap_Hafalan_Pertingkatan_' . str_replace(['/', ' ', '-'], '_', $tahunAjaran->nama_tahun_ajaran) . '.xlsx';

        Notification::make()
            ->title('Mengunduh rekapitulasi pertingkatan...')
            ->success()
            ->send();

        return Excel::download(new RekapPertingkatanExport($tahunAjaranId), $filename);
    }
}
