<?php

namespace App\Filament\Resources\HeroBanners\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HeroBannersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')
                    ->collection('image')
                    ->disk('public')
                    ->label(''),
                TextColumn::make('title')->label('Título')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('link_type')->label('Enlace')
                    ->badge()
                    ->formatStateUsing(fn(string $state) => match ($state) {
                        'product' => 'Producto',
                        'category' => 'Categoría',
                        'url' => 'URL',
                        default => 'Ninguno',
                    }),
                TextColumn::make('sort_order')->label('Orden')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')->label('Activo')
                    ->boolean(),
                TextColumn::make('created_at')->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
