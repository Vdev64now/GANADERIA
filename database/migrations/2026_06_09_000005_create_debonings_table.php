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
        Schema::create('debonings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('slaughter_id')->constrained('slaughters')->onDelete('cascade');
            $table->string('side'); // izquierdo, derecho, ambos
            $table->date('deboning_date');
            $table->decimal('input_weight', 8, 2);
            $table->decimal('total_cuts_weight', 8, 2);
            $table->decimal('waste_weight', 8, 2);
            $table->decimal('yield_percentage', 5, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debonings');
    }
};
