<?php

namespace App\Filament\Resources\PraktikumClassResource\Pages;

use App\Filament\Resources\PraktikumClassResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPraktikumClass extends EditRecord
{
    protected static string $resource = PraktikumClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
