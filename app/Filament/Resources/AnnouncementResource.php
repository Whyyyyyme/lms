<?php

namespace App\Filament\Resources;

use BackedEnum;
use UnitEnum;
use App\Filament\Resources\AnnouncementResource\Pages;
use App\Models\Announcement;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AnnouncementResource extends Resource
{
    protected static ?string $model = Announcement::class;
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-megaphone';
    protected static string | UnitEnum | null $navigationGroup = 'Konten Praktikum';
    protected static ?string $navigationLabel = 'Pengumuman';
    protected static ?string $modelLabel = 'Pengumuman';
    protected static ?string $pluralModelLabel = 'Pengumuman';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Select::make('class_id')->label('Kelas')->relationship('kelas', 'name')->searchable()->preload()->required(),
            Forms\Components\TextInput::make('title')->label('Judul')->required()->maxLength(255),
            Forms\Components\Textarea::make('content')->label('Isi Pengumuman')->required()->columnSpanFull(),
            Forms\Components\Select::make('created_by')->label('Dibuat Oleh')->relationship('creator', 'name')->searchable()->preload(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('title')->label('Judul')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('kelas.name')->label('Kelas'),
            Tables\Columns\TextColumn::make('creator.name')->label('Pembuat'),
            Tables\Columns\TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y H:i')->sortable(),
        ])->recordActions([
            \Filament\Actions\ViewAction::make(), \Filament\Actions\EditAction::make(), \Filament\Actions\DeleteAction::make(),
        ])->toolbarActions([\Filament\Actions\BulkActionGroup::make([\Filament\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnnouncements::route('/'),
            'create' => Pages\CreateAnnouncement::route('/create'),
            'view' => Pages\ViewAnnouncement::route('/{record}'),
            'edit' => Pages\EditAnnouncement::route('/{record}/edit'),
        ];
    }
}
