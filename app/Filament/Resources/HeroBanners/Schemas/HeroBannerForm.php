<?php

namespace App\Filament\Resources\HeroBanners\Schemas;

use App\Models\Category;
use App\Models\Product;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class HeroBannerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                SpatieMediaLibraryFileUpload::make('image')
                    ->label('Imagen del banner')
                    ->collection('image')
                    ->disk('public')
                    ->image()
                    ->imageEditor()
                    ->required()
                    ->columnSpanFull(),

                TextInput::make('title')
                    ->label('Título interno (solo referencia en el admin)')
                    ->maxLength(150)
                    ->columnSpanFull(),

                Select::make('link_type')
                    ->label('Este banner enlaza a...')
                    ->options([
                        'none' => 'Nada (solo imagen)',
                        'product' => 'Un producto',
                        'category' => 'Una categoría',
                        'url' => 'Una URL personalizada',
                    ])
                    ->default('none')
                    ->live()
                    ->required(),

                Select::make('product_id')
                    ->label('Producto')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn($get) => $get('link_type') === 'product')
                    ->required(fn($get) => $get('link_type') === 'product'),

                Select::make('category_id')
                    ->label('Categoría')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn($get) => $get('link_type') === 'category')
                    ->required(fn($get) => $get('link_type') === 'category'),

                TextInput::make('external_url')
                    ->label('URL')
                    ->url()
                    ->placeholder('https://...')
                    ->visible(fn($get) => $get('link_type') === 'url')
                    ->required(fn($get) => $get('link_type') === 'url'),

                TextInput::make('sort_order')
                    ->label('Orden')
                    ->numeric()
                    ->default(0)
                    ->required(),

                Toggle::make('is_active')
                    ->label('Activo')
                    ->default(true)
                    ->required(),
            ]);
    }
}
