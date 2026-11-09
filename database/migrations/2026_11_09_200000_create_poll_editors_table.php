<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Mede-beheerders: gebruikers die een poll van iemand anders mogen
        // beheren (vragen, volgorde, resultaten, live zetten). Alleen de
        // eigenaar en admins kunnen de poll zelf verwijderen.
        Schema::create('poll_editors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poll_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['poll_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('poll_editors');
    }
};
