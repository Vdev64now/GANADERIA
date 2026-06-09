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
        Schema::create('cattles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained('farms')->onDelete('cascade');
            $table->string('ear_tag');
            $table->string('breed')->nullable();
            $table->string('provider')->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('live_weight', 8, 2);
            $table->decimal('purchase_price_total', 10, 2);
            $table->string('status')->default('en_pie'); // en_pie, beneficiado_parcial, beneficiado_completo, despostado_completo, vendido
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cattles');
    }
};
