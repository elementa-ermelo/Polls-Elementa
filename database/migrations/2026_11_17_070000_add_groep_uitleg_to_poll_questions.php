<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('poll_questions', function (Blueprint $table) {
            // Regel onder de titel van een themakaart; telt bij de eerste vraag van de kaart.
            $table->string('groep_uitleg', 255)->nullable()->after('kort');
        });
    }

    public function down(): void
    {
        Schema::table('poll_questions', function (Blueprint $table) {
            $table->dropColumn('groep_uitleg');
        });
    }
};
