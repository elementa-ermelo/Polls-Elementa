<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Inlogcode per e-mail. Standaard AAN, ook voor nieuwe gebruikers (les van werklijsten, 24 aug 2026).
            $table->boolean('two_factor_enabled')->default(true)->after('is_admin');
        });
        // Accounts met alleen een gebruikersnaam kunnen geen code ontvangen: voorlopig uit, tot er een adres is.
        DB::table('users')->where('email', 'not like', '%@%')->update(['two_factor_enabled' => false]);

        Schema::create('trusted_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('token_hash', 64)->unique();
            $table->string('ua', 255)->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trusted_devices');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('two_factor_enabled');
        });
    }
};
