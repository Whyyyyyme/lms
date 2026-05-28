<?php

namespace App\Filament\Resources;

use BackedEnum;
use UnitEnum;
use App\Filament\Resources\AcademicYearResource\Pages;
use App\Models\AcademicYear;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AcademicYearResource extends Resource
{
    protected static ?string $model = AcademicYear::class;
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-calendar-days';
    protected static string | UnitEnum | null $navigationGroup = 'Akademik';
    protected static ?string $navigationLabel = 'Tahun Akademik';
    protected static ?string $modelLabel = 'Tahun Akademik';
    protected static ?string $pluralModelLabel = 'Tahun Akademik';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\TextInput::make('year')->label('Tahun Akademik')->placeholder('2025/2026')->required()->maxLength(20),
            Forms\Components\Select::make('semester')->label('Semester')->options(['ganjil' => 'Ganjil', 'genap' => 'Genap'])->required(),
            Forms\Components\Toggle::make('is_active')->label('Aktif')->default(false),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('year')->label('Tahun')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('semester')->label('Semester')->badge(),
            Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
            Tables\Columns\TextColumn::make('courses_count')->label('Matakuliah')->counts('courses'),
        ])->recordActions([
            \Filament\Actions\ViewAction::make(),
            \Filament\Actions\EditAction::make(),
            \Filament\Actions\DeleteAction::make(),
        ])->toolbarActions([\Filament\Actions\BulkActionGroup::make([\Filament\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAcademicYears::route('/'),
            'create' => Pages\CreateAcademicYear::route('/create'),
            'view' => Pages\ViewAcademicYear::route('/{record}'),
            'edit' => Pages\EditAcademicYear::route('/{record}/edit'),
        ];
    }
}
