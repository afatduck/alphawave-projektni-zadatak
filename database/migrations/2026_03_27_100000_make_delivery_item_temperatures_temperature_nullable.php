<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE delivery_item_temperatures ALTER COLUMN temperature DROP NOT NULL');
    }

    public function down(): void
    {
    }
};
