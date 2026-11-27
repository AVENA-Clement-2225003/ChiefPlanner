<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            // `sub` claim of the Authentik identity: the only link between an account and a person.
            $table->string('sub')->unique();
            $table->string('email')->unique();
            $table->string('name');
            // Mirrors the admin group of the instance, refreshed at every login (ADR 0002).
            $table->boolean('is_admin')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
