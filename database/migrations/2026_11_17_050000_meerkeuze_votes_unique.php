<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('votes', function (Blueprint $table) {
            // Meerkeuze: een rij per aangevinkte optie, dus de optie hoort in de unieke sleutel.
            $table->unique(['poll_id', 'poll_question_id', 'email', 'poll_option_id'], 'votes_poll_vraag_email_optie_unique');
            $table->dropUnique(['poll_id', 'poll_question_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::table('votes', function (Blueprint $table) {
            $table->unique(['poll_id', 'poll_question_id', 'email']);
            $table->dropUnique('votes_poll_vraag_email_optie_unique');
        });
    }
};
