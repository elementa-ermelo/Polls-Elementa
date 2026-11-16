<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('polls', function (Blueprint $table) {
            // Naam en logo op de vragenlijst zelf; leeg = die van de eigenaar.
            $table->string('organisatie_naam')->nullable()->after('anonymous');
            $table->string('organisatie_logo')->nullable()->after('organisatie_naam');
        });
    }

    public function down(): void
    {
        Schema::table('polls', function (Blueprint $table) {
            $table->dropColumn(['organisatie_naam', 'organisatie_logo']);
        });
    }
};
