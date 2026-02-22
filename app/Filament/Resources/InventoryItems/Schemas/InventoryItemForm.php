<?php

namespace App\Filament\Resources\InventoryItems\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class InventoryItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->required(),
                TextInput::make('serial_number')
                    ->required(),
                Radio::make('status')
                    ->required()
                    ->live()
                    ->options([
                    'in_stock' => 'In stock',
                    'delivered' => 'Delivered',
                ]),
                DatePicker::make('purchased_at'),
                DatePicker::make('warranty_expires_at'),
                TextInput::make('notes'),
            ]);
    }
}
