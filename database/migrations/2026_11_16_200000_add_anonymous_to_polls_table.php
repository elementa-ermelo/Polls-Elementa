<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('polls', function (Blueprint $table) {
            // Anoniem: geen naam/e-mail, één inzending per apparaat (cookie) plus een rem per aansluiting.
            $table->boolean('anonymous')->default(false)->after('requires_confirmation');
        });
    }

    public function down(): void
    {
        Schema::table('polls', function (Blueprint $table) {
            $table->dropColumn('anonymous');
        });
    }
};
