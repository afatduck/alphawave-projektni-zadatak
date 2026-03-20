<?php

namespace App\Filament\Resources\DeliveryItems\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DeliveryItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('delivery.client.name')
                    ->label('Client')
                    ->searchable(),
                TextColumn::make('inventoryItem.product.name')
                    ->label('Product')
                    ->searchable(),
                TextColumn::make('longitude')
                    ->numeric(decimalPlaces: 4),
                TextColumn::make('latitude')
                    ->numeric(decimalPlaces: 4),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
