<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Models\User;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Administrasi';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $navigationLabel = 'Pengguna admin';

    protected static ?string $modelLabel = 'pengguna admin';

    public static function canAccess(): bool
    {
        return auth()->user()?->isOwner() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Akun admin')->schema([
                TextInput::make('name')->label('Nama')->required()->maxLength(255),
                TextInput::make('email')->label('Email')->email()->required()->maxLength(255)->unique(ignoreRecord: true),
                Select::make('role')->label('Peran')->options(['owner' => 'Pemilik', 'editor' => 'Pengelola konten'])
                    ->default('editor')->required()->helperText('Pemilik mengatur pengguna dan pengaturan website. Pengelola konten mengelola konten dan media.'),
                TextInput::make('password')->label('Kata sandi')->password()->revealable()->autocomplete('new-password')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->required(fn (?User $record): bool => $record === null)
                    ->minLength(12)->confirmed(),
                TextInput::make('password_confirmation')->label('Ulangi kata sandi')->password()->revealable()
                    ->dehydrated(false)->required(fn (?User $record): bool => $record === null),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('Nama')->searchable()->sortable(),
            TextColumn::make('email')->label('Email')->searchable()->sortable(),
            TextColumn::make('role')->label('Peran')->badge()->formatStateUsing(fn (string $state): string => $state === 'owner' ? 'Pemilik' : 'Pengelola konten'),
            TextColumn::make('updated_at')->label('Terakhir diubah')->dateTime()->sortable(),
        ])->recordActions([
            EditAction::make(),
            DeleteAction::make()->visible(fn (User $record): bool => ! $record->isLastOwner()),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageUsers::route('/')];
    }
}
