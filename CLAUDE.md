# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project: "Cómo cambiar tu negocio de transporte"

Management system for a trucking company. It exists to answer one question with trustworthy, traceable data: **what does it really cost to operate each truck, and how much money does each truck really generate?**

Goals: real cost per truck; profitability per trip / route / customer / unit; preventive + corrective maintenance control; fleet availability and utilization; a single KPI dashboard; full traceability from every KPI down to the source records that produced it.

**Status: schema built.** Discovery (business/finance/database docs) is validated enough to have produced the physical schema: 28 migrations (27 domain tables + a `users.role` alter) and their Eloquent models, factories, and enums are in place and migrated against the real MySQL database. No controllers, Livewire screens, Form Requests, Policies, or the financial-calculation service exist yet — those are the next phases (MVP, then Operations/Maintenance/Profitability/Dashboard per the phase list below).

## Mandatory stack

- Laravel 13 / PHP 8.3, Eloquent ORM.
- **MySQL is the single source of truth.** Every schema change goes through a Laravel migration. Never use Google Sheets (or any spreadsheet) as the primary datastore.
- UI: Blade + Livewire 4 + Flux 2 + Tailwind 4 for the MVP. Deviate only with a clearly stated technical reason.
- n8n for automation where it adds value.
- Money: `DECIMAL` columns only, never float. Foreign keys + indexes on every relation. Validation via Form Requests. Authorization via Policies/Gates. Controllers stay thin (logic in actions/services). Every critical financial calculation must have tests.

Confirm an API against the installed version before using it. Notable: Livewire 4.4, Flux 2.19, Fortify 1.39, `laravel/ai` 0.11, PHPUnit 12.5, Larastan 3.12. Frontend build is `vite-plus` (`vp`), not plain Vite.

## The AI agent team (`app/Ai/Agents/`)

The project is built by a simulated senior team. Each role is a `laravel/ai` agent class — implements `Laravel\Ai\Contracts\Agent`, uses the `Promptable` trait, carries `#[MaxTokens]` / `#[Temperature]` attributes, and returns its system prompt from `instructions()`. Use the agent that owns a discipline for that discipline's work; Claude Code orchestrates the pipeline and applies each agent's output, with a human validation gate between phases.

Pipeline order (each phase feeds the next):

1. `BusinessAnalystAgent` — processes, requirements, use cases.
2. `FinancialAnalystAgent` — cost model, profitability, KPIs, ROI.
3. `DataArchitectAgent` — MySQL design, ERD.
4. `LaravelArchitectAgent` — Laravel architecture: patterns, Jobs, Events, Service Providers.
5. `UiUxAgent` — interface, navigation flows, Tailwind UI guidelines.
6. `DeveloperAgent` — implementation: clean PHP, validation, Eloquent.
7. `QaAgent` — unit + integration test plans (PHPUnit).
8. `DataAuditorAgent` — audits calculations, data integrity, simulated transactions.

Invocation: `(new BusinessAnalystAgent)->prompt('...')` returns an `AgentResponse`; also `->stream()`, `->queue()`, and `::fake()` / `::assertPrompted()` for tests. Provider/model resolution falls back to `config('ai.default')` unless the class adds `#[Provider(...)]` / `#[Model(...)]`.
**Provider: Anthropic — confirmed working end-to-end.** `config/ai.php` published with `'default' => 'anthropic'`; `ANTHROPIC_API_KEY` set; account has credits. A real `->prompt()` call has been verified to return an actual Anthropic response. **No agent uses `#[Temperature(...)]`** — `claude-sonnet-5` (the default model) rejects the `temperature` parameter with a 400 (replaced by adaptive thinking on the current model generation); it was removed from all 8 classes. `#[MaxTokens(...)]` is unaffected.

`App\Concerns\{PasswordValidationRules,ProfileValidationRules}` live in `app/Concerns/` (moved from `app/Ai/Concerns/`, where the namespace didn't match the path and Fortify's `CreateNewUser`/`ResetUserPassword` fataled — registration and password reset were broken until this was fixed).

## Working method (from the project brief)

