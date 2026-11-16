<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('poll_questions', function (Blueprint $table) {
            // Ingevuld = de vraag is niet verplicht en een leeg antwoord telt als deze optie (op tekst, niet op id:
            // bij het bewerken worden opties op tekst gekoppeld).
            $table->string('standaard_antwoord')->nullable()->after('met_toelichting');
        });
    }

    public function down(): void
    {
        Schema::table('poll_questions', function (Blueprint $table) {
            $table->dropColumn('standaard_antwoord');
        });
    }
};
