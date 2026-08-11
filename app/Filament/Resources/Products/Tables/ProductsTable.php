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
                    ->label('Image'),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('store.name')
                    ->label('Store'),

                TextColumn::make('category.name')
                    ->label('Category'),

                TextColumn::make('base_price')
                    ->label('Price')
                    ->money('HNL')
                    ->sortable()
                    ->description(fn ($record) => $record->discounted_price
                        ? 'Now: L. ' . number_format($record->discounted_price, 2)
                        : null),

                TextColumn::make('variants_count')
                    ->counts('variants')
                    ->label('Variants'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'gray',
                        'draft' => 'warning',
                    }),

                IconColumn::make('is_featured')
                    ->boolean(),
            ])
            ->filters([
                //
            ]);
    }
}