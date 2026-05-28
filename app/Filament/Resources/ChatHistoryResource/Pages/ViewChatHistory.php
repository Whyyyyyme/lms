<?php

namespace App\Filament\Resources\ChatHistoryResource\Pages;

use App\Filament\Resources\ChatHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewChatHistory extends ViewRecord
{
    protected static string $resource = ChatHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
