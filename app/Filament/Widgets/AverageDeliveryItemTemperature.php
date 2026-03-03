<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class AverageDeliveryItemTemperature extends StatsOverviewWidget
{
    protected function getStats(): array
    {

        $average = DB::table("delivery_item_temperatures")
            ->whereIn("id", function ($sub) {
                $sub->selectRaw("MAX(id)")
                    ->from("delivery_item_temperatures")
                    ->groupBy("delivery_item_id");
            })
            -> avg("temperature");

        return [
            Stat::make('Average Delivery Item Temperature', $average . '°C'),
        ];
    }
}
