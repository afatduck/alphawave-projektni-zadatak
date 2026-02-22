<?php

namespace App\Filament\Resources\Deliveries\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DeliveryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('client_id')
                    ->relationship('client', 'name')
                    ->required(),
                DateTimePicker::make('delivered_at')
                    ->required(),
                TextInput::make('reference'),
                TextInput::make('note'),
            ]);
    }
}
