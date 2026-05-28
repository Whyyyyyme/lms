<?php

namespace App\Filament\Resources;

use BackedEnum;
use UnitEnum;
use App\Filament\Resources\AssignmentResource\Pages;
use App\Models\Assignment;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AssignmentResource extends Resource
{
    protected static ?string $model = Assignment::class;
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static string | UnitEnum | null $navigationGroup = 'Konten Praktikum';
    protected static ?string $navigationLabel = 'Tugas';
    protected static ?string $modelLabel = 'Tugas';
    protected static ?string $pluralModelLabel = 'Tugas';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Select::make('class_id')->label('Kelas')->relationship('kelas', 'name')->searchable()->preload()->required(),
            Forms\Components\TextInput::make('title')->label('Judul')->required()->maxLength(255),
            Forms\Components\Textarea::make('description')->label('Deskripsi')->columnSpanFull(),
            Forms\Components\FileUpload::make('file_path')->label('File')->disk('public')->directory('assignments')->downloadable()->openable(),
            Forms\Components\DateTimePicker::make('deadline')->label('Deadline')->required(),
            Forms\Components\TextInput::make('max_score')->label('Nilai Maksimal')->numeric()->default(100)->required(),
            Forms\Components\Select::make('created_by')->label('Dibuat Oleh')->relationship('creator', 'name')->searchable()->preload(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('title')->label('Judul')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('kelas.name')->label('Kelas'),
            Tables\Columns\TextColumn::make('deadline')->label('Deadline')->dateTime('d M Y H:i')->sortable(),
            Tables\Columns\TextColumn::make('max_score')->label('Nilai Maks.'),
            Tables\Columns\TextColumn::make('submissions_count')->label('Submission')->counts('submissions'),
        ])->recordActions([
            \Filament\Actions\ViewAction::make(), \Filament\Actions\EditAction::make(), \Filament\Actions\DeleteAction::make(),
        ])->toolbarActions([\Filament\Actions\BulkActionGroup::make([\Filament\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssignments::route('/'),
            'create' => Pages\CreateAssignment::route('/create'),
            'view' => Pages\ViewAssignment::route('/{record}'),
            'edit' => Pages\EditAssignment::route('/{record}/edit'),
        ];
    }
}
