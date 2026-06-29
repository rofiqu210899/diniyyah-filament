<?php

namespace App\Filament\Resources\MustahiqResource\Pages;

use App\Filament\Resources\MustahiqResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMustahiq extends EditRecord
{
    protected static string $resource = MustahiqResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
