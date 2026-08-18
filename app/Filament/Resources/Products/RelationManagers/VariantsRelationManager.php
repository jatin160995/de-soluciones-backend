<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return 'Variantes';
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('sku')
                ->label('SKU')
                ->required()
                ->unique(table: 'product_variants', ignoreRecord: true),

            TextInput::make('price')
                ->label('Precio')
                ->numeric()
                ->prefix(config('store.currency_symbol'))
                ->required(),

            TextInput::make('discounted_price')
                ->label('Precio con descuento')
                ->numeric()
                ->prefix(config('store.currency_symbol'))
                ->nullable()
                ->helperText('Déjalo en blanco si no está en oferta.')
                ->lt('price')
                ->validationMessages([
                    'lt' => 'El precio con descuento debe ser menor que el precio.',
                ]),

            TextInput::make('stock_quantity')
                ->label('Cantidad en stock')
                ->numeric()
                ->default(0)
                ->required()
                ->helperText('Los cambios aquí se registran automáticamente en inventory_movements.'),

            TextInput::make('size')
                ->label('Talla'),

            TextInput::make('color')
                ->label('Color'),

            KeyValue::make('extra_attributes')
                ->label('Otros atributos')
                ->keyLabel('Atributo')
                ->valueLabel('Valor')
                ->helperText('Ej: material, peso'),

            Toggle::make('is_active')
                ->label('Activo')
                ->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('sku')
            ->columns([
                TextColumn::make('sku')->label('SKU')->searchable(),
                TextColumn::make('size')->label('Talla'),
                TextColumn::make('color')->label('Color'),
                TextColumn::make('price')->label('Precio')->money(config('store.currency')),
                TextColumn::make('discounted_price')->label('Precio con descuento')->money(config('store.currency'))->placeholder('—'),
                TextColumn::make('stock_quantity')->label('Stock'),
                IconColumn::make('is_active')->label('Activo')->boolean(),
            ])
            ->headerActions([
                CreateAction::make()->label('Nueva variante'),
            ])
            ->actions([
                EditAction::make()->label('Editar'),
                DeleteAction::make()->label('Eliminar'),
            ]);
    }
}
