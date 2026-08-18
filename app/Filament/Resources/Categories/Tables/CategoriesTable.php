<?php

namespace App\Filament\Resources\Categories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')
                    ->collection('image')
                    ->label('')
                    ->circular(),
                TextColumn::make('parent.name')->label('Categoría padre')
                    ->searchable(),
                TextColumn::make('type')->label('Tipo')
                    ->badge(),
                TextColumn::make('name')->label('Nombre')
                    ->searchable(),
                TextColumn::make('slug')->label('Slug')
                    ->searchable(),
                TextColumn::make('sort_order')->label('Orden')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')->label('Activo')
                    ->boolean(),
                TextColumn::make('created_at')->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label('Actualizado')
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
