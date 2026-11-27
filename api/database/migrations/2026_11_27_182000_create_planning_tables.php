<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Up to two MealFlows a user applies to the week (A and B); each Slot then picks A, B or neither.
        Schema::create('preferences', function (Blueprint $table) {
            $table->foreignId('user_id')->primary()->constrained()->cascadeOnDelete();
            $table->foreignId('meal_flow_a_id')->nullable()->constrained('meal_flows')->nullOnDelete();
            $table->foreignId('meal_flow_b_id')->nullable()->constrained('meal_flows')->nullOnDelete();
        });

        // Which Slots of the week get a meal at each Proposal, for how many people, from which MealFlow.
        Schema::create('slot_preferences', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day');
            $table->enum('slot', ['lunch', 'dinner']);
            $table->boolean('enabled')->default(true);
            $table->unsignedTinyInteger('servings')->default(1);
            $table->enum('flow', ['a', 'b'])->nullable();
            $table->primary(['user_id', 'day', 'slot']);
        });

        // The current Week: one meal per planned Slot. A null dish is a meal to replace (its dish was removed).
        Schema::create('meals', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day');
            $table->enum('slot', ['lunch', 'dinner']);
            $table->foreignId('dish_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('servings')->default(1);
            $table->primary(['user_id', 'day', 'slot']);
        });

        // The Week as it was before the last Proposal, to undo it (ADR: older history lives in the browser session).
        Schema::create('previous_weeks', function (Blueprint $table) {
            $table->foreignId('user_id')->primary()->constrained()->cascadeOnDelete();
            $table->json('meals');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('previous_weeks');
        Schema::dropIfExists('meals');
        Schema::dropIfExists('slot_preferences');
        Schema::dropIfExists('preferences');
    }
};
