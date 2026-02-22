<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use App\Models\InventoryItem;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Items in stock', InventoryItem::where('status', 'in_stock')->count()),
            Stat::make('Items delivered', InventoryItem::where('status', 'delivered')->count()),
            Stat::make('Faulty items', InventoryItem::where('status', 'faulty')->count()),
            Stat::make('Expiring Soon', 
            InventoryItem::whereRaw("
                            COALESCE(
                                warranty_expires_at,
                                purchased_at + (
                                    SELECT warranty_months FROM products 
                                    WHERE products.id = inventory_items.product_id
                                ) * INTERVAL '1 month'
                            ) BETWEEN ? AND ?
                        ", [now(), now()->addDays(30)])
                    ->count()),
        ];
    }
}
