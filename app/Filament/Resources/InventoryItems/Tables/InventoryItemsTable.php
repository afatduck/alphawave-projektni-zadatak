<?php

namespace App\Filament\Resources\InventoryItems\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InventoryItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->searchable(),
                TextColumn::make('serial_number')
                    ->searchable(),
                TextColumn::make('status'),
                TextColumn::make('purchased_at')
                    ->date(),
                TextColumn::make('warranty_expires_at_dynamic')
                    ->label("Warranty expires at")
                    ->date()
                    ->sortable(),
                TextColumn::make('notes')
            ])
            ->filters([
                Filter::make("warranty_expires_soon")
                    ->label("Warranty expiring soon")
                    ->query(fn (Builder $query) => $query
                    ->whereRaw("
                            COALESCE(
                                warranty_expires_at,
                                purchased_at + (
                                    SELECT warranty_months FROM products 
                                    WHERE products.id = inventory_items.product_id
                                ) * INTERVAL '1 month'
                            ) BETWEEN ? AND ?
                        ", [now(), now()->addDays(30)])
                    ),
                    SelectFilter::make('status')
                        ->options([
                            'in_stock' => 'In stock',
                            'delivered' => 'Delivered',
                        ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
