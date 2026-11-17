<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('poll_questions', function (Blueprint $table) {
            // Themakaart: opeenvolgende vragen met dezelfde groep staan samen op één kaart (titel = groep).
            $table->string('groep', 120)->nullable()->after('vervolg_bij');
            // Korte regel binnen de themakaart (bijv. "Gezelligheid"); leeg = de vraag zelf.
            $table->string('kort', 255)->nullable()->after('groep');
        });
    }

    public function down(): void
    {
        Schema::table('poll_questions', function (Blueprint $table) {
            $table->dropColumn(['groep', 'kort']);
        });
    }
};
