<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poll_id')->constrained()->cascadeOnDelete();
            $table->foreignId('poll_option_id')->nullable()->constrained()->nullOnDelete();
            $table->string('respondent_name');
            $table->string('email');
            $table->unsignedTinyInteger('age');
            $table->unsignedTinyInteger('numeric_value')->nullable();
            $table->string('confirmation_token')->nullable()->unique();
            $table->timestamp('confirmation_sent_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->unique(['poll_id', 'email']);
            $table->index(['poll_id', 'confirmed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
