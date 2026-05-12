<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('votes', function (Blueprint $table) {
            // Drop the old unique constraint that only covers poll_id + email.
            // With multi-question polls, each question creates its own vote row,
            // so the unique key must include poll_question_id.
            $table->dropUnique(['poll_id', 'email']);
            $table->unique(['poll_id', 'poll_question_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::table('votes', function (Blueprint $table) {
            $table->dropUnique(['poll_id', 'poll_question_id', 'email']);
            $table->unique(['poll_id', 'email']);
        });
    }
};
