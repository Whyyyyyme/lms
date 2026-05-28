<?php

namespace App\Filament\Resources;

use BackedEnum;
use UnitEnum;
use App\Filament\Resources\UserResource\Pages;
use App\Models\PraktikumClass;
use App\Models\User;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-users';
    protected static string | UnitEnum | null $navigationGroup = 'Manajemen User';
    protected static ?string $navigationLabel = 'User';
    protected static ?string $modelLabel = 'User';
    protected static ?string $pluralModelLabel = 'User';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Section::make('Informasi Akun')->schema([
                Forms\Components\TextInput::make('name')->label('Nama Lengkap')->required()->maxLength(255),
                Forms\Components\TextInput::make('nim_nip')->label('NIM / NIP')->maxLength(50)->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('email')->label('Email')->email()->required()->maxLength(255)->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->maxLength(255)
                    ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? Hash::make($state) : null)
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create'),
                Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true),
            ])->columns(2),
            Forms\Components\Section::make('Role & Kelas')->schema([
                Forms\Components\Select::make('roles')
                    ->label('Role')
                    ->relationship('roles', 'name')
                    ->options(fn () => Role::query()->pluck('name', 'id'))
                    ->preload()
                    ->searchable()
                    ->multiple()
                    ->required(),
                Forms\Components\Select::make('kelas_id')
                    ->label('Kelas Utama')
                    ->options(fn () => PraktikumClass::query()->with('course')->get()->mapWithKeys(fn ($class) => [$class->id => $class->course?->name.' - '.$class->name]))
                    ->searchable()
                    ->preload(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('nim_nip')->label('NIM/NIP')->searchable(),
                Tables\Columns\TextColumn::make('email')->label('Email')->searchable(),
                Tables\Columns\TextColumn::make('roles.name')->label('Role')->badge(),
                Tables\Columns\TextColumn::make('kelas.name')->label('Kelas'),
                Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y H:i')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')->label('Role')->relationship('roles', 'name')->multiple(),
                Tables\Filters\TernaryFilter::make('is_active')->label('Status Aktif'),
            ])
            ->recordActions([
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->toolbarActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
