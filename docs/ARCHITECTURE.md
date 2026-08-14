# Architecture

ShortHop is a Laravel-based short-link tool for the public domain configured by `APP_URL`.

## Hosting

ShortHop is hosting-platform agnostic and can run anywhere that supports its Laravel and database requirements. Deployment-specific domains, infrastructure, and branch mappings belong to each operator's environment rather than the application repository.

For every deployed environment:

- set `APP_URL` to its canonical HTTPS origin;
- configure a persistent Laravel-supported database;
- configure mail before provisioning an administrator through the password-reset flow;
- run migrations and seed roles and permissions as documented in the README;
- map deployment branches and promotion workflows according to the chosen hosting provider.

Laravel Cloud is one compatible hosting option, but it is not an application requirement.

## Route Shape

- `/` renders the public root view with modest public cache headers. The common public path is `/{urlKey}`.
- `/app` is the Filament application panel and primary Links dashboard. It should list short links, newest first.
- `/login` is the canonical Fortify authentication screen; Filament's `/app/login` route redirects there.
- `/app/links/{shortURL}/qr/{format}` serves authenticated QR assets for link management. These routes must remain before the public short-link catch-all and require app access.
- `/{urlKey}` is the public short-link redirect route and must remain thin and registered after app/auth/system routes.

## Short Links

The core short-link engine is `ashallendesign/short-url`.

Use the package for:

- URL key generation
- destination URL storage
- redirects
- visit tracking
- single-use links
- activation/deactivation windows

Application-specific behavior should live in ShortHop code, not package edits. Every redirect retains a visit timestamp for counts and single-use links. Optional browser, operating-system, device, referrer-origin, and IP fields are selected per link in Filament; IP collection defaults to disabled. `NormalizeShortLinkReferrer` reduces HTTP and HTTPS referrers to their origin before the package can persist them and discards unsupported or malformed values.

QR codes are generated from each short link's public URL as SVG or 1024px PNG assets. Generation is requested asynchronously by the Filament View modal through authenticated `/app` routes, cached server-side forever per format/background variant, and explicitly forgotten when a link is saved from the edit action. The default QR background is white; transparent assets are requested with `background=transparent` and remain an export/display variant rather than persisted link state.

When package-facing short-link workflows grow beyond a small inline transformation, extract them into single-purpose application Actions rather than broad service classes. Keep Filament widgets/resources thin and let Actions preserve package invariants such as URL normalization.

## Redirect Path

The redirect path should do as little as possible:

1. Match a short URL key.
2. Normalize any referrer to a safe origin.
3. Resolve the destination.
4. Let the package record the visit timestamp and any enabled visitor fields.
5. Redirect.

Avoid dashboard, auth, or UI logic in the redirect path.

Short-link redirects use the `short-links` throttle. The limit is intentionally generous enough for short shared-IP QR-code bursts while still capping sustained abuse.

## App UI

Filament is the primary UI for managing links and viewing analytics. Fortify owns the authentication flow so password, passkey, two-factor, and disabled-user checks share one login surface; Filament retains `/app/login` only as a redirect to `/login`.

Use standard Filament resources/pages/actions for CRUD.

User management lives in Filament as a first-party resource rather than a role/permission plugin. Roles and permissions are seeded as code-owned defaults, while the user resource assigns existing roles and disables/enables accounts without exposing destructive deletion.

Disabled users have `users.disabled_at` set, cannot authenticate through Fortify, cannot access the Filament panel, and have database-backed sessions invalidated when disabled.

Self-management remains in the existing `/settings/*` Livewire pages and is linked from the Filament user menu.

## Reserved Keys

Root-level short links can conflict with app routes. Reserved keys should include at least:

- `app`
- `login`
- `logout`
- `register`
- `forgot-password`
- `reset-password`
- `email`
- `api`
- `livewire`
- `storage`
- `build`
- `assets`

## Current Package Boundaries

- Laravel/Fortify: authentication.
- Filament: authenticated app UI at `/app`.
- Flux: existing Blade/Livewire UI components where used outside Filament.
- Short URL package: short-link persistence, redirects, and tracking.
- `mallardduck/blade-lucide-icons`: Lucide icon support for Filament/Blade icon names.