- Work in phases; never generate the whole application at once. Phases: Discovery → Requirements → Financial model → ERD → MySQL model → Migrations → MVP → Operations & expenses → Maintenance → Profitability → Dashboard → n8n → QA → Deploy.
- Do not invent data. Question assumptions. Call out missing data explicitly. Document every formula. Justify architectural decisions. Keep data, logic, and presentation separated.
- **Before any structural change to the data model**, present: impact, affected files, the migration, risks, and rollback strategy.
- Persistent project docs live in `/docs/{business,finance,database,architecture,decisions,testing}` (each has a `README.md` stating its purpose and owning agent). The ERD, data dictionary, financial rules, and architectural decisions are maintained there as living documents.
- Use Git from day one with small, descriptive commits.

### Discovery deliverables (current task — no code, no migrations, no components, no endpoints)

Produce, then wait for validation: (A) business analysis, (B) process map, (C) data to collect, (D) missing data, (E) required entities, (F) relationships, (G) conceptual ERD, (H) data dictionary, (I) business rules, (J) financial formulas, (K) KPIs, (L) questions for the company owner, (M) MVP definition.

Starting entities — a starting point, **not** a final schema; detect missing entities, wrong relationships, and normalization issues first: `trucks`, `drivers`, `customers`, `routes`, `trips`, `fuel_records`, `toll_records`, `maintenance`, `maintenance_schedule`, `tires`, `truck_expenses`, `truck_fixed_costs`, `invoices`, `payments`, `users`.

### Financial model

Must compute: monthly / daily / per-km / per-hour / per-trip cost; revenue per km; profit per km; fuel cost per km; maintenance per km; profitability per truck / trip / customer / route.

Formulas: `profit = revenue − cost`; `margin % = profit / revenue × 100`; `cost per km = total cost / km driven`. Separate direct vs. indirect costs. Do not assume an indirect-cost allocation method — present alternatives and recommend one.

Maintenance tracking: preventive, corrective, history, odometer, cost, provider, downtime, next service, and status `AL_DIA` / `PROXIMO` / `VENCIDO`.

Dashboard: billing, costs, profit, margin, cost/km, revenue/km, utilization, availability, profitability per truck/customer/route, overdue/upcoming maintenance.

## Commands

```
composer run dev          # serve + queue listener + vite, concurrently
npm run dev               # vite-plus dev server only
npm run build             # production asset build (vp build)

php artisan test                       # full suite (JSON output via laravel/pao)
php artisan test --compact             # compact output
php artisan test --filter=test_name    # single test by name
php artisan test tests/Feature/DashboardTest.php   # single file
vendor/bin/phpunit --filter=test_name  # direct runner, same args

vendor/bin/pint                 # fix code style; run `pint --dirty --format agent` before finalizing PHP changes
composer lint                   # pint --parallel
composer types:check            # phpstan / larastan, level 7
composer test                   # config:clear + pint --test + phpstan + artisan test  (mirrors CI)
```

CI (`.github/workflows/tests.yml`): `composer setup` then `composer ci:check` on PHP 8.3 / Node 22, on push to `main` and every PR.

## Architecture notes

- Laravel 13 slim skeleton: `bootstrap/app.php` wires routing/middleware/exceptions (JSON errors for `api/*`); `bootstrap/providers.php` registers only `AppServiceProvider` + `FortifyServiceProvider`.
- `AppServiceProvider::configureDefaults()`: `CarbonImmutable` dates, `DB::prohibitDestructiveCommands` in production, strict production password policy.
- Auth is Fortify (headless). `FortifyServiceProvider` binds each auth view to a Livewire component in `resources/views/livewire/auth/*` and defines `login` / `two-factor` / `passkeys` rate limiters. Custom actions in `app/Actions/Fortify/`. Passkeys via `laravel/passkeys` (+ `resources/js/passkeys.js`); 2FA columns added by migration.
- Routes: `routes/web.php` (`/`, `/dashboard` behind `auth`+`verified`), `routes/settings.php` (profile/appearance/security via `Route::livewire()`), `routes/console.php`.
- Data layer today: only the `User` model plus framework + passkeys + 2FA migrations. `DatabaseSeeder` creates one `test@example.com` user.
- Laravel Boost MCP server is enabled (`.mcp.json`); prefer its tools (`database-schema`, `search-docs`, `tinker`, …).

