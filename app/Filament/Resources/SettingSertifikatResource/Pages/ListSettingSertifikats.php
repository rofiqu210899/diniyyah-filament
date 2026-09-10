<?php

namespace App\Filament\Resources\SettingSertifikatResource\Pages;

use App\Filament\Resources\SettingSertifikatResource;
use App\Models\SettingSertifikat;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSettingSertifikats extends ListRecords
{
    protected static string $resource = SettingSertifikatResource::class;

    public function mount(): void
    {
        parent::mount();

        // Pastikan default setting sudah ada
        SettingSertifikat::getAktifSetting();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
