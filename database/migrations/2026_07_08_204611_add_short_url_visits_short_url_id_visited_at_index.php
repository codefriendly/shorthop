<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection(config('short-url.connection'))->table('short_url_visits', function (Blueprint $table) {
            $table->index(
                ['short_url_id', 'visited_at'],
                'short_url_visits_short_url_id_visited_at_index',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(config('short-url.connection'))->table('short_url_visits', function (Blueprint $table) {
            $table->dropIndex('short_url_visits_short_url_id_visited_at_index');
        });
    }
};
