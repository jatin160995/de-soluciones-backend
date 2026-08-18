<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Nombre')
                    ->required(),
                TextInput::make('email')
                    ->label('Correo electrónico')
                    ->email()
                    ->required(),
                DateTimePicker::make('email_verified_at')->label('Correo verificado el'),
                TextInput::make('password')
                    ->password()
                    ->label(fn(string $operation) => $operation === 'create'
                        ? 'Contraseña'
                        : 'Nueva contraseña (déjala en blanco para mantener la actual)')
                    ->required(fn(string $operation): bool => $operation === 'create')
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->dehydrated(fn($state) => filled($state)),
                TextInput::make('phone')->label('Teléfono')
                    ->tel()
                    ->default(null),
                DateTimePicker::make('phone_verified_at')->label('Teléfono verificado el'),
                Select::make('status')
                    ->label('Estado')
                    ->options(['active' => 'Activo', 'inactive' => 'Inactivo', 'banned' => 'Bloqueado'])
                    ->default('active')
                    ->required(),
                Select::make('roles')->label('Roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),
            ]);
    }
}
