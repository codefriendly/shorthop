<?php

use Illuminate\Support\Facades\DB;

test('short url tables have dashboard query indexes', function () {
    $shortUrlIndexes = collect(DB::select('PRAGMA index_list(short_urls)'))
        ->pluck('name');

    $shortUrlVisitIndexes = collect(DB::select('PRAGMA index_list(short_url_visits)'))
        ->pluck('name');

    expect($shortUrlIndexes)->toContain('short_urls_created_at_index')
        ->and($shortUrlVisitIndexes)->toContain('short_url_visits_short_url_id_visited_at_index');
});
