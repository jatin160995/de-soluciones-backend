<?php

namespace App\Filament\Resources\Stores\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use App\Models\User;
use Illuminate\Support\Str;

class StoreForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('owner_user_id')
                    ->label('Owner')
                    ->relationship('owner', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('name')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true),
                Textarea::make('description')
                    ->default(null)
                    ->columnSpanFull(),
                SpatieMediaLibraryFileUpload::make('logo')
                    ->collection('logo')
                    ->image()
                    ->imageEditor()
                    ->avatar(),
                TextInput::make('whatsapp_number')
                    ->tel()
                    ->default(null),
                Select::make('status')
                    ->options(['active' => 'Active', 'inactive' => 'Inactive', 'suspended' => 'Suspended'])
                    ->default('active')
                    ->required(),
                TextInput::make('commission_rate')
                    ->required()
                    ->numeric()
                    ->default(0.0)
                    ->suffix('%'),
            ]);
    }
}