@php
    $appUrl = rtrim(config('app.url'), '/');
    $appHost = parse_url($appUrl, PHP_URL_HOST) ?: $appUrl;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', ['title' => $appHost, 'titleSuffix' => false])
    </head>
    <body class="min-h-screen bg-slate-100 text-slate-950 antialiased">
        <main class="flex min-h-screen items-center px-5 py-10 sm:px-8">
            <section class="mx-auto w-full max-w-2xl rounded-2xl border border-slate-950/15 bg-white p-6 sm:p-8">
                <div class="flex flex-col gap-8">
                    <header class="flex items-center justify-between gap-4">
                        <a href="{{ route('home') }}" class="font-mono text-sm font-semibold text-slate-950 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-slate-950">
                            {{ $appHost }}
                        </a>

                        <a href="/app" class="inline-flex min-h-11 items-center rounded-full border border-slate-950/20 px-4 py-2 text-sm font-semibold text-slate-950 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-slate-950">
                            Manage links
                        </a>
                    </header>

                    <div class="space-y-5">
                        <h1 class="max-w-xl text-3xl leading-tight font-black tracking-[-0.03em] text-balance sm:text-5xl">
                            {{ $appHost }} is a short-link domain.
                        </h1>

                        <p class="max-w-xl text-base leading-7 text-slate-700 sm:text-lg">
                            Links here usually include a short code after the slash, like <span class="font-mono font-semibold text-slate-950">{{ $appHost }}/example</span>. Open the complete link to continue to its destination.
                        </p>
                    </div>

                    <div class="rounded-xl border border-slate-950/10 bg-white p-4 sm:p-5">
                        <div class="flex flex-col gap-3 font-mono text-sm text-slate-950 sm:flex-row sm:items-center">
                            <span>{{ $appHost }}/example</span>
                            <span class="hidden text-slate-500 sm:inline" aria-hidden="true">-&gt;</span>
                            <span class="text-slate-500">example.com</span>
                        </div>
                    </div>

                    <div class="space-y-3 border-t border-slate-950/10 pt-5 text-sm leading-6 text-slate-700">
                        <p>
                            If you landed on {{ $appHost }} by itself, the link may be incomplete. Check the address you were given.
                        </p>
                        <p>
                            If you manage this domain, use Manage links to open the dashboard.
                        </p>
                    </div>
                </div>
            </section>
        </main>
    </body>
</html>