## Domain data model

27 tables beyond the starter kit's `users` (plus a `role` column added to `users`), matching [`docs/database/conceptual-model.md`](docs/database/conceptual-model.md) with the entity list finalized after the owner's Discovery answers. Migrations: `database/migrations/2026_09_10_2000*`, applied in FK-dependency order (lookups → master data → operations → maintenance → costs → billing → system). Models in `app/Models/`, one per table, using the app's attribute-based `#[Fillable]` convention and a `casts()` method; status/type columns are backed PHP enums in `app/Enums/`. Every model has a factory in `database/factories/`. `cost_types` is seeded with the owner's real categories (`database/seeders/CostTypeSeeder.php`).

Key modelling decisions baked into the schema (see `docs/decisions/000{1,2,3}-*.md`): no capital-cost/depreciation columns; trip distance defaults from `routes.standard_km` (`trucks.current_odometer` is a maintained estimate, re-anchored by `maintenance.odometer`); driver pay (`driver_worklogs`) feeds the overhead pool, not a per-trip cost; overhead allocation is recorded per truck per period in `fixed_cost_allocations`; periods are explicit date ranges (weekly by default), never `YYYY-MM`. `maintenance_schedule` has no stored status column — `AL_DIA`/`PROXIMO`/`VENCIDO` is computed (see `MaintenanceSchedule::status()`).

**The financial-calculation engine is built**, in `app/Services/Financial/`:

- `Period` — explicit date range value object (never `YYYY-MM`); `Period::weekContaining($date)` gives the Thu→Wed week (ADR 0003).
- `ProrationCalculator` — prorates a monthly/quarterly/annual billed amount to a period (§J.3).
- `TireCostEstimator` — straight-line tire amortisation over weeks mounted, from `tire_events` (§J.4).
- `OverheadAllocationService` — computes the overhead pool (prorated `overhead_costs` + `driver_worklogs` pay) and persists the worked-days split to `fixed_cost_allocations` (ADR 0001). `allocate()` is idempotent per period.
- `CostCalculator` — direct + indirect cost breakdown for a truck/period (§J.1); no capital-cost line.
- `FinancialCalculator` — the facade: `truckSummary()`, `tripCost()`, `routeProfitability()`, `customerProfitability()`, `fleetSummary()`, `availability()`, `utilization()`. Customer attribution goes through a trip's `rate_agreement` or, failing that, its `invoice_trip.invoice`; a trip with neither is excluded, not guessed.

All money math happens in `float` internally (rounded to 2dp only at the edges) rather than `bcmath` — the DB columns remain the `DECIMAL` source of truth; this is a reporting layer over them. 28 tests in `tests/{Unit,Feature}/Services/Financial/` cover the formulas and the documented edge cases (zero-trip weeks, corrective-only downtime, proration, unattributed trips).

**Form Requests and Policies exist for the core master data plus `Trip`** (`Truck`,
`Driver`, `Customer`, `Route`, `Trip`): `app/Http/Requests/{Store,Update}{Model}Request.php`
and `app/Policies/{Model}Policy.php`, the latter thin subclasses of
`App\Policies\ModelPolicy` (every role reads; only a non-viewer role — `owner_admin`,
`admin` — writes; `UserRole::canManage()`). Policies rely on Laravel's naming-convention
auto-discovery, no manual registration. `authorize()` is validated against the rule, not
yet against a real route, since no controllers exist to bind one.

**Livewire screens exist for the core master data plus `Trips`** — `Trucks`, `Drivers`,
`Customers`, `Routes`, `Trips`, each a single class-based component combining list +
search + pagination + a create/edit modal + delete, at `app/Livewire/{Plural}/Index.php`
with its view at `resources/views/livewire/{plural}.blade.php` (Livewire's convention for
a class literally named `Index`: no `index.blade.php` subpath). Routed in
`routes/fleet.php` (`trucks`, `drivers`, `customers`, `routes`, `trips`, all
`auth`+`verified`), linked from the sidebar under "Flota". Each validates via its Form
Request's rules and authorizes via its Policy on every action (`mount`, `create`, `edit`,
`save`, `delete`) — never trusts the UI alone.

