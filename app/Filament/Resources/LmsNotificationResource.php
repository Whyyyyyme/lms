<?php

namespace App\Filament\Resources;

use BackedEnum;
use UnitEnum;
use App\Filament\Resources\LmsNotificationResource\Pages;
use App\Models\LmsNotification;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LmsNotificationResource extends Resource
{
    protected static ?string $model = LmsNotification::class;
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-bell';
    protected static string | UnitEnum | null $navigationGroup = 'Aktivitas';
    protected static ?string $navigationLabel = 'Notifikasi';
    protected static ?string $modelLabel = 'Notifikasi';
    protected static ?string $pluralModelLabel = 'Notifikasi';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\TextInput::make('type')->label('Tipe')->required(),
            Forms\Components\Select::make('user_id')->label('User')->relationship('user', 'name')->searchable()->preload(),
            Forms\Components\TextInput::make('title')->label('Judul')->maxLength(255),
            Forms\Components\Textarea::make('message')->label('Pesan')->columnSpanFull(),
            Forms\Components\DateTimePicker::make('read_at')->label('Dibaca Pada'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('title')->label('Judul')->searchable(),
            Tables\Columns\TextColumn::make('user.name')->label('User')->searchable(),
            Tables\Columns\TextColumn::make('type')->label('Tipe')->badge(),
            Tables\Columns\IconColumn::make('read_at')->label('Dibaca')->boolean(fn ($record): bool => filled($record->read_at)),
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
            'index' => Pages\ListLmsNotifications::route('/'),
            'view' => Pages\ViewLmsNotification::route('/{record}'),
            'edit' => Pages\EditLmsNotification::route('/{record}/edit'),
        ];
    }
}
