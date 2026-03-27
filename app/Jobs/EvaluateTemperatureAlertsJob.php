<?php

namespace App\Jobs;

use App\Filament\Resources\DeliveryItems\DeliveryItemResource;
use App\Mail\DeliveryItemTemperatureAlertMail;
use App\Mail\DeliveryItemTemperatureMissingMail;
use App\Models\DeliveryItemTemperature;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class EvaluateTemperatureAlertsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(): void
    {
        $latestRows = $this->getLatestRowsByItem();

        if ($latestRows->isEmpty()) {
            return;
        }

        $previousAlertByItem = $this->getPreviousAlertByItem($latestRows->keys()->all());
        $historyStatsByItem = $this->getHistoryStatsByItem($latestRows);

        $hours = (int) config('temperatures.delivery_item_temperature_hours');
        $requiredCount = max(0, intdiv($hours, 24) - 1);
        $range = (float) config('temperatures.alert_range');

        $alertsToEmail = [];
        $missingTemperatureToEmail = [];

        foreach ($latestRows as $itemId => $latest) {
            $wasAlert = (bool) ($previousAlertByItem->get($itemId) ?? false);
            $latestTemperature = $latest->temperature !== null ? (float) $latest->temperature : null;
            $stats = $historyStatsByItem->get($itemId);

            $historyCount = (int) ($stats->history_count ?? 0);
            $hourAverage = isset($stats->hour_average) ? (float) $stats->hour_average : null;

            $isAlert = false;

            if ($latestTemperature !== null && $hourAverage !== null && $historyCount >= $requiredCount) {
                $isAlert = $latestTemperature < ($hourAverage - $range)
                    || $latestTemperature > ($hourAverage + $range);
            }

            DeliveryItemTemperature::query()
                ->whereKey($latest->id)
                ->update([
                    'is_alert' => $isAlert,
                    'updated_at' => now(),
                ]);

            if (! $wasAlert && $latestTemperature === null) {
                $missingTemperatureToEmail[] = [
                    'delivery_item_id' => $itemId,
                    'recorded_at' => (string) $latest->recorded_at,
                    'delivery_item_url' => DeliveryItemResource::getUrl('view', ['record' => $itemId]),
                ];
            }

            if ($isAlert && ! $wasAlert) {
                $alertsToEmail[] = [
                    'delivery_item_id' => $itemId,
                    'recorded_at' => (string) $latest->recorded_at,
                    'temperature' => $latestTemperature,
                    'hour_average' => $hourAverage,
                    'range' => $range,
                    'delivery_item_url' => DeliveryItemResource::getUrl('view', ['record' => $itemId]),
                ];
            }
        }

        $this->sendAlerts($alertsToEmail);
        $this->sendMissingTemperatureAlerts($missingTemperatureToEmail);
    }

    private function getLatestRowsByItem(): Collection
    {
        return DeliveryItemTemperature::query()
            ->fromSub(function ($query): void {
                $query->from('delivery_item_temperatures')
                    ->selectRaw('id, delivery_item_id, recorded_at, temperature, ROW_NUMBER() OVER (PARTITION BY delivery_item_id ORDER BY recorded_at DESC, id DESC) as rn');
            }, 'ranked')
            ->where('rn', 1)
            ->get(['id', 'delivery_item_id', 'recorded_at', 'temperature'])
            ->keyBy('delivery_item_id');
    }

    private function getPreviousAlertByItem(array $deliveryItemIds): Collection
    {
        if ($deliveryItemIds === []) {
            return collect();
        }

        return DeliveryItemTemperature::query()
            ->fromSub(function ($query): void {
                $query->from('delivery_item_temperatures')
                    ->selectRaw('delivery_item_id, is_alert, ROW_NUMBER() OVER (PARTITION BY delivery_item_id ORDER BY recorded_at DESC, id DESC) as rn');
            }, 'ranked')
            ->where('rn', 2)
            ->whereIn('delivery_item_id', $deliveryItemIds)
            ->get(['delivery_item_id', 'is_alert'])
            ->pluck('is_alert', 'delivery_item_id')
            ->map(fn ($value): bool => (bool) $value);
    }

    private function getHistoryStatsByItem(Collection $latestRows): Collection
    {
        if ($latestRows->isEmpty()) {
            return collect();
        }

        $latestSub = DB::query()
            ->from('delivery_item_temperatures')
            ->selectRaw('delivery_item_id, recorded_at, ROW_NUMBER() OVER (PARTITION BY delivery_item_id ORDER BY recorded_at DESC, id DESC) as rn');

        return DeliveryItemTemperature::query()
            ->from('delivery_item_temperatures as t')
            ->joinSub($latestSub, 'l', function ($join): void {
                $join->on('l.delivery_item_id', '=', 't.delivery_item_id')
                    ->where('l.rn', '=', 1);
            })
            ->whereIn('t.delivery_item_id', $latestRows->keys()->all())
            ->whereColumn('t.recorded_at', '<', 'l.recorded_at')
            ->whereNotNull('t.temperature')
            ->whereRaw('EXTRACT(HOUR FROM t.recorded_at) = EXTRACT(HOUR FROM l.recorded_at)')
            ->groupBy('t.delivery_item_id')
            ->get([
                't.delivery_item_id',
                DB::raw('COUNT(*) as history_count'),
                DB::raw('AVG(t.temperature) as hour_average'),
            ])
            ->keyBy('delivery_item_id');
    }

    /**
     * @param array<int, array<string, mixed>> $alerts
     */
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

    /**
     * @param array<int, array<string, mixed>> $alerts
     */
    private function sendMissingTemperatureAlerts(array $alerts): void
    {
        $recipient = config('temperatures.alert_email');

        if (! is_string($recipient) || $recipient === '' || $alerts === []) {
            return;
        }

        foreach ($alerts as $alert) {
            Mail::to($recipient)->send(new DeliveryItemTemperatureMissingMail($alert));
        }
    }
}
