<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fixed list of store aisles (Rayons); each user maps their ingredients onto them (their store, their layout).
        Schema::create('aisles', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('label');
            $table->unsignedSmallInteger('position');
        });
        DB::table('aisles')->insert($this->aisles());

        Schema::create('ingredient_settings', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $table->string('aisle_id')->nullable();
            $table->foreign('aisle_id')->references('id')->on('aisles')->nullOnDelete();
            $table->primary(['user_id', 'ingredient_id']);
        });

        // A Pack: a quantity the user can buy an ingredient in, at their store, with an optional price.
        Schema::create('ingredient_packs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $table->string('unit_id');
            $table->foreign('unit_id')->references('id')->on('units');
            $table->decimal('size', 10, 4);
            $table->decimal('price', 8, 2)->nullable();
        });

        Schema::create('extras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('aisle_id')->nullable();
            $table->foreign('aisle_id')->references('id')->on('aisles')->nullOnDelete();
            $table->timestamps();
        });

        // Items of the shopping list ticked in the store.
        Schema::create('shopping_ticks', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('item_key');
            $table->primary(['user_id', 'item_key']);
        });

        // What was really paid for a shopping trip (the list itself only shows an estimate).
        Schema::create('spendings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 8, 2);
            $table->date('spent_on');
            $table->timestamps();
            $table->index(['user_id', 'spent_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spendings');
        Schema::dropIfExists('shopping_ticks');
        Schema::dropIfExists('extras');
        Schema::dropIfExists('ingredient_packs');
        Schema::dropIfExists('ingredient_settings');
        Schema::dropIfExists('aisles');
    }

    /** @return list<array<string, mixed>> */
    private function aisles(): array
    {
        $labels = [
            'produce' => 'Fruits et légumes',
            'meat-fish' => 'Viandes et poissons',
            'fresh' => 'Frais',
            'frozen' => 'Surgelés',
            'grocery' => 'Épicerie',
            'drinks' => 'Boissons',
            'household' => 'Hygiène et maison',
            'other' => 'Autres',
        ];

        $rows = [];
        foreach (array_keys($labels) as $index => $id) {
            $rows[] = ['id' => $id, 'label' => $labels[$id], 'position' => $index];
        }

        return $rows;
    }
};
