<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    protected static function booted()
    {
        static::created(function ($model) {
            $model->inventoryItem->update(["status" => "delivered"]);
        });
    }

    protected $fillable = [
        "delivery_id",
        "inventory_item_id",
    ];
}
