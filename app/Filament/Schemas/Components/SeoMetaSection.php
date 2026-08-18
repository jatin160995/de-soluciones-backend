<?php

namespace App\Filament\Schemas\Components;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;

class SeoMetaSection
{
    public static function make(): Section
    {
        return Section::make('SEO')
            ->description('Optional — leave blank to auto-generate from the name/description.')
            ->collapsible()
            ->collapsed()
            ->schema([
                Group::make([
                    TextInput::make('meta_title')
                        ->label('Meta title')
                        ->maxLength(160)
                        ->helperText('Recommended: under 60 characters.'),

                    Textarea::make('meta_description')
                        ->label('Meta description')
                        ->maxLength(320)
                        ->rows(3)
                        ->helperText('Recommended: under 155 characters.'),

                    FileUpload::make('og_image_path')
                        ->label('Social share image (OG image)')
                        ->image()
                        ->directory('seo')
                        ->helperText('Falls back to the main product/category image if left blank.'),
                ])->relationship('seoMeta'),
            ]);
    }
}
