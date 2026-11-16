<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('votes', function (Blueprint $table) {
            // Herkomst: alleen de site waar de link vandaan kwam (bijv. facebook.com), of 'direct'.
            $table->string('herkomst', 100)->nullable()->after('open_answer');
        });
    }

    public function down(): void
    {
        Schema::table('votes', function (Blueprint $table) {
            $table->dropColumn('herkomst');
        });
    }
};
