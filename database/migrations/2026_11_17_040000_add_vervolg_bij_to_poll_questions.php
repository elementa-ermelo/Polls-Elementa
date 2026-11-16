<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('poll_questions', function (Blueprint $table) {
            // Vervolgvraag: de volgende vraag verschijnt ALLEEN bij deze optie (op tekst).
            $table->string('vervolg_bij')->nullable()->after('overslaan_bij');
        });
    }

    public function down(): void
    {
        Schema::table('poll_questions', function (Blueprint $table) {
            $table->dropColumn('vervolg_bij');
        });
    }
};
