<?php

namespace App\Jobs;

use App\Models\DeliveryItem;
use App\Models\DeliveryItemTemperature;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class FetchTemperaturesJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $this->updateAllTemperatures();
        $this->deleteOldRecords();
    }

    private function updateAllTemperatures(): void
    {
        $items = DeliveryItem::select('id', 'longitude', 'latitude')
            ->orderBy('id')
            ->get();

        if ($items->isEmpty()) {
            return;
        }

        $now = now()->utc()->subHour();

        $response = Http::get(config('temperatures.api_uri'), [
            'latitude' => $items->pluck('latitude')->implode(','),
            'longitude' => $items->pluck('longitude')->implode(','),
            'hourly' => 'temperature_2m',
            'start_hour' => $now->format('Y-m-d\\TH:00'),
            'end_hour' => $now->format('Y-m-d\\TH:00'),
        ]);

        $jsonResponse = $response->json();

        if (array_key_exists('hourly', $jsonResponse)) {
            $jsonResponse = [$jsonResponse];
        }

        $rows = [];
        $insertedAt = now();

        foreach ($items->values() as $index => $item) {
            $rows[] = [
                'delivery_item_id' => $item->id,
                'recorded_at' => $jsonResponse[$index]['hourly']['time'][0],
                'temperature' => $jsonResponse[$index]['hourly']['temperature_2m'][0],
                'created_at' => $insertedAt,
                'updated_at' => $insertedAt,
            ];
        }

        if ($rows !== []) {
            DeliveryItemTemperature::upsert(
                $rows,
                ['delivery_item_id', 'recorded_at'],
                ['temperature', 'updated_at']
            );
        }
    }

    private function deleteOldRecords(): void
    {
        $maxHours = config('temperatures.delivery_item_temperature_hours');
        $cutoff = now()->utc()->subHours($maxHours);

        DeliveryItemTemperature::where('recorded_at', '<', $cutoff)->delete();
    }
}
