# ShortHop

ShortHop is a self-hosted URL shortener built with Laravel. It provides a private dashboard for managing branded links, generating QR codes, and viewing basic visit analytics. Short links use the domain configured in `APP_URL`.

## Requirements

- PHP 8.4.1 or newer
- Composer
- Node.js 22.13 or newer, below Node.js 25, with npm
- SQLite with the PHP PDO SQLite extension for the default local setup, or another Laravel-supported database with its corresponding PHP PDO extension

## Installation

Install the PHP dependencies and create the local environment file:

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Set `APP_URL` and configure the database connection in `.env`, then prepare the database and frontend assets:

```bash
php artisan migrate --seed --seeder=DevelopmentSeeder
npm ci
npm run build
```

## Development

Start the application and frontend development processes:

```bash
composer run dev
```

To run only the Vite development server, use `npm run dev`. Build production frontend assets with `npm run build`.

### Local administrator

The explicitly invoked `DevelopmentSeeder` creates this local development account:

```text
Email: admin@example.com
Password: password
```

The development seeder refuses to run unless `APP_ENV=local`. The ordinary `DatabaseSeeder` never creates a default user, password, or demo link.

## Production setup

Before running any production Artisan commands, do not deploy `.env.example` unchanged. Complete this preflight:

- Set `APP_ENV=production` and `APP_DEBUG=false`. Debug mode can expose sensitive configuration through error pages.
- Set `APP_URL` to the canonical HTTPS origin used for public short links.
- Configure and verify the production database connection.
- Generate a unique `APP_KEY`, store it as a persistent secret, and never regenerate it for an existing installation.
- Configure a real outbound mail transport and sender address. The default `log` mailer does not deliver the password-reset message used to choose the administrator password.

For deployments with a writable `.env` file, generate the application key with `php artisan key:generate`. For managed platforms, run `php artisan key:generate --show` locally and save the output directly in the platform's secret manager without exposing it in deployment logs.

Production deployments must include a frontend asset build. Some deployment services, including Laravel Cloud, handle the application build when deploying from the repository. Otherwise, ensure the deployment pipeline or an administrator runs:

```bash
npm ci
npm run build
```

Run the build during CI or deployment when possible rather than on a live application server.

Run the migrations and seed the roles and permissions:

```bash
php artisan migrate --force
php artisan db:seed --force
```

Provision an administrator with a real operator email address:

```bash
php artisan app:provision-admin \
    --name="Administrator" \
    --email="owner@example.com"
```

The command is non-interactive and can be run through Laravel Cloud or another host's command runner. It creates a random internal password, verifies the email address, and assigns the `admin` role. It does not print a password or change an existing user's name or password.

Configure outbound mail, then use the **Forgot password** link on the login page to choose the administrator password. Running the command again is safe and confirms that the existing user has administrator access.

## Visit analytics and privacy

Every redirect creates a row in `short_url_visits` containing the short-link identifier and visit timestamp. ShortHop uses these rows for visit counts and sparklines and to support single-use links.

New links collect browser, browser version, operating system, operating system version, device type, and referrer site by default. Referrers are reduced to their HTTP or HTTPS origin before storage, so credentials, paths, query parameters, and fragments are discarded. Raw IP address collection is disabled by default. This metadata is stored per visit and should not be treated as fully anonymous.

Operators can choose the visitor fields for each link in its Filament create or edit form. Turning off **Collect visitor details** prevents optional metadata from being collected, but the basic visit row and timestamp are still recorded. Tracking choices are stored with each link in `short_urls`.

ShortHop does not automatically expire or purge visit records. Self-hosting operators are responsible for appropriate notices, database access controls, and retention or deletion policies for their jurisdictions and use cases.

## Optional developer tooling

### Laravel Boost

Laravel Boost is included as a development dependency but is not required to run the application. Contributors who use Boost can configure their preferred agent integration locally:

```bash
php artisan boost:install
```

## Tests and code quality

Run all project checks:

```bash
composer test
```

The checks can also be run separately:

```bash
composer lint:check
composer types:check
php artisan test
```

## License

ShortHop is open-source software licensed under the [Apache License 2.0](LICENSE).
