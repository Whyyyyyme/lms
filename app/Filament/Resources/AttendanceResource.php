<?php

namespace App\Filament\Resources;

use BackedEnum;
use UnitEnum;
use App\Filament\Resources\AttendanceResource\Pages;
use App\Models\Attendance;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-check-circle';
    protected static string | UnitEnum | null $navigationGroup = 'Aktivitas';
    protected static ?string $navigationLabel = 'Absensi';
    protected static ?string $modelLabel = 'Absensi';
    protected static ?string $pluralModelLabel = 'Absensi';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Select::make('class_id')->label('Kelas')->relationship('kelas', 'name')->searchable()->preload()->required(),
            Forms\Components\DatePicker::make('session_date')->label('Tanggal Sesi')->required(),
            Forms\Components\Select::make('opened_by')->label('Dibuka Oleh')->relationship('opener', 'name')->searchable()->preload(),
            Forms\Components\DateTimePicker::make('opened_at')->label('Waktu Dibuka'),
            Forms\Components\DateTimePicker::make('closed_at')->label('Waktu Ditutup'),
            Forms\Components\Toggle::make('is_open')->label('Sedang Dibuka')->default(false),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('kelas.name')->label('Kelas')->searchable(),
            Tables\Columns\TextColumn::make('session_date')->label('Tanggal')->date('d M Y')->sortable(),
            Tables\Columns\TextColumn::make('opener.name')->label('Dibuka Oleh'),
            Tables\Columns\TextColumn::make('records_count')->label('Record')->counts('records'),
            Tables\Columns\IconColumn::make('is_open')->label('Terbuka')->boolean(),
        ])->filters([
            Tables\Filters\TernaryFilter::make('is_open')->label('Status Terbuka'),
        ])->recordActions([
            \Filament\Actions\ViewAction::make(), \Filament\Actions\EditAction::make(), \Filament\Actions\DeleteAction::make(),
        ])->toolbarActions([\Filament\Actions\BulkActionGroup::make([\Filament\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendances::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
            'view' => Pages\ViewAttendance::route('/{record}'),
            'edit' => Pages\EditAttendance::route('/{record}/edit'),
        ];
    }
}
