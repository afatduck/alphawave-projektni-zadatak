<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_item_temperatures', function (Blueprint $table) {
            $table->unique(
                ['delivery_item_id', 'recorded_at'],
                'delivery_item_temperatures_item_recorded_unique'
            );
        });
    }

    public function down(): void
    {
    }
};
