<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Artículos del pedido';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_name')
            ->columns([
                TextColumn::make('product_name')
                    ->label('Producto'),
                TextColumn::make('sku')
                    ->label('SKU'),
                TextColumn::make('variant_attributes')
                    ->label('Variante')
                    ->state(function ($record) {
                        $attrs = $record->variant_attributes;

                        if (empty($attrs) || ! is_array($attrs)) {
                            return '—';
                        }

                        return collect($attrs)
                            ->filter(fn($value) => filled($value))
                            ->map(fn($value, $key) => ucfirst((string) $key) . ': ' . $value)
                            ->implode(', ');
                    }),
                TextColumn::make('unit_price')
                    ->label('Precio unitario')
                    ->money(fn() => config('store.currency', 'HNL')),
                TextColumn::make('quantity')
                    ->label('Cantidad'),
                TextColumn::make('line_total')
                    ->label('Total de línea')
                    ->money(fn() => config('store.currency', 'HNL')),
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
