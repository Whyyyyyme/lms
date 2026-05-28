<?php

namespace App\Filament\Resources\LmsNotificationResource\Pages;

use App\Filament\Resources\LmsNotificationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewLmsNotification extends ViewRecord
{
    protected static string $resource = LmsNotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
