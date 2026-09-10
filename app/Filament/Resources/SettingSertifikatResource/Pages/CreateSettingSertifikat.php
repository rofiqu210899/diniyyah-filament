<?php

namespace App\Filament\Resources\SettingSertifikatResource\Pages;

use App\Filament\Resources\SettingSertifikatResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSettingSertifikat extends CreateRecord
{
    protected static string $resource = SettingSertifikatResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
