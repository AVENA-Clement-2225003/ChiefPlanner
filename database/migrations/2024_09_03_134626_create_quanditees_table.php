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
        Schema::create('quanditees', function (Blueprint $table) {
            $table->unsignedBigInteger('id_ingredient');
            $table->unsignedBigInteger('id_plat');
            $table->string('quantity');

            $table->primary(['id_ingredient', 'id_plat']);
            $table->foreign('id_ingredient')->references('id')->on('ingredients')->onDelete('cascade');
            $table->foreign('id_plat')->references('id')->on('plats')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quanditees');
    }
};
