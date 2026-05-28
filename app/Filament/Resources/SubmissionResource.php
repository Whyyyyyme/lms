<?php

namespace App\Filament\Resources;

use BackedEnum;
use UnitEnum;
use App\Filament\Resources\SubmissionResource\Pages;
use App\Models\Submission;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SubmissionResource extends Resource
{
    protected static ?string $model = Submission::class;
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-inbox-stack';
    protected static string | UnitEnum | null $navigationGroup = 'Aktivitas';
    protected static ?string $navigationLabel = 'Submission';
    protected static ?string $modelLabel = 'Submission';
    protected static ?string $pluralModelLabel = 'Submission';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Select::make('assignment_id')->label('Tugas')->relationship('assignment', 'title')->searchable()->preload()->required(),
            Forms\Components\Select::make('student_id')->label('Mahasiswa')->relationship('student', 'name', modifyQueryUsing: fn ($query) => $query->role('mahasiswa'))->searchable()->preload()->required(),
            Forms\Components\FileUpload::make('file_path')->label('File')->disk('public')->directory('submissions')->downloadable()->openable(),
            Forms\Components\DateTimePicker::make('submitted_at')->label('Waktu Submit'),
            Forms\Components\TextInput::make('score')->label('Nilai')->numeric(),
            Forms\Components\Textarea::make('feedback')->label('Feedback')->columnSpanFull(),
            Forms\Components\DateTimePicker::make('graded_at')->label('Waktu Dinilai'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('student.name')->label('Mahasiswa')->searchable(),
            Tables\Columns\TextColumn::make('assignment.title')->label('Tugas')->searchable(),
            Tables\Columns\TextColumn::make('submitted_at')->label('Submit')->dateTime('d M Y H:i')->sortable(),
            Tables\Columns\TextColumn::make('score')->label('Nilai')->sortable(),
            Tables\Columns\IconColumn::make('graded_at')->label('Dinilai')->boolean(fn ($record): bool => filled($record->graded_at)),
        ])->filters([
            Tables\Filters\TernaryFilter::make('graded_at')->label('Sudah Dinilai')->nullable(),
        ])->recordActions([
            \Filament\Actions\ViewAction::make(), \Filament\Actions\EditAction::make(), \Filament\Actions\DeleteAction::make(),
        ])->toolbarActions([\Filament\Actions\BulkActionGroup::make([\Filament\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubmissions::route('/'),
            'create' => Pages\CreateSubmission::route('/create'),
            'view' => Pages\ViewSubmission::route('/{record}'),
            'edit' => Pages\EditSubmission::route('/{record}/edit'),
        ];
    }
}
