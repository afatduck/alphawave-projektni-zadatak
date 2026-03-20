<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_item_temperatures', function (Blueprint $table) {
            $table->boolean('is_alert')->default(false)->after('temperature');
        });
    }

    public function down(): void
    {
    }
};
