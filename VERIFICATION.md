# Build verification — 22 October 2026

## Implemented and tested

Runtime: PHP 8.3.6, Laravel 12.70.1, Composer-resolved locked dependencies, MariaDB 10.11.14, PHPUnit 11.5.57.

| Check | Result |
|---|---|
| Composer dependency installation and package discovery | PASS |
| PHP lint for application, migrations, seeders and routes | PASS |
| Laravel Pint formatting | PASS |
| Route registration | PASS |
| Blade template compilation (`view:cache`) | PASS |
| Storage link (`storage:link`) | PASS |
| MariaDB schema migrations | PASS |
| MariaDB development/demo seeding | PASS |
| Automated suite with SQLite in memory | PASS: 14 tests, 89 assertions |
| Automated suite with isolated MariaDB test database | PASS: 14 tests, 89 assertions |
| Laravel development server starts | PASS |
| Actual HTTP GET home and marketplace, backed by MariaDB | PASS: 200, rendered HTML |

Tests include registration role protection and password hashing; login/logout; listing image upload/replacement/deletion; search filters; private enquiries and seller replies; sold-material rejection; ownership/admin authorization; negative prices, invalid and oversized images; reports and moderation; account suspension; profile password confirmation; public/student page rendering.

## Implemented but not visually verified

Responsive Bootstrap layouts for desktop/tablet/mobile, mobile navigation and browser JavaScript confirmations. Automated browser visual checks could not run: no browser binary was preinstalled and the Chromium download returned an invalid archive. Windows/XAMPP setup is documented but was not executed on a Windows host. Database concurrency was designed using row locks but not load-tested.

## Scope and limitations

All mandatory application modules are implemented. No integrated payment, live chat, delivery tracking, password recovery, email verification flow, or automated copyright detection is included; these are outside the requested first-version workflow. Contact is a useful help page directing users to enquiries, reports and their college administrator; no message is silently sent to an unconfigured address. Student phone fields are private. Reports are removed when their related material is deleted, as documented and implemented with cascading keys.

This is a locally tested college demonstration project, not a claim of production security certification. Composer dependencies must be installed on the destination machine. Local demo passwords are configured by the operator and are never included in the archive.
