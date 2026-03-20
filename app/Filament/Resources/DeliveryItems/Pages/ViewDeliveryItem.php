<?php

namespace App\Filament\Resources\DeliveryItems\Pages;

use App\Filament\Resources\DeliveryItems\DeliveryItemResource;
use App\Filament\Resources\DeliveryItems\Widgets\DeliveryItemTemperaturesChart;
use Filament\Resources\Pages\ViewRecord;

class ViewDeliveryItem extends ViewRecord
{
    protected static string $resource = DeliveryItemResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            DeliveryItemTemperaturesChart::class,
        ];
    }
}
