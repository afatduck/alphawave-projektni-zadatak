<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryItemTemperature extends Model
{
    public function item(): BelongsTo
    {
        return $this->belongsTo(DeliveryItem::class);
    }

    protected $fillable = ["delivery_item_id", "recorded_at", "temperature", "is_alert"];

    protected $casts = [
        'recorded_at' => 'datetime',
        'is_alert' => 'boolean',
    ];
}
