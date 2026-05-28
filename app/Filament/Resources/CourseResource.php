<?php

namespace App\Filament\Resources;

use BackedEnum;
use UnitEnum;
use App\Filament\Resources\CourseResource\Pages;
use App\Models\Course;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-book-open';
    protected static string | UnitEnum | null $navigationGroup = 'Akademik';
    protected static ?string $navigationLabel = 'Matakuliah';
    protected static ?string $modelLabel = 'Matakuliah';
    protected static ?string $pluralModelLabel = 'Matakuliah';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Select::make('academic_year_id')->label('Tahun Akademik')->relationship('academicYear', 'year')->searchable()->preload()->required(),
            Forms\Components\TextInput::make('name')->label('Nama Matakuliah')->required()->maxLength(255),
            Forms\Components\TextInput::make('code')->label('Kode')->required()->maxLength(50)->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('sks')->label('SKS')->numeric()->minValue(1)->maxValue(6)->required(),
            Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->label('Nama')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('code')->label('Kode')->searchable(),
            Tables\Columns\TextColumn::make('academicYear.year')->label('Tahun'),
            Tables\Columns\TextColumn::make('sks')->label('SKS')->sortable(),
            Tables\Columns\TextColumn::make('classes_count')->label('Kelas')->counts('classes'),
            Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
        ])->filters([
            Tables\Filters\SelectFilter::make('academic_year_id')->label('Tahun Akademik')->relationship('academicYear', 'year'),
            Tables\Filters\TernaryFilter::make('is_active')->label('Status Aktif'),
        ])->recordActions([
            \Filament\Actions\ViewAction::make(), \Filament\Actions\EditAction::make(), \Filament\Actions\DeleteAction::make(),
        ])->toolbarActions([\Filament\Actions\BulkActionGroup::make([\Filament\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'view' => Pages\ViewCourse::route('/{record}'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
        ];
    }
}
