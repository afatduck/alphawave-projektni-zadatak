<?php

namespace App\Filament\Widgets;

use App\Models\DeliveryItemTemperature;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AverageDeliveryItemTemperature extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $average = DeliveryItemTemperature::query()
            ->whereIn('id', function ($q) {
                $q->selectRaw('MAX(id)')
                    ->from('delivery_item_temperatures')
                    ->groupBy('delivery_item_id');
            })
            ->avg('temperature');

        $displayAverage = $average !== null
            ? number_format((float) $average, 2) . '°C'
            : 'N/A';

        return [
            Stat::make('Average Delivery Item Temperature', $displayAverage),
        ];
    }
}
