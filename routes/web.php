<?php

use App\Http\Controllers\ShortLinkQrCodeController;
use App\Http\Middleware\NormalizeShortLinkReferrer;
use AshAllenDesign\ShortURL\Controllers\ShortURLController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')
    ->middleware('cache.headers:public;max_age=300;s_maxage=3600;stale_while_revalidate=86400;etag')
    ->name('home');

require __DIR__.'/settings.php';

Route::middleware(['auth', 'can:access app'])
    ->prefix('app/links/{shortURL}/qr')
    ->whereNumber('shortURL')
    ->controller(ShortLinkQrCodeController::class)
    ->group(function (): void {
        Route::get('{format}', 'show')
            ->whereIn('format', ['png', 'svg'])
            ->name('short-links.qr.show');

        Route::get('{format}/download', 'download')
            ->whereIn('format', ['png', 'svg'])
            ->name('short-links.qr.download');
    });

Route::get('/{shortURLKey}', ShortURLController::class)
    ->middleware([NormalizeShortLinkReferrer::class, 'throttle:short-links'])
    ->where('shortURLKey', '[A-Za-z0-9\-_]+')
    ->name('short-links.redirect');
