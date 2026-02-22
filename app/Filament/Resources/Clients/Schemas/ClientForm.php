<?php

namespace App\Filament\Resources\Clients\Schemas;

use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Radio::make('type')
                    ->required()
                    ->live()
                    ->options([
                    'person' => 'Person',
                    'company' => 'Company',
                ]),
                TextInput::make('oib')->requiredIf("type", "company"),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                TextInput::make('phone')->tel(),
                TextInput::make('address'),
            ]);
    }
}
