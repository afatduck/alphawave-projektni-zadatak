<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId("product_id");
            $table->string("serial_number")->unique();
            $table->enum("status", ["in_stock", "delivered"]);
            $table->date("purchased_at")->nullable();
            $table->date("warranty_expires_at")->nullable();
            $table->string("notes")->nullable();
            $table->timestamps();

            $table->index("product_id");
            $table->index("status");
            $table->index("warranty_expires_at");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
