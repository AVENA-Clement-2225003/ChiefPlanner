<?php

use App\Models\Semaine;
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
        Schema::create('semaine', function (Blueprint $table) {
            $table->id();
            $table->string('day');
            $table->enum('time', array('morning', 'afternoon'));
            $table->boolean('selected')->default(0);
        });
        foreach (['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'] as $jour) {
            $newMorningDay = new Semaine();
            $newMorningDay->day = $jour;
            $newMorningDay->time = 'morning';
            $newMorningDay->save();

            $newAfternoonDay = new Semaine();
            $newAfternoonDay->day = $jour;
            $newAfternoonDay->time = 'afternoon';
            $newAfternoonDay->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('semaines');
    }
};
