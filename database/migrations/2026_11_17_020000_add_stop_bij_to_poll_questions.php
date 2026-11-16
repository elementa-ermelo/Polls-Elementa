<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('poll_questions', function (Blueprint $table) {
            // Slimme sprong: kiest de invuller deze optie (op tekst), dan zijn de vragen erna niet nodig.
            $table->string('stop_bij')->nullable()->after('standaard_antwoord');
        });
    }

    public function down(): void
    {
        Schema::table('poll_questions', function (Blueprint $table) {
            $table->dropColumn('stop_bij');
        });
    }
};
