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
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');
            $table->string('type'); // media_canal_izquierda, media_canal_derecha, corte
            $table->foreignId('slaughter_id')->nullable()->constrained('slaughters')->onDelete('set null');
            $table->foreignId('deboning_item_id')->nullable()->constrained('deboning_items')->onDelete('set null');
            $table->decimal('weight', 8, 2);
            $table->decimal('price_per_kg', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
