<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\MediaCollection;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Product Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->columnSpanFull()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->prefix('$'),
                        TextInput::make('stock')
                            ->required()
                            ->numeric(),
                        MarkdownEditor::make('description')
                            ->columnSpanFull(),
                    ]),
                Section::make('Cover Image')
                    ->columns(1)
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('cover_image')
                            ->collection(MediaCollection::ProductCoverImage->value)
                            ->image()
                            ->hiddenLabel()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
