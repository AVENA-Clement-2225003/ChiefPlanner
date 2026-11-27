<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fixed lists: Units and Tags. They are data the code relies on, so they ship with the schema
 * (Watchtower only runs migrations, never seeders; ADR 0003). Changing a list means a new migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('label');
            $table->enum('family', ['mass', 'volume', 'count']);
            // How many base units (g, ml, one item) make one of this unit; lets a family be added up.
            $table->decimal('factor', 10, 4);
            $table->unsignedSmallInteger('position');
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('label');
            $table->enum('kind', ['cuisine', 'diet', 'constraint']);
            $table->unsignedSmallInteger('position');
        });

        DB::table('units')->insert($this->units());
        DB::table('tags')->insert($this->tags());
    }

    public function down(): void
    {
        Schema::dropIfExists('tags');
        Schema::dropIfExists('units');
    }

    /** @return list<array<string, mixed>> */
    private function units(): array
    {
        $rows = [
            ['unit', 'unité', 'count', 1],
            ['g', 'g', 'mass', 1],
            ['kg', 'kg', 'mass', 1000],
            ['ml', 'ml', 'volume', 1],
            ['cl', 'cl', 'volume', 10],
            ['l', 'L', 'volume', 1000],
            ['tbsp', 'c. à soupe', 'volume', 15],
            ['tsp', 'c. à café', 'volume', 5],
            ['portion', 'portion(s)', 'count', 1],
            ['clove', 'gousse(s)', 'count', 1],
            ['slice', 'tranche(s)', 'count', 1],
            ['pinch', 'pincée(s)', 'count', 1],
        ];

        return array_map(fn (array $row, int $index) => [
            'id' => $row[0], 'label' => $row[1], 'family' => $row[2], 'factor' => $row[3], 'position' => $index,
        ], $rows, array_keys($rows));
    }

    /** @return list<array<string, mixed>> */
    private function tags(): array
    {
        $rows = [
            ['asian', 'Asiatique', 'cuisine'],
            ['italian', 'Italien', 'cuisine'],
            ['french', 'Français', 'cuisine'],
            ['mexican', 'Mexicain', 'cuisine'],
            ['indian', 'Indien', 'cuisine'],
            ['mediterranean', 'Méditerranéen', 'cuisine'],
            ['meat', 'Viande', 'diet'],
            ['poultry', 'Volaille', 'diet'],
            ['fish', 'Poisson', 'diet'],
            ['vegetarian', 'Végétarien', 'diet'],
            ['vegan', 'Végan', 'diet'],
            ['quick', 'Rapide', 'constraint'],
            ['cheap', 'Pas cher', 'constraint'],
            ['oven', 'Four', 'constraint'],
            ['soup', 'Soupe', 'constraint'],
        ];

        return array_map(fn (array $row, int $index) => [
            'id' => $row[0], 'label' => $row[1], 'kind' => $row[2], 'position' => $index,
        ], $rows, array_keys($rows));
    }
};
