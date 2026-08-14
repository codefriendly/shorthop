<?php

test('the root page explains the short-link domain', function () {
    config([
        'app.name' => 'Private Application Name',
        'app.url' => 'https://short.test',
    ]);

    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertSee('short.test')
        ->assertSee('short.test is a short-link domain.')
        ->assertSee('short.test/example')
        ->assertSee('Open the complete link to continue to its destination.')
        ->assertSee('example.com')
        ->assertSee('the link may be incomplete')
        ->assertSee('Manage links')
        ->assertDontSee('href="https://short.test"', false)
        ->assertDontSee('Private Application Name')
        ->assertDontSee('Root page')
        ->assertDontSee('Sign in')
        ->assertDontSee('Dashboard')
        ->assertDontSee('short.test/some-link')
        ->assertDontSee('Personal URL shortener')
        ->assertDontSee('Every client link, accounted for.')
        ->assertDontSee('Client link ledger')
        ->assertDontSee('Let\'s get started')
        ->assertDontSee('Documentation')
        ->assertDontSee('redirect path');
});

test('the root page uses modest public cache headers', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertHeader('Cache-Control', 'max-age=300, public, s-maxage=3600, stale-while-revalidate=86400')
        ->assertHeader('ETag');
});
