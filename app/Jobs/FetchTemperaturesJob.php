<?php

namespace App\Jobs;

use App\Models\DeliveryItemTemperature;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Throwable;

class FetchTemperaturesJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    /**
     * @var array<int>
     */
    public array $backoff = [30, 60, 120, 300];

    public function __construct(
        public int $deliveryItemId,
        public float $longitude,
        public float $latitude,
    ) {
    }

    public function handle(): void
    {
        $recordedAt = now()->utc()->subHour()->startOfHour();

        $response = Http::get(config('temperatures.api_uri'), [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'hourly' => 'temperature_2m',
            'start_hour' => $recordedAt->format('Y-m-d\\TH:00'),
            'end_hour' => $recordedAt->format('Y-m-d\\TH:00'),
        ])->throw();

        $jsonResponse = $response->json();
        $temperature = $jsonResponse['hourly']['temperature_2m'][0] ?? null;

        if ($temperature === null) {
            throw new \RuntimeException('Temperature data is missing from weather response.');
        }

        $this->upsertTemperature($recordedAt, (float) $temperature);
    }

    public function failed(Throwable $exception): void
    {
        $recordedAt = now()->utc()->subHour()->startOfHour();

        $this->upsertTemperature($recordedAt, null);
    }

    private function upsertTemperature(Carbon $recordedAt, ?float $temperature): void
    {
        DeliveryItemTemperature::query()->upsert(
            [[
                'delivery_item_id' => $this->deliveryItemId,
                'recorded_at' => $recordedAt,
                'temperature' => $temperature,
                'is_alert' => $temperature !== null,
                'created_at' => now(),
                'updated_at' => now(),
            ]],
            ['delivery_item_id', 'recorded_at'],
            ['temperature', 'is_alert', 'updated_at']
        );
    }
}
