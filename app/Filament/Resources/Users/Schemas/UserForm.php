<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

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
                    ->searchable()
                    ->live(),

                Section::make('Plan de comisión')
                    ->description('Solo aplica a agentes de venta. Define cómo se calculará su comisión mensual sobre pedidos entregados.')
                    ->relationship('commissionPlan')
                    ->visible(function ($get) {
                        $roleIds = $get('roles') ?? [];

                        return Role::whereIn('id', $roleIds)
                            ->where('name', 'sales_agent')
                            ->exists();
                    })
                    ->schema([
                        Select::make('type')
                            ->label('Tipo de comisión')
                            ->options([
                                'flat_percent' => 'Porcentaje fijo del pedido',
                                'volume_bonus' => 'Bono por tramo de volumen mensual',
                                'tiered_percent' => 'Porcentaje escalonado (con deducción)',
                            ])
                            ->default('flat_percent')
                            ->live()
                            ->required(),

                        TextInput::make('config.percent')
                            ->label('Porcentaje del pedido')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->visible(fn($get) => $get('type') === 'flat_percent')
                            ->required(fn($get) => $get('type') === 'flat_percent'),

                        Repeater::make('config.tiers')
                            ->label('Tramos de bono mensual')
                            ->schema([
                                TextInput::make('threshold_lps')
                                    ->label('Ventas entregadas ≥ (LPS)')
                                    ->numeric()
                                    ->required(),
                                TextInput::make('bonus_usd')
                                    ->label('Bono (USD)')
                                    ->numeric()
                                    ->prefix('$')
                                    ->required(),
                            ])
                            ->columns(2)
                            ->defaultItems(1)
                            ->addActionLabel('Agregar tramo')
                            ->reorderable(false)
                            ->helperText('Solo se paga el tramo más alto alcanzado, no se acumulan.')
                            ->visible(fn($get) => $get('type') === 'volume_bonus')
                            ->required(fn($get) => $get('type') === 'volume_bonus'),

                        Grid::make(4)
                            ->visible(fn($get) => $get('type') === 'tiered_percent')
                            ->schema([
                                TextInput::make('config.deduction_percent')
                                    ->label('Deducción inicial (%)')
                                    ->numeric()
                                    ->suffix('%')
                                    ->required(fn($get) => $get('type') === 'tiered_percent'),
                                TextInput::make('config.threshold_lps')
                                    ->label('Umbral mensual (LPS)')
                                    ->numeric()
                                    ->required(fn($get) => $get('type') === 'tiered_percent'),
                                TextInput::make('config.below_percent')
                                    ->label('% si ventas ≤ umbral')
                                    ->numeric()
                                    ->suffix('%')
                                    ->required(fn($get) => $get('type') === 'tiered_percent'),
                                TextInput::make('config.above_percent')
                                    ->label('% si ventas > umbral')
                                    ->numeric()
                                    ->suffix('%')
                                    ->required(fn($get) => $get('type') === 'tiered_percent'),
                            ]),

                        Toggle::make('is_active')
                            ->label('Plan activo')
                            ->default(true)
                            ->helperText('Desactívalo para dejar de generar comisiones a este agente sin borrar su historial.'),
                    ]),
            ]);
    }
}
