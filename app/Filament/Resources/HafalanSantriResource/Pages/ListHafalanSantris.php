<?php

namespace App\Filament\Resources\HafalanSantriResource\Pages;

use App\Filament\Resources\HafalanSantriResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\HafalanKolektifExport;

class ListHafalanSantris extends ListRecords
{
    protected static string $resource = HafalanSantriResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export')
                ->label('Ekspor')
                ->icon('heroicon-o-arrow-down-tray')
                ->form([
                    DatePicker::make('tanggal_mulai')
                        ->label('Tanggal Mulai')
                        ->required(),
                    DatePicker::make('tanggal_sampai')
                        ->label('Tanggal Sampai')
                        ->required(),
                    Select::make('jk')
                        ->label('Jenis Kelamin')
                        ->placeholder('Semua (Putra & Putri)')
                        ->options([
                            1 => 'Putra',
                            2 => 'Putri',
                        ]),
                ])
                ->action(function (array $data) {
                    $tanggalMulai  = $data['tanggal_mulai'];
                    $tanggalSampai = $data['tanggal_sampai'];
                    $jk            = $data['jk'] ?? null;

                    $jkLabel = match ((int) $jk) {
                        1 => '-putra',
                        2 => '-putri',
                        default => '',
                    };

                    return Excel::download(
                        new HafalanKolektifExport($tanggalMulai, $tanggalSampai, $jk),
                        "hafalan-kolektif{$jkLabel}-{$tanggalMulai}-to-{$tanggalSampai}.xlsx"
                    );
                })
        ];
    }
}
