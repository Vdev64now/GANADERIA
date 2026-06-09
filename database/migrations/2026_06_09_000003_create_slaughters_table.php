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
        Schema::create('slaughters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cattle_id')->constrained('cattles')->onDelete('cascade');
            $table->foreignId('slaughterhouse_id')->constrained('slaughterhouses')->onDelete('cascade');
            $table->date('slaughter_date');
            $table->decimal('left_carcass_weight', 8, 2);
            $table->decimal('right_carcass_weight', 8, 2);
            $table->decimal('slaughter_cost', 10, 2)->default(0.00);
            $table->string('left_carcass_status')->default('disponible'); // disponible, despostado, vendido
            $table->string('right_carcass_status')->default('disponible'); // disponible, despostado, vendido
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slaughters');
    }
};
