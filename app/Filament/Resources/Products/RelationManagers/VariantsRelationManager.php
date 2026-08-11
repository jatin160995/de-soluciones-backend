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

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('sku')
                ->required()
                ->unique(table: 'product_variants', ignoreRecord: true),

            TextInput::make('price')
                ->numeric()
                ->prefix('L.')
                ->required(),
                TextInput::make('discounted_price')
                    ->numeric()
                    ->prefix('L.')
                    ->nullable()
                    ->helperText('Leave blank if not on sale.')
                    ->lt('price')
                    ->validationMessages([
                        'lt' => 'Discounted price must be lower than price.',
                    ]),

            TextInput::make('stock_quantity')
                ->numeric()
                ->default(0)
                ->required()
                ->helperText('Changes here are logged automatically to inventory_movements.'),

            TextInput::make('size'),

            TextInput::make('color'),

            KeyValue::make('extra_attributes')
                ->label('Other attributes')
                ->keyLabel('Attribute')
                ->valueLabel('Value')
                ->helperText('e.g. material, weight'),

            Toggle::make('is_active')
                ->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('sku')
            ->columns([
                TextColumn::make('sku')->searchable(),
                TextColumn::make('size'),
                TextColumn::make('color'),
                TextColumn::make('price')->money('HNL'),
                TextColumn::make('discounted_price')->money('HNL')->placeholder('—'),
                TextColumn::make('stock_quantity')->label('Stock'),
                IconColumn::make('is_active')->boolean(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}