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

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Product details')
                ->columns(2)
                ->schema([
                    Select::make('store_id')
                        ->label('Store')
                        ->options(Store::pluck('name', 'id'))
                        ->default(fn () => Store::first()?->id)
                        ->required()
                        ->searchable(),

                    Select::make('category_id')
                        ->label('Category')
                        ->relationship('category', 'name', fn ($query) => $query->where('type', 'product'))
                        ->searchable()
                        ->preload(),

                    TextInput::make('name')
                        ->required()
                        ->maxLength(180)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),

                    TextInput::make('slug')
                        ->required()
                        ->maxLength(200)
                        ->unique(ignoreRecord: true),

                    Textarea::make('description')
                        ->columnSpanFull(),

                    TextInput::make('base_price')
                        ->label('Base price (HNL)')
                        ->numeric()
                        ->prefix('L.')
                        ->required(),
                        TextInput::make('discounted_price')
                            ->label('Discounted price (HNL)')
                            ->numeric()
                            ->prefix('L.')
                            ->nullable()
                            ->helperText('Leave blank if not on sale.')
                            ->lt('base_price')
                            ->validationMessages([
                                'lt' => 'Discounted price must be lower than the base price.',
                            ]),

                    Select::make('status')
                        ->options(['draft' => 'Draft', 'active' => 'Active', 'inactive' => 'Inactive'])
                        ->default('draft')
                        ->required(),

                    Toggle::make('is_featured'),
                ]),

            Section::make('Images')
                ->schema([
                    SpatieMediaLibraryFileUpload::make('images')
                        ->collection('images')
                        ->multiple()
                        ->reorderable()
                        ->appendFiles()
                        ->panelLayout('grid')
                        ->imageEditor()
                        ->maxSize(5120),
                ]),
        ]);
    }
}