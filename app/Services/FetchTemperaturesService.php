<?php
namespace App\Services;

use App\Models\DeliveryItem;
use App\Models\DeliveryItemTemperature;
use Illuminate\Support\Facades\Http;

const OPEN_METEO_URI = "https://api.open-meteo.com/v1/forecast";
const MAX_HOURS = 24;

class FetchTemperaturesService
{
    public function updateDeliveryItemTemperatures(
        int $item_id,
        float $longitude,
        float $latitude
    ): void
    {
        $now = now()->utc()->subHour();
        $yesterday = $now->copy()->subHours(23);

        $response = Http::get(OPEN_METEO_URI, [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'hourly' => 'temperature_2m',
            'start_hour' => $yesterday->format('Y-m-d\TH:00'),
            'end_hour' => $now->format('Y-m-d\TH:00'),
        ]);
        $json_response = $response->json();
        $hourly = $json_response["hourly"];
        $times = $hourly["time"];
        $temperatures = $hourly["temperature_2m"];

        for($i = 0; $i < count($times); $i++) {
            $time = $times[$i];
            $temperature = $temperatures[$i];
            DeliveryItemTemperature::firstOrCreate(
                ["delivery_item_id" => $item_id, "recorded_at" => $time],
                ["temperature" => $temperature]
            );
        }
    }

    public function updateAllTemperatures(): void {
        $items = DeliveryItem::select("id", "longitude", "latitude")->get();
        foreach($items as $item) {
            $this->updateDeliveryItemTemperatures(
                $item->id,
                $item->longitude,
                $item->latitude
            );
        }
        $this->deleteOldRecords();
    }

    private function deleteOldRecords(): void
    {
        $groupedCounts = DeliveryItemTemperature::selectRaw("delivery_item_id, COUNT(*) as count")
            ->groupBy('delivery_item_id')
            ->havingRaw('COUNT(*) > ?', [MAX_HOURS])
            ->get();
        
        foreach ($groupedCounts as $gc) {
            DeliveryItemTemperature::where("delivery_item_id", $gc->delivery_item_id)
                ->orderBy("id", "desc")
                ->skip(MAX_HOURS)
                ->take($gc->count - MAX_HOURS)
                ->delete();
        }
    }
}
?>