<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('polls', function (Blueprint $table) {
            // Bestaande polls houden de e-mailbevestiging (zo werkten ze altijd);
            // nieuwe polls kiezen het zelf via de schakelaar in het formulier.
            $table->boolean('requires_confirmation')->default(true)->after('access_code');
        });
    }

    public function down(): void
    {
        Schema::table('polls', function (Blueprint $table) {
            $table->dropColumn('requires_confirmation');
        });
    }
};
