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
            Stat::make('Total Clients', Client::count()),
            Stat::make('Active Inventory', InventoryItem::where('status', 'in_stock')->count()),
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
