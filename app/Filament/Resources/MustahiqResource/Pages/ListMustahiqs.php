<?php

namespace App\Filament\Resources\MustahiqResource\Pages;

use App\Filament\Resources\MustahiqResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMustahiqs extends ListRecords
{
    protected static string $resource = MustahiqResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
