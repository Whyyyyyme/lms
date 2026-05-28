<?php

namespace App\Filament\Resources;

use BackedEnum;
use UnitEnum;
use App\Filament\Resources\MaterialResource\Pages;
use App\Models\Material;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MaterialResource extends Resource
{
    protected static ?string $model = Material::class;
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-document-text';
    protected static string | UnitEnum | null $navigationGroup = 'Konten Praktikum';
    protected static ?string $navigationLabel = 'Materi';
    protected static ?string $modelLabel = 'Materi';
    protected static ?string $pluralModelLabel = 'Materi';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Select::make('class_id')->label('Kelas')->relationship('kelas', 'name')->searchable()->preload()->required(),
            Forms\Components\TextInput::make('title')->label('Judul')->required()->maxLength(255),
            Forms\Components\Select::make('type')->label('Tipe')->options(['pdf' => 'PDF', 'video' => 'Video', 'dokumen' => 'Dokumen', 'link' => 'Link'])->required(),
            Forms\Components\FileUpload::make('file_path')->label('File')->disk('public')->directory('materials')->downloadable()->openable(),
            Forms\Components\Textarea::make('description')->label('Deskripsi')->columnSpanFull(),
            Forms\Components\Select::make('created_by')->label('Dibuat Oleh')->relationship('creator', 'name')->searchable()->preload(),
            Forms\Components\DateTimePicker::make('published_at')->label('Dipublikasikan'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('title')->label('Judul')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('kelas.name')->label('Kelas'),
            Tables\Columns\TextColumn::make('type')->label('Tipe')->badge(),
            Tables\Columns\TextColumn::make('creator.name')->label('Pembuat'),
            Tables\Columns\TextColumn::make('published_at')->label('Publikasi')->dateTime('d M Y H:i')->sortable(),
        ])->filters([
            Tables\Filters\SelectFilter::make('type')->label('Tipe')->options(['pdf' => 'PDF', 'video' => 'Video', 'dokumen' => 'Dokumen', 'link' => 'Link']),
        ])->recordActions([
            \Filament\Actions\ViewAction::make(), \Filament\Actions\EditAction::make(), \Filament\Actions\DeleteAction::make(),
        ])->toolbarActions([\Filament\Actions\BulkActionGroup::make([\Filament\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMaterials::route('/'),
            'create' => Pages\CreateMaterial::route('/create'),
            'view' => Pages\ViewMaterial::route('/{record}'),
            'edit' => Pages\EditMaterial::route('/{record}/edit'),
        ];
    }
}
