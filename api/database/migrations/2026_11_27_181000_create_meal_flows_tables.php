<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meal_flows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 80);
            $table->timestamps();
        });

        Schema::create('meal_flow_dish', function (Blueprint $table) {
            $table->foreignId('meal_flow_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dish_id')->constrained()->cascadeOnDelete();
            $table->primary(['meal_flow_id', 'dish_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meal_flow_dish');
        Schema::dropIfExists('meal_flows');
    }
};
