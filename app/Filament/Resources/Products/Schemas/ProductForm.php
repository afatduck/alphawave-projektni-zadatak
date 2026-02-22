<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('sku')
                    ->label('SKU'),
                TextInput::make('manufacturer'),
                TextInput::make('warranty_months')
                    ->required()
                    ->numeric()
                    ->default(24),
            ]);
    }
}
