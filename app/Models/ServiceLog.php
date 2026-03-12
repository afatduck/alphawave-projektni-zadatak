<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceLog extends Model
{

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function productName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->inventoryItem->product->name
        );
    }

    protected $fillable = [
        "inventory_item_id",
        "performed_at",
        "action",
        "description",
    ];
}
