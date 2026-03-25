<?php

namespace App\Jobs;

use App\Filament\Resources\DeliveryItems\DeliveryItemResource;
use App\Mail\DeliveryItemTemperatureAlertMail;
use App\Models\DeliveryItem;
use App\Models\DeliveryItemTemperature;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class FetchTemperaturesJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 10;
    public int $backoff = 30;

    public function handle(): void
    {
        try {
            $this->updateAllTemperatures();
        } catch (\Exception $e) {
            $this->release($this->backoff);
        }
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

        $maxHours = (int) config('temperatures.delivery_item_temperature_hours');
        $range = (float) config('temperatures.alert_range');
        $shouldEvaluateAlerts = $maxHours >= 24;
        $requiredHistoryCount = $maxHours - $maxHours % 24;
        $targetRecordedAt = $now->copy()->startOfHour();
        $targetHour = (int) $targetRecordedAt->format('H');

        $historyStatsByItemId = collect();

        if ($shouldEvaluateAlerts) {
            $historyStatsByItemId = DeliveryItemTemperature::query()
                ->selectRaw(
                    'delivery_item_id, COUNT(*) as historical_count, AVG(CASE WHEN EXTRACT(HOUR FROM recorded_at) = ? THEN temperature END) as hour_average',
                    [$targetHour]
                )
                ->whereIn('delivery_item_id', $items->pluck('id'))
                ->where('recorded_at', '<', $targetRecordedAt)
                ->groupBy('delivery_item_id')
                ->get()
                ->keyBy('delivery_item_id');
        }

        $rows = [];
        $alerts = [];
        $insertedAt = now();

        foreach ($items->values() as $index => $item) {
            $recordedAt = Carbon::parse($jsonResponse[$index]['hourly']['time'][0], 'UTC')->startOfHour();
            $temperature = (float) $jsonResponse[$index]['hourly']['temperature_2m'][0];
            $isAlert = false;

            if ($shouldEvaluateAlerts) {
                $stats = $historyStatsByItemId->get($item->id);
                $historicalCount = (int) ($stats->historical_count ?? 0);
                $hourAverage = isset($stats->hour_average) ? (float) $stats->hour_average : null;

                if ($historicalCount >= $requiredHistoryCount && $hourAverage !== null) {
                    $isAlert = $temperature < ($hourAverage - $range)
                        || $temperature > ($hourAverage + $range);

                    if ($isAlert) {
                        $alerts[] = [
                            'delivery_item_id' => $item->id,
                            'recorded_at' => $recordedAt->toDateTimeString(),
                            'temperature' => $temperature,
                            'hour_average' => $hourAverage,
                            'range' => $range,
                            'delivery_item_url' => DeliveryItemResource::getUrl('view', ['record' => $item->id]),
                        ];
                    }
                }
            }

            $rows[] = [
                'delivery_item_id' => $item->id,
                'recorded_at' => $recordedAt,
                'temperature' => $temperature,
                'is_alert' => $isAlert,
                'created_at' => $insertedAt,
                'updated_at' => $insertedAt,
            ];
        }

        if ($rows !== []) {
            DeliveryItemTemperature::upsert(
                $rows,
                ['delivery_item_id', 'recorded_at'],
                ['temperature', 'is_alert', 'updated_at']
            );

            $this->sendAlerts($alerts);
        }
    }

    private function sendAlerts(array $alerts): void
    {
        $recipient = config('temperatures.alert_email');

        if (! is_string($recipient) || $recipient === '' || $alerts === []) {
            return;
        }

        foreach ($alerts as $alert) {
            Mail::to($recipient)->send(new DeliveryItemTemperatureAlertMail($alert));
        }
    }

    private function deleteOldRecords(): void
    {
        $maxHours = config('temperatures.delivery_item_temperature_hours');
        $cutoff = now()->utc()->subHours($maxHours + 1);

        DeliveryItemTemperature::where('recorded_at', '<', $cutoff)->delete();
    }
}
