<?php

namespace App\Jobs;

use App\Models\DeliveryItem;
use App\Models\DeliveryItemTemperature;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class FetchAllTemperaturesJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $items = DeliveryItem::query()
            ->select('id', 'longitude', 'latitude')
            ->orderBy('id')
            ->get();

        if ($items->isEmpty()) {
            return;
        }

        $this->deleteOldRecords();

        foreach ($items as $item) {
            FetchTemperaturesJob::dispatch(
                $item->id,
                (float) $item->longitude,
                (float) $item->latitude,
            );
        }

        EvaluateTemperatureAlertsJob::dispatch()->delay(now()->addMinutes(10));
    }

    private function deleteOldRecords(): void
    {
        $maxHours = (int) config('temperatures.delivery_item_temperature_hours');
        $cutoff = now()->utc()->subHours($maxHours);

        DeliveryItemTemperature::query()
            ->where('recorded_at', '<', $cutoff)
            ->delete();
    }
}
