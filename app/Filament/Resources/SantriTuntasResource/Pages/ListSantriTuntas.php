<?php

namespace App\Filament\Resources\SantriTuntasResource\Pages;

use App\Filament\Resources\SantriTuntasResource;
use Filament\Resources\Pages\ListRecords;

class ListSantriTuntas extends ListRecords
{
    protected static string $resource = SantriTuntasResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
