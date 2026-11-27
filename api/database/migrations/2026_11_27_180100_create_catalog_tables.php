<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            // Lower-case, accent-free, single-spaced name: two ingredients with the same key are the same ingredient.
            $table->string('name_key');
            $table->boolean('is_shared')->default(true);
            $table->timestamps();

            $table->unique(['owner_id', 'name_key']);
            $table->index('name_key');
        });

        Schema::create('dishes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedSmallInteger('prep_time_minutes')->default(0);
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('easy');
            // Moments where the dish suits: a JSON list of `lunch` and/or `dinner`.
            $table->json('slots');
            $table->boolean('is_shared')->default(false);
            $table->timestamps();
        });

        Schema::create('dish_tag', function (Blueprint $table) {
            $table->foreignId('dish_id')->constrained()->cascadeOnDelete();
            $table->string('tag_id');
            $table->foreign('tag_id')->references('id')->on('tags')->cascadeOnDelete();
            $table->primary(['dish_id', 'tag_id']);
        });

        Schema::create('dish_ingredients', function (Blueprint $table) {
            $table->foreignId('dish_id')->constrained()->cascadeOnDelete();
            // Restrict: an ingredient used by a dish cannot disappear under it.
            $table->foreignId('ingredient_id')->constrained()->restrictOnDelete();
            // Always for ONE person (ADR 0005).
            $table->decimal('quantity_per_person', 10, 4);
            $table->string('unit_id');
            $table->foreign('unit_id')->references('id')->on('units');
            $table->primary(['dish_id', 'ingredient_id']);
        });

        Schema::create('dish_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dish_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('position');
            $table->text('instruction');
        });

        // A user who adopted a shared dish of the community. Removed with the dish (RGPD, no copy).
        Schema::create('dish_subscriptions', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dish_id')->constrained()->cascadeOnDelete();
            $table->primary(['user_id', 'dish_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dish_subscriptions');
        Schema::dropIfExists('dish_steps');
        Schema::dropIfExists('dish_ingredients');
        Schema::dropIfExists('dish_tag');
        Schema::dropIfExists('dishes');
        Schema::dropIfExists('ingredients');
    }
};
