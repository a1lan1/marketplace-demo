<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Infolists;

use App\Enums\MediaCollection;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make()
                    ->columns(1)
                    ->schema([
                        SpatieMediaLibraryImageEntry::make('cover_image')
                            ->collection(MediaCollection::ProductCoverImage->value)
                            ->imageWidth('100%')
                            ->imageHeight('100%')
                            ->hiddenLabel(),
                    ]),
                Section::make('Product Details')
                    ->columns(1)
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('description'),
                        TextEntry::make('price')
                            ->money('USD'),
                        TextEntry::make('stock'),
                        TextEntry::make('seller.name')
                            ->label('Seller'),
                        TextEntry::make('image_tags')
                            ->badge()
                            ->separator(',')
                            ->label('Image Tags'),
                        TextEntry::make('image_moderation_status')
                            ->badge()
                            ->label('Image Moderation Status'),
                        TextEntry::make('created_at')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->dateTime(),
                    ]),
            ]);
    }
}
