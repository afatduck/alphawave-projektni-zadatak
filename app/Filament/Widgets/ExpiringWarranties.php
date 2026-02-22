<?php

namespace App\Filament\Widgets;

use App\Models\InventoryItem;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class ExpiringWarranties extends TableWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => InventoryItem::query()->whereRaw("
            COALESCE(
                warranty_expires_at,
                purchased_at + (
                    SELECT warranty_months FROM products 
                    WHERE products.id = inventory_items.product_id
                ) * INTERVAL '1 month'
            ) BETWEEN ? AND ?
        ", [now(), now()->addDays(30)]))
            ->columns([
                TextColumn::make('product.name'),
                TextColumn::make('serial_number'),
                TextColumn::make('warranty_expires_at_dynamic')
                    ->label('Expires at')
                    ->date()
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
