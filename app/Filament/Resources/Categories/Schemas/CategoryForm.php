<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use App\Filament\Schemas\Components\SeoMetaSection;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('parent_id')
                    ->label('Categoría padre')
                    ->relationship(
                        'parent',
                        'name',
                        modifyQueryUsing: fn($query, $record) => $record
                            ? $query->whereKeyNot($record->id)
                            : $query,
                    )
                    ->searchable()
                    ->preload()
                    ->default(null),
                Hidden::make('type')->label('Nombre')
                    ->default('product'),
                TextInput::make('name')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state))),
                TextInput::make('slug')->label('Slug')
                    ->required()
                    ->unique(ignoreRecord: true),
                SpatieMediaLibraryFileUpload::make('image')->label('Imagen')
                    ->collection('image')
                    ->image()
                    ->imageEditor(),
                SeoMetaSection::make(),
                TextInput::make('sort_order')->label('Orden')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')->label('Activo')
                    ->required()
                    ->default(true),
            ]);
    }
}
