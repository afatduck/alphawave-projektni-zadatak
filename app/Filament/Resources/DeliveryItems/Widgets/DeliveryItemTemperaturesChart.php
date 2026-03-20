<?php

namespace App\Filament\Resources\DeliveryItems\Widgets;

use App\Models\DeliveryItem;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;

class DeliveryItemTemperaturesChart extends ChartWidget
{
    public ?DeliveryItem $record = null;

    protected int | string | array $columnSpan = 'full';

    public function getHeading(): string | Htmlable | null
    {
        $hours = (int) config('temperatures.delivery_item_temperature_hours');

        return "Temperature (last {$hours}h)";
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $hours = (int) config('temperatures.delivery_item_temperature_hours');
        $end = now()->utc()->subHour()->startOfHour();
        $start = $end->copy()->subHours($hours - 1);

        $labels = [];
        $data = [];

        $temperatureByHour = $this->record
            ? $this->record
                ->temperatures()
                ->whereBetween('recorded_at', [$start, $end])
                ->get()
                ->keyBy(fn ($temperature) => $temperature->recorded_at->utc()->format('Y-m-d H:00:00'))
            : collect();

        for ($i = 0; $i < $hours; $i++) {
            $hour = $start->copy()->addHours($i);
            $hourKey = $hour->format('Y-m-d H:00:00');

            $labels[] = $hour->format('H:i');
            $data[] = $temperatureByHour->get($hourKey)?->temperature;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Temperature',
                    'data' => $data,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): ?array
    {
        return [
            'spanGaps' => false,
        ];
    }
}
