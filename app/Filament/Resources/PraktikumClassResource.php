<?php

namespace App\Filament\Resources;

use BackedEnum;
use UnitEnum;
use App\Filament\Resources\PraktikumClassResource\Pages;
use App\Models\PraktikumClass;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PraktikumClassResource extends Resource
{
    protected static ?string $model = PraktikumClass::class;
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-academic-cap';
    protected static string | UnitEnum | null $navigationGroup = 'Akademik';
    protected static ?string $navigationLabel = 'Kelas Praktikum';
    protected static ?string $modelLabel = 'Kelas Praktikum';
    protected static ?string $pluralModelLabel = 'Kelas Praktikum';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Section::make('Informasi Kelas')->schema([
                Forms\Components\Select::make('course_id')->label('Matakuliah')->relationship('course', 'name')->searchable()->preload()->required(),
                Forms\Components\Select::make('assistant_id')->label('Asisten')->relationship('assistant', 'name', modifyQueryUsing: fn ($query) => $query->role('asisten'))->searchable()->preload(),
                Forms\Components\TextInput::make('name')->label('Nama Kelas')->required()->maxLength(100),
                Forms\Components\TextInput::make('room')->label('Ruangan')->maxLength(100),
                Forms\Components\TextInput::make('schedule')->label('Jadwal')->maxLength(255),
                Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true),
            ])->columns(2),
            Forms\Components\Section::make('Mahasiswa Manual / Khusus')->description('Akses utama mahasiswa mengikuti semester mata kuliah. Pilihan ini hanya untuk pembagian khusus/manual.')->schema([
                Forms\Components\Select::make('students')
                    ->label('Mahasiswa Manual / Khusus')
                    ->relationship('students', 'name', modifyQueryUsing: fn ($query) => $query->role('mahasiswa'))
                    ->multiple()
                    ->searchable()
                    ->preload(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->label('Kelas')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('course.name')->label('Matakuliah')->searchable(),
            Tables\Columns\TextColumn::make('assistant.name')->label('Asisten')->searchable(),
            Tables\Columns\TextColumn::make('schedule')->label('Jadwal'),
            Tables\Columns\TextColumn::make('students_count')->label('Mahasiswa Manual')->counts('students'),
            Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
        ])->filters([
            Tables\Filters\SelectFilter::make('course_id')->label('Matakuliah')->relationship('course', 'name'),
            Tables\Filters\TernaryFilter::make('is_active')->label('Status Aktif'),
        ])->recordActions([
            \Filament\Actions\ViewAction::make(), \Filament\Actions\EditAction::make(), \Filament\Actions\DeleteAction::make(),
        ])->toolbarActions([\Filament\Actions\BulkActionGroup::make([\Filament\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPraktikumClasses::route('/'),
            'create' => Pages\CreatePraktikumClass::route('/create'),
            'view' => Pages\ViewPraktikumClass::route('/{record}'),
            'edit' => Pages\EditPraktikumClass::route('/{record}/edit'),
        ];
    }
}
