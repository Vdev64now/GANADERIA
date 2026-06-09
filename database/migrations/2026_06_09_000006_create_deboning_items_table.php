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
        Schema::create('deboning_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deboning_id')->constrained('debonings')->onDelete('cascade');
            $table->foreignId('cut_type_id')->constrained('cut_types')->onDelete('cascade');
            $table->decimal('weight', 8, 2);
            $table->decimal('current_weight', 8, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deboning_items');
    }
};
