<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function serviceLogs(): HasMany
    {
        return $this->hasMany(ServiceLog::class);
    }

    protected function warrantyExpiresAtDynamic(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->warranty_expires_at != null) return $this->warranty_expires_at;
                if ($this->purchased_at != null) return $this->purchased_at->addMonths($this->product->warranty_months);
                return null;

            }
        );
    }

    protected $fillable = [
        "product_id",
        "serial_number",
        "status",
        "purchased_at",
        "warranty_expires_at",
        "notes",
    ];

    protected $casts = [
        'purchased_at' => 'datetime',
        'warranty_expires_at' => 'datetime',
    ];
}
