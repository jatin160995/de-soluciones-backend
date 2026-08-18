<?php

namespace App\Filament\Resources\Stores\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use App\Models\User;
use Illuminate\Support\Str;
use App\Filament\Schemas\Components\SeoMetaSection;

class StoreForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('owner_user_id')
                    ->label('Propietario')
                    ->relationship('owner', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('name')->label('Nombre')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state))),
                TextInput::make('slug')->label('Slug')
                    ->required()
                    ->unique(ignoreRecord: true),
                Textarea::make('description')->label('Descripción')
                    ->default(null)
                    ->columnSpanFull(),
                SpatieMediaLibraryFileUpload::make('logo')->label('Logo')
                    ->collection('logo')
                    ->image()
                    ->imageEditor()
                    ->avatar(),
                TextInput::make('whatsapp_number')->label('Número de WhatsApp')
                    ->tel()
                    ->default(null),
                SeoMetaSection::make(),
                Select::make('status')->label('Estado')
                    ->options(['active' => 'Active', 'inactive' => 'Inactive', 'suspended' => 'Suspended'])
                    ->default('active')
                    ->required(),
                TextInput::make('commission_rate')->label('Tasa de comisión')
                    ->required()
                    ->numeric()
                    ->default(0.0)
                    ->suffix('%'),
            ]);
    }
}
