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
        Schema::create('contain', function (Blueprint $table) {
            $table->unsignedBigInteger('id_plat');
            $table->unsignedBigInteger('id_playlist');
            
            $table->primary(['id_plat', 'id_playlist']);
            
            $table->foreign('id_plat')
                ->references('id_plat')
                ->on('plats')
                ->onDelete('cascade');
                
            $table->foreign('id_playlist')
                ->references('id_playlist')
                ->on('playlist')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contain');
    }
};
