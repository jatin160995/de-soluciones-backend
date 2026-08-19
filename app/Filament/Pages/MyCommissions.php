<?php

namespace App\Filament\Pages;

use App\Models\AgentCommissionStatement;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class MyCommissions extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.my-commissions';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $navigationLabel = 'Mis Comisiones';

    protected static ?string $title = 'Mis Comisiones';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('sales_agent') ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(AgentCommissionStatement::query()->where('user_id', auth()->id()))
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
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn(string $state) => $state === 'paid' ? 'Pagada' : 'Pendiente')
                    ->color(fn(string $state) => $state === 'paid' ? 'success' : 'warning'),
                TextColumn::make('paid_at')
                    ->label('Pagada el')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—'),
            ]);
    }
}
