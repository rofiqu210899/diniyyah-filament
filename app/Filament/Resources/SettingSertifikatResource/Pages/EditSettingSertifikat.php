<?php

namespace App\Filament\Resources\SettingSertifikatResource\Pages;

use App\Filament\Resources\SettingSertifikatResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSettingSertifikat extends EditRecord
{
    protected static string $resource = SettingSertifikatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('preview')
                ->label('Pratinjau Sertifikat')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->url(fn() => route('sertifikat.preview_setting', ['setting' => $this->record->id]), shouldOpenInNewTab: true),

            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
