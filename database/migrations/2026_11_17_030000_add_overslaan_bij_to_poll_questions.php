<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('poll_questions', function (Blueprint $table) {
            // Slimme sprong over één vraag: bij deze optie (op tekst) valt de volgende vraag weg.
            $table->string('overslaan_bij')->nullable()->after('stop_bij');
        });
    }

    public function down(): void
    {
        Schema::table('poll_questions', function (Blueprint $table) {
            $table->dropColumn('overslaan_bij');
        });
    }
};
