<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Random\Randomizer;
use App\Services\SeedDeliveryItemTemperaturesService;

class DeliveryItem extends Model
{
    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function temperatures(): HasMany
    {
        return $this->hasMany(DeliveryItemTemperature::class)->orderBy("id");
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $randomizer = new Randomizer();
            $model->longitude = $randomizer->getFloat(-180, 180);
            $model->latitude = $randomizer->getFloat(-90, 90);
        });
            
        static::created(function ($model) {
            $model->inventoryItem->update(["status" => "delivered"]);

            if (! config('temperatures.use_seeder')) {
                return;
            }

            app(SeedDeliveryItemTemperaturesService::class)->createForDeliveryItem(
                $model->id,
                $model->longitude,
                $model->latitude,
            );
        });
    }

    protected $fillable = [
        "delivery_id",
        "inventory_item_id",
    ];

}
