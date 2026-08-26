<?php

namespace App\Filament\Resources\Users\RelationManagers;

use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CommissionStatementsRelationManager extends RelationManager
{
    protected static string $relationship = 'commissionStatements';

    protected static ?string $title = 'Comisiones mensuales';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('period_month')
            ->defaultSort('period_month', 'desc')
            ->columns([
                TextColumn::make('period_month')
                    ->label('Mes')
                    ->formatStateUsing(fn($state) => ucfirst($state->translatedFormat('F Y'))),
                TextColumn::make('delivered_sales_total')
                    ->label('Ventas entregadas')
                    ->money(fn() => config('store.currency', 'HNL')),
                TextColumn::make('commission_amount')
                    ->label('Comisión')
                    ->formatStateUsing(fn($state, $record) => number_format($state, 2) . ' ' . $record->commission_currency),
                TextColumn::make('markup_bonus_amount')
                    ->label('Bono por sobreprecio')
                    ->formatStateUsing(fn($state) => (float) $state > 0 ? number_format($state, 2) . ' HNL' : '—')
                    ->toggleable(),
                TextColumn::make('total_hnl')
                    ->label('Total')
                    ->formatStateUsing(fn($state, $record) => $state !== null
                        ? number_format($state, 2) . ' HNL'
                        : number_format($record->commission_amount, 2) . ' ' . $record->commission_currency)
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn(string $state) => $state === 'paid' ? 'Pagada' : 'Pendiente')
                    ->color(fn(string $state) => $state === 'paid' ? 'success' : 'warning'),
                TextColumn::make('paid_at')
                    ->label('Pagada el')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—'),
                TextColumn::make('paidBy.name')
                    ->label('Pagada por')
                    ->placeholder('—'),
            ])
            ->headerActions([])
            ->recordActions([
                Action::make('markAsPaid')
                    ->label('Marcar como pagada')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->status === 'pending'
                        && auth()->user()->can('manage_commissions'))
                    ->requiresConfirmation()
                    ->schema([
                        Textarea::make('note')
                            ->label('Nota (opcional)')
                            ->rows(2),
                    ])
                    ->action(function ($record, array $data) {
                        $record->markAsPaid(auth()->user(), $data['note'] ?? null);

                        Notification::make()
                            ->title('Comisión marcada como pagada')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([]);
    }
}
