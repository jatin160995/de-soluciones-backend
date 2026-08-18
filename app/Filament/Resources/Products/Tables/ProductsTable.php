<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('images')
                    ->collection('images')
                    ->conversion('thumb')
                    ->label('Imagen'),

                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('store.name')
                    ->label('Tienda'),

                TextColumn::make('category.name')
                    ->label('Categoría'),

                TextColumn::make('base_price')
                    ->label('Precio')
                    ->money(config('store.currency'))
                    ->sortable()
                    ->description(fn($record) => $record->discounted_price
                        ? 'Ahora: ' . config('store.currency_symbol') . ' ' . number_format($record->discounted_price, 2)
                        : null),

                TextColumn::make('variants_count')
                    ->counts('variants')
                    ->label('Variantes'),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'active' => 'Activo',
                        'inactive' => 'Inactivo',
                        'draft' => 'Borrador',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'gray',
                        'draft' => 'warning',
                    }),

                IconColumn::make('is_featured')
                    ->label('Destacado')
                    ->boolean(),
            ])
            ->filters([
                //
            ]);
    }
}
