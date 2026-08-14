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
        Schema::connection(config('short-url.connection'))->table('short_urls', function (Blueprint $table) {
            $table->index('created_at', 'short_urls_created_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(config('short-url.connection'))->table('short_urls', function (Blueprint $table) {
            $table->dropIndex('short_urls_created_at_index');
        });
    }
};
