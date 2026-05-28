<?php

namespace App\Filament\Resources;

use BackedEnum;
use UnitEnum;
use App\Filament\Resources\ChatHistoryResource\Pages;
use App\Models\ChatHistory;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ChatHistoryResource extends Resource
{
    protected static ?string $model = ChatHistory::class;
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static string | UnitEnum | null $navigationGroup = 'Aktivitas';
    protected static ?string $navigationLabel = 'Riwayat Chatbot';
    protected static ?string $modelLabel = 'Riwayat Chatbot';
    protected static ?string $pluralModelLabel = 'Riwayat Chatbot';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Select::make('user_id')->label('User')->relationship('user', 'name')->searchable()->preload()->required(),
            Forms\Components\Select::make('role')->label('Role Chat')->options(['user' => 'User', 'assistant' => 'Assistant'])->required(),
            Forms\Components\Textarea::make('message')->label('Pesan')->required()->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('user.name')->label('User')->searchable(),
            Tables\Columns\TextColumn::make('role')->label('Role')->badge(),
            Tables\Columns\TextColumn::make('message')->label('Pesan')->limit(80)->searchable(),
            Tables\Columns\TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y H:i')->sortable(),
        ])->recordActions([
            \Filament\Actions\ViewAction::make(), \Filament\Actions\EditAction::make(), \Filament\Actions\DeleteAction::make(),
        ])->toolbarActions([\Filament\Actions\BulkActionGroup::make([\Filament\Actions\DeleteBulkAction::make()])]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChatHistories::route('/'),
            'view' => Pages\ViewChatHistory::route('/{record}'),
            'edit' => Pages\EditChatHistory::route('/{record}/edit'),
        ];
    }
}
