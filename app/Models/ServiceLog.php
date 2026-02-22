<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceLog extends Model
{
    protected $fillable = [
        "inventory_item_id",
        "performed_at",
        "action",
        "description",
    ];
}
