<?php

namespace App\Filament\Resources\HafalanSantriResource\Pages;

use App\Filament\Resources\HafalanSantriResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHafalanSantris extends ListRecords
{
    protected static string $resource = HafalanSantriResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
