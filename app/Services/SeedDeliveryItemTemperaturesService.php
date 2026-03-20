<?php

namespace App\Services;

use App\Models\DeliveryItemTemperature;
use Illuminate\Support\Facades\Http;

class SeedDeliveryItemTemperaturesService
{
    public function createForDeliveryItem(int $deliveryItemId, float $longitude, float $latitude): void
    {
        $maxHours = (int) config('temperatures.delivery_item_temperature_hours');
        $count = random_int(0, $maxHours);

        if ($count === 0) {
            return;
        }

        $baseTemperature = $this->fetchBaseTemperature($longitude, $latitude);

        $end = now()->utc()->subHour()->startOfHour();
        $start = $end->copy()->subHours($count - 1);
        $insertedAt = now();
        $rows = [];

        for ($i = 0; $i < $count; $i++) {
            $recordedAt = $start->copy()->addHours($i);

            $rows[] = [
                'delivery_item_id' => $deliveryItemId,
                'recorded_at' => $recordedAt,
                'temperature' => round($baseTemperature + random_int(-40, 40) / 10, 1),
                'created_at' => $insertedAt,
                'updated_at' => $insertedAt,
            ];
        }

        DeliveryItemTemperature::insert($rows);
    }

    private function fetchBaseTemperature(float $longitude, float $latitude): float
    {
        $lastHour = now()->utc()->subHour()->startOfHour();

        $response = Http::get(config('temperatures.api_uri'), [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'hourly' => 'temperature_2m',
            'start_hour' => $lastHour->format('Y-m-d\\TH:00'),
            'end_hour' => $lastHour->format('Y-m-d\\TH:00'),
        ]);

        return (float) ($response->json()['hourly']['temperature_2m'][0] ?? 20.0);
    }
}
