<?php

namespace App\Filament\Resources\PraktikumClassResource\Pages;

use App\Filament\Resources\PraktikumClassResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPraktikumClasses extends ListRecords
{
    protected static string $resource = PraktikumClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
