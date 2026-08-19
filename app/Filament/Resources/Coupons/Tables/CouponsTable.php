<?php

namespace App\Filament\Resources\Coupons\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CouponsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->weight('bold')
                    ->badge(),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn(string $state) => $state === 'percent' ? 'Porcentaje' : 'Monto fijo')
                    ->color(fn(string $state) => $state === 'percent' ? 'primary' : 'warning'),
                TextColumn::make('value')
                    ->label('Valor')
                    ->formatStateUsing(fn($state, $record) => $record->type === 'percent'
                        ? number_format($state, 2) . '%'
                        : 'L. ' . number_format($state, 2)),
                TextColumn::make('used_count')
                    ->label('Usos')
                    ->formatStateUsing(fn($state, $record) => $record->max_uses
                        ? "{$state} / {$record->max_uses}"
                        : "{$state} / \u{221E}"),
                TextColumn::make('starts_at')
                    ->label('Desde')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—'),
                TextColumn::make('expires_at')
                    ->label('Hasta')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—'),
                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'percent' => 'Porcentaje',
                        'fixed' => 'Monto fijo',
                    ]),
                TernaryFilter::make('is_active')
                    ->label('Activo'),
            ])
            ->defaultSort('created_at', 'desc')
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
