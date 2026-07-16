<?php

namespace App\Filament\Pages;

use App\Exports\RekapPertingkatanExport;
use App\Exports\RekapPertingkatanSheet;
use App\Exports\RekapMustahiqExport;
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

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'inputer']) ?? false;
    }

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

    protected function getActions(): array
    {
        return [];
    }

    public function openPreview(): void
    {
        $this->validate();
        $this->mountAction('preview');
    }

    public function getPreviewData($tahunAjaranId): array
    {
        $ula = (new RekapPertingkatanSheet($tahunAjaranId, 1, 'ULA'))->getData();
        $wustho = (new RekapPertingkatanSheet($tahunAjaranId, 2, 'WUSTHO'))->getData();
        $ulya = (new RekapPertingkatanSheet($tahunAjaranId, 3, 'ULYA'))->getData();

        return [
            'ula' => $ula,
            'wustho' => $wustho,
            'ulya' => $ulya,
        ];
    }

    public function previewAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('preview')
            ->modalHeading('Pratinjau Rekapitulasi Ahad Legi')
            ->modalWidth('7xl')
            ->modalContent(function () {
                $tahunAjaranId = $this->data['tahun_ajaran_id'] ?? null;
                if (!$tahunAjaranId) return null;

                $previewData = $this->getPreviewData($tahunAjaranId);
                return view('filament.pages.rekapitulasi-preview-modal', $previewData);
            })
            ->modalSubmitActionLabel('Unduh Excel')
            ->modalCancelActionLabel('Tutup')
            ->action(function () {
                return $this->downloadRekapPertingkatanDirect();
            });
    }

    
    public function downloadRekapPertingkatanDirect()
    {
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

    public function openDownloadMustahiq(): void
    {
        $this->validate();
        $this->mountAction('downloadMustahiq');
    }

    public function downloadMustahiqAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('downloadMustahiq')
            ->modalHeading('Unduh Rekap Per Mustahiq')
            ->modalWidth('md')
            ->form([
                Select::make('jenjang')
                    ->label('Pilih Tingkatan / Jenjang')
                    ->options([
                        1 => 'ULA',
                        2 => 'WUSTHO',
                        3 => 'ULYA',
                    ])
                    ->required()
                    ->preload(),
            ])
            ->modalSubmitActionLabel('Unduh Excel')
            ->modalCancelActionLabel('Tutup')
            ->action(function (array $data) {
                $tktId = (int) $data['jenjang'];
                return $this->downloadRekapMustahiqSingle($tktId);
            });
    }

    public function downloadRekapMustahiqSingle($tktId)
    {
        $tahunAjaranId = $this->data['tahun_ajaran_id'];
        $tahunAjaran = TahunAjaran::find($tahunAjaranId);

        if (!$tahunAjaran) {
            Notification::make()
                ->title('Tahun ajaran tidak ditemukan')
                ->danger()
                ->send();
            return null;
        }

        $jenjangName = match ($tktId) {
            1 => 'ULA',
            2 => 'WUSTHO',
            3 => 'ULYA',
            default => '',
        };

        $suffix = str_replace(['/', ' ', '-'], '_', $tahunAjaran->nama_tahun_ajaran);
        $filename = "Rekap_Hafalan_Per_Mustahiq_{$jenjangName}_{$suffix}.xlsx";

        // Verify if there are mustahiqs for this tkt
        $hasMustahiq = \App\Models\Mustahiq::where('tahun_ajaran_id', $tahunAjaranId)
            ->where('tkt', $tktId)
            ->exists();

        if (!$hasMustahiq) {
            Notification::make()
                ->title('Tidak ada data mustahiq')
                ->body("Tidak ditemukan data mustahiq untuk tingkatan {$jenjangName} di tahun ajaran ini.")
                ->warning()
                ->send();
            return null;
        }

        Notification::make()
            ->title("Mengunduh rekapitulasi per mustahiq {$jenjangName}...")
            ->success()
            ->send();

        return Excel::download(new RekapMustahiqExport($tahunAjaranId, $tktId), $filename);
    }
}
