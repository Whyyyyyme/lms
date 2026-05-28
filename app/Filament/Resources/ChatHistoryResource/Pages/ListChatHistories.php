<?php

namespace App\Filament\Resources\ChatHistoryResource\Pages;

use App\Filament\Resources\ChatHistoryResource;
use Filament\Resources\Pages\ListRecords;

class ListChatHistories extends ListRecords
{
    protected static string $resource = ChatHistoryResource::class;
}
