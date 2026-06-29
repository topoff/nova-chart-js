# Team Memory: nova-chart-js

This file contains deliberate, team-reviewed memory for this project.
It is versioned in this repository and is intended for Claude Code, Codex, and OpenCode.

Keep this file short. It is an agent memory index, not long-form documentation.
If a section grows beyond concise facts and pointers, move details to `docs/` and link them here.

## Stable facts

- Laravel **Nova 5** chart-card package. Fork of `coroo/nova-chartjs` (via `artcore-society/nova-chart-js`), maintained by top-offerten. Provides Stacked/Bar/Line/Area/Pie/Doughnut/Polar/Scatter cards + small API controllers.
- **Composer name `topoff/nova-chart-js`**, but the **PHP namespace stays `Coroowicaksono\ChartJsIntegration`** (composer name ≠ namespace). **Never rename the namespace** — `backend` references those classes directly (`app/Nova/Metrics/...`).
- Distribution: **public on packagist**, consumed by `backend` via `topoff/nova-chart-js`. GitHub repo is **`top-offerten/nova-chart-js`** (org `top-offerten`, NOT `topoff` — push access differs from the other topoff packages).
- `laravel/nova` is a **private, licensed** require → `composer install` needs `nova.laravel.com` auth (kept as a `repositories` entry in the package's own `composer.json`).
- Ships built JS in `dist/` (source in `resources/js`); `CardServiceProvider` registers the Nova script + `routes/api.php`.

## Architecture pointers

- Cards + API: `src/*Chart.php`, `src/api/` controllers, `src/CardServiceProvider.php`, `routes/api.php`.
- Tooling mirrors the other topoff packages: `rector.php`, `phpstan.neon.dist` (larastan, level 5, `phpstan-baseline.neon`), Pint default preset; CI `phpstan.yml` + `fix-php-code-style-issues.yml`.

## Commands and workflows

- Static analysis + style: `composer clean` (Rector + Pint + PHPStan). `composer format` / `composer analyse` / `composer lint`.
- **PHPStan baseline must be seeded once** (`vendor/bin/phpstan analyse --generate-baseline`) — needs Nova installed (license), so it can't run in CI/agents without `nova.laravel.com` auth.
- Release: commit → `git tag vX.Y.Z` → push branch + tag → ensure packagist syncs (then `composer update topoff/nova-chart-js` in `backend`). A stable tag is required; `^1.0.0` will not match `dev-master` alone.

## Conventions specific to this project

- No test suite and no Spatie package-skeleton (workbench/testbench) — intentional for a vendored Nova fork. Don't add them without a reason.
- Keep the fork minimal; prefer upstreaming or thin patches over diverging from `coroo/nova-chartjs` behavior.

## Do not store here

- Personal preferences.
- Temporary debugging notes.
- Secrets, credentials, API keys, tokens, or private customer data.
- Facts that belong only to another project.
- Long-form agent documentation that belongs in `docs/`.
