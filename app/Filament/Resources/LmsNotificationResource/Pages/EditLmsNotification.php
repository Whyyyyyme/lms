<?php

namespace App\Filament\Resources\LmsNotificationResource\Pages;

use App\Filament\Resources\LmsNotificationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLmsNotification extends EditRecord
{
    protected static string $resource = LmsNotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