`Trips` is the more involved screen: selecting a route defaults `distance` to
`route.standard_km` and flags it `distance_estimated = true`
(docs/decisions/0002-trip-distance-source.md); editing the distance by hand clears that
flag. Selecting a `rate_agreement` (filtered to the chosen route, or a customer-wide
agreement with `route_id = null`) defaults `price` and links the trip to a customer for
`FinancialCalculator::customerProfitability()` — but `trips.price` stays authoritative
and editable, and skipping the agreement leaves the trip unattributed to any customer
until it's invoiced (`docs/database/conceptual-model.md`). A completed trip requires an
`actual_end`, since `FinancialCalculator` recognises revenue by that column. `created_by`
is intentionally excluded from the model's `#[Fillable]` list and set directly
(`$trip->created_by = ...`) rather than mass-assigned. The component validates through
`Validator::make()` with an explicit payload — not the usual `$this->validate()` — solely
so an unselected `rate_agreement_id` (`''` from the `<select>`) normalizes to `null`
before the `nullable`+`integer` rule sees it; Livewire still catches the resulting
`ValidationException` and populates the field errors the same way.

Covered by 76 tests total across both layers (`tests/Feature/{Policies,Http/Requests,Livewire}/`).

Not built yet: invoice status derivation (paid/partial/overdue) and IVA line-item tax.

## Current setup gaps (resolve as the relevant phase begins)

- This machine's `C:\php\php.ini` had no CA bundle configured (`curl.cainfo`/`openssl.cafile` empty), so **any** outbound HTTPS call from PHP CLI failed with `cURL error 60: SSL certificate problem`. Fixed by pointing both at a downloaded `C:\php\cacert.pem`. This is a machine-level PHP setting, not part of the repo — note it here in case a fresh machine hits the same `ProviderConnectionException`.
- `docs/business/discovery.md` §L.2: all 10 answered (2026-09-11) and folded in, including the structural one (`payment_allocations`, [ADR 0004](docs/decisions/0004-payment-allocations.md), implemented). Still missing: **route list with standard km/toll** and **per-truck fixed-cost amounts** — both block seeding real data, not the schema.
- `git remote origin` → `github.com/Michael35-ns/transport_bussines_ai.git`; pushed as of the schema-build commit.

---

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application running on PHP 8.3. You are an expert with the Laravel ecosystem. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists (settled decisions, non-obvious traps, standing constraints). Framework and package guidelines that only apply to specific paths (testing, frontend, components) also live there, under `.ai/rules/boost` — this is not just recorded decisions, it is load-bearing guidance you have not seen inline. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.
- Record a rule with `record-rule` only when the user explicitly asks for one. Instructions for the work at hand are not rules, no matter how emphatic: "remove this typo", "use X here" are work to do, not rules to record. Never record a rule on your own initiative, as a byproduct of a change, or to summarize what you just did. When the user does ask, pass a `glob` (e.g. `app/Http/Controllers/**`), a short `title`, and a few-line `note`. Use `record-rule` rather than your native memory or notes tool, because native memory is personal and session-scoped, while only `.ai/rules` is shared with the team and persists in the repo.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.
- Activate the `deploying-to-cloud` skill whenever deploying to Laravel Cloud, configuring Cloud environments or resources, using the Cloud CLI, or troubleshooting Cloud deployments.

=== tests rules ===

# Test Enforcement

- Add or update tests for behavior and logic changes when a test provides meaningful regression coverage.
- Pure copy, styling, and layout-only changes do not require new or updated tests.
- When test coverage applies, run the affected tests and ensure they pass.
- Test the changed behavior and its important failure modes, but do not add tests beyond them.
- Read the `testing-best-practices` skill before writing tests.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== livewire/core rules ===

# Livewire

- Livewire allows you to build dynamic, reactive interfaces in PHP without writing JavaScript.
- You can use Alpine.js for client-side interactions instead of JavaScript frameworks.
- Keep state server-side so the UI reflects it. Validate and authorize in actions as you would in HTTP requests.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This project uses PHPUnit. Create tests with `php artisan make:test --phpunit {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/phpunit` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.

</laravel-boost-guidelines>
