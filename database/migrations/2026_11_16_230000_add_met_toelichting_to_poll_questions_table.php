<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('poll_questions', function (Blueprint $table) {
            // Aan: onder de antwoorden staat een ingeklapt, niet verplicht veld "Wilt u dit toelichten?".
            $table->boolean('met_toelichting')->default(false)->after('help_text');
        });
    }

    public function down(): void
    {
        Schema::table('poll_questions', function (Blueprint $table) {
            $table->dropColumn('met_toelichting');
        });
    }
};
