<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('polls', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('title');
        });

        // Bestaande polls een slug geven op basis van hun titel.
        $used = [];
        foreach (DB::table('polls')->orderBy('id')->get(['id', 'title']) as $poll) {
            $base = Str::slug($poll->title) ?: 'poll';
            $slug = $base;
            $i = 2;
            while (in_array($slug, $used, true) || ctype_digit($slug)) {
                $slug = $base.'-'.$i++;
            }
            $used[] = $slug;
            DB::table('polls')->where('id', $poll->id)->update(['slug' => $slug]);
        }

        Schema::table('polls', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('polls', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
