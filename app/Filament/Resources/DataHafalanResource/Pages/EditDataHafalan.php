<?php

namespace App\Filament\Resources\DataHafalanResource\Pages;

use App\Filament\Resources\DataHafalanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDataHafalan extends EditRecord
{
    protected static string $resource = DataHafalanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
