<?php

namespace App\Filament\Resources\Coupons\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)->schema([
                    TextInput::make('code')
                        ->label('Código')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->helperText('Se guarda en mayúsculas automáticamente, ej: BIENVENIDA10.')
                        ->maxLength(40),
                    Select::make('type')
                        ->label('Tipo de descuento')
                        ->options([
                            'percent' => 'Porcentaje (%)',
                            'fixed' => 'Monto fijo (L.)',
                        ])
                        ->default('percent')
                        ->live()
                        ->required(),
                ]),

                Grid::make(2)->schema([
                    TextInput::make('value')
                        ->label(fn($get) => $get('type') === 'fixed' ? 'Monto del descuento' : 'Porcentaje del descuento')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(fn($get) => $get('type') === 'percent' ? 100 : null)
                        ->prefix(fn($get) => $get('type') === 'fixed' ? 'L.' : null)
                        ->suffix(fn($get) => $get('type') === 'percent' ? '%' : null)
                        ->required(),
                    TextInput::make('max_uses')
                        ->label('Límite de usos')
                        ->numeric()
                        ->minValue(1)
                        ->placeholder('Ilimitado')
                        ->helperText('Déjalo vacío para que no tenga límite de uso.'),
                ]),

                TextInput::make('used_count')
                    ->label('Usos hasta ahora')
                    ->numeric()
                    ->default(0)
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('Este número lo actualiza el sistema automáticamente cuando un cliente use el cupón.'),

                Grid::make(2)->schema([
                    DateTimePicker::make('starts_at')
                        ->label('Válido desde')
                        ->native(false)
                        ->helperText('Déjalo vacío para que esté disponible desde ya.'),
                    DateTimePicker::make('expires_at')
                        ->label('Válido hasta')
                        ->native(false)
                        ->helperText('Déjalo vacío para que no expire.'),
                ]),

                Toggle::make('is_active')
                    ->label('Activo')
                    ->default(true)
                    ->helperText('Desactívalo para pausar el cupón sin borrarlo.'),
            ]);
    }
}
