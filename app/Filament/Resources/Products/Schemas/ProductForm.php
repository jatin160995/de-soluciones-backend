<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Store;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use App\Filament\Schemas\Components\SeoMetaSection;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Detalles del producto')
                ->columns(2)
                ->schema([
                    Select::make('store_id')
                        ->label('Tienda')
                        ->options(Store::pluck('name', 'id'))
                        ->default(fn() => Store::first()?->id)
                        ->required()
                        ->searchable(),

                    Select::make('category_id')
                        ->label('Categoría')
                        ->relationship('category', 'name', fn($query) => $query->where('type', 'product'))
                        ->searchable()
                        ->preload(),

                    TextInput::make('name')
                        ->label('Nombre')
                        ->required()
                        ->maxLength(180)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state))),

                    TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->maxLength(200)
                        ->unique(ignoreRecord: true),

                    Textarea::make('description')
                        ->label('Descripción')
                        ->columnSpanFull(),

                    TextInput::make('base_price')
                        ->label('Precio base (' . config('store.currency') . ')')
                        ->numeric()
                        ->prefix(config('store.currency_symbol'))
                        ->required(),

                    TextInput::make('discounted_price')
                        ->label('Precio con descuento (' . config('store.currency') . ')')
                        ->numeric()
                        ->prefix(config('store.currency_symbol'))
                        ->nullable()
                        ->helperText('Déjalo en blanco si no está en oferta.')
                        ->lt('base_price')
                        ->validationMessages([
                            'lt' => 'El precio con descuento debe ser menor que el precio base.',
                        ]),

                    Select::make('status')
                        ->label('Estado')
                        ->options(['draft' => 'Borrador', 'active' => 'Activo', 'inactive' => 'Inactivo'])
                        ->default('draft')
                        ->required(),


                    Toggle::make('is_featured')
                        ->label('Destacado'),
                ]),

            Section::make('Imágenes')
                ->schema([
                    SpatieMediaLibraryFileUpload::make('images')
                        ->label('Imágenes')
                        ->collection('images')
                        ->multiple()
                        ->reorderable()
                        ->appendFiles()
                        ->panelLayout('grid')
                        ->imageEditor()
                        ->maxSize(5120),
                    SeoMetaSection::make(),
                ]),

        ]);
    }
}
