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

    public function downloadRekapMustahiqDirect()
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

        $suffix = str_replace(['/', ' ', '-'], '_', $tahunAjaran->nama_tahun_ajaran);

        $files = [
            'ULA' => [
                'tkt_id' => 1,
                'filename' => "Rekap_Hafalan_Per_Mustahiq_ULA_{$suffix}.xlsx"
            ],
            'WUSTHO' => [
                'tkt_id' => 2,
                'filename' => "Rekap_Hafalan_Per_Mustahiq_WUSTHO_{$suffix}.xlsx"
            ],
            'ULYA' => [
                'tkt_id' => 3,
                'filename' => "Rekap_Hafalan_Per_Mustahiq_ULYA_{$suffix}.xlsx"
            ],
        ];

        $generatedFiles = [];
        foreach ($files as $name => $info) {
            $hasMustahiq = \App\Models\Mustahiq::where('tahun_ajaran_id', $tahunAjaranId)
                ->where('tkt', $info['tkt_id'])
                ->exists();

            if ($hasMustahiq) {
                Excel::store(new RekapMustahiqExport($tahunAjaranId, $info['tkt_id']), $info['filename'], 'local');
                $generatedFiles[] = $info['filename'];
            }
        }

        if (empty($generatedFiles)) {
            Notification::make()
                ->title('Tidak ada data mustahiq')
                ->body('Tidak ditemukan data mustahiq untuk tingkatan kelas di tahun ajaran ini.')
                ->warning()
                ->send();
            return null;
        }

        $zipFilename = "Rekap_Hafalan_Per_Mustahiq_{$suffix}.zip";
        $zipPath = storage_path("app/{$zipFilename}");

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            foreach ($generatedFiles as $filename) {
                $filePath = storage_path("app/" . $filename);
                if (file_exists($filePath)) {
                    $zip->addFile($filePath, $filename);
                }
            }
            $zip->close();
        }

        // Delete temporary excel files from storage
        foreach ($generatedFiles as $filename) {
            $filePath = storage_path("app/" . $filename);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        Notification::make()
            ->title('Mengunduh rekapitulasi per mustahiq...')
            ->success()
            ->send();

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }
}
