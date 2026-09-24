<laravel-boost-guidelines>
=== .ai/10-project-specifics rules ===

# About the package

This is a Laravel package for shared Eclipse PHP / Filament functionality. All Eclipse PHP packages require
this package and build on the Foundation classes in `src/Foundation` or use other common implementation classes.

Also, read and take into account the [README.md](README.md) file for project-specific documentation.

=== .ai/20-common-ai-guidelines rules ===

# Common AI Instructions for Eclipse PHP packages

This section contains common instructions for AI systems working on Eclipse PHP packages. It is intended to be included
by Laravel Boost to create AI agent-focused documentation (e.g the `AGENTS.md` file).  
This lowers the maintenance burden and keeps the instructions consistent across packages.  
All paths and commands in this file are relative to the root of the package.
Any preceding or following documentation referring to running commands and tests, coding guidelines, environment setup,
and other technical details should be adapted by taking these common guidelines into account.

## Project setup

- Source code lives in `src/`
- Tests live in `tests/`
- Workbench (testing) application lives in `workbench/`
- Package testbench configuration is in `testbench.yaml`

## Technology Stack

- PHP 8.3+
- Filament
- Orchestra Testbench
- Pest / PHPUnit
- Composer
- Laravel Pint
- Lando / Docker for development

## Commands

Because the application in development runs in a Docker container, using Lando as a wrapper, use the Lando commands
to run commands inside the container.

### Run a PHP command

```shell
lando php <command>
```

### Run a composer command

```shell
lando composer <command>
```

### Run an artisan command

To run a command against the workbench application, use the `testbench` command instead of `artisan`:
```shell
lando testbench <command>
```
E.g.
```shell
lando testbench migrate
```

## Coding Standards

- Follow Laravel conventions.
- Follow PSR-12 style.
- Use Laravel Pint for formatting.
- Prefer typed properties, return types, and constructor property promotion where appropriate.
- Keep classes small and focused.
- Use clear, descriptive names.
- Avoid unnecessary abstractions.
- Do not introduce dependencies unless they are clearly justified.

### Running the Linter (Laravel Pint)

```shell
lando format
```
To pass parameters to the internal tool, use double dash before the parameters:
```shell
lando format -- <parameters>
```
E.g.
```shell
lando format -- --dirty
```

Before considering a task complete, run:
```shell
lando format
lando test
```

## PHP Guidelines

- Use PHP 8.3-compatible syntax only.
- Prefer strict, readable code over clever code.
- Use enums where they improve clarity.
- Use Laravel collections only when they make the code easier to read.
- Prefer dependency injection over facades when practical.
- Avoid global helpers unless they already exist in the project and are appropriate.

## Laravel Package Guidelines

- Keep package code inside `src/`.
- Use the namespace defined in `composer.json` for package classes.
- Register package services through the package service provider.
- Keep workbench-only code inside `workbench/`.
- Do not place application-specific logic in package source code.
- Avoid publishing config, migrations, or assets unless required by the package behavior.

## Testing Guidelines

- Use Pest for new tests unless an existing nearby test uses PHPUnit style.
- Place feature tests in `tests/Feature`.
- Place unit tests in `tests/Unit`.
- Place testing support classes in `tests/Support`.
- Use Orchestra Testbench patterns for package tests.
- Prefer testing public behavior rather than private implementation details.
- Add or update tests when changing behavior.

### Running Tests

To run all tests:
```shell
lando test
```
To run specific tests or to pass parameters to Pest:
```shell
lando test -- <parameters>
```
E.g.:
```shell
lando test -- --filter=ArchTest
```
If running the full test suite is too expensive, run the most relevant test file and explain what was run.

## Documentation

- Update `README.md` when changing public package behavior.
- Update examples when APIs change.
- Keep documentation concise and practical.
- Do not update the `CHANGELOG.md` file — it is automatically updated by a GitHub Action.

## AI Behavior Rules

When working in this repository:

1. Inspect existing nearby code before proposing changes.
2. Preserve existing project structure and naming conventions.
3. Prefer small, incremental changes.
4. Do not rewrite files unnecessarily.
5. Do not add new packages without asking.
6. Do not change public APIs without explaining the impact.
7. Include tests for behavior changes.
8. Mention any commands that should be run to verify the change.

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
- Follow existing application Enum naming conventions.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.
- Activate the `deploying-to-cloud` skill whenever deploying to Laravel Cloud, configuring Cloud environments or resources, using the Cloud CLI, or troubleshooting Cloud deployments.

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

=== laravel/v12 rules ===

# Laravel 12

- CRITICAL: ALWAYS use `search-docs` tool for version-specific Laravel documentation and updated code examples.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

## Laravel 12 Structure

- In Laravel 12, middleware are no longer registered in `src/Http/Kernel.php`.
- Middleware are configured declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- The `src/Console/Kernel.php` file no longer exists; use `bootstrap/app.php` or `routes/console.php` for console configuration.
- Console commands in `src/Console/Commands/` are automatically available and do not require manual registration.

## Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.

- Laravel 12 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

# Pest

- This project uses Pest. Create tests with `php artisan make:test --pest {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.
- Do not delete tests or test files without approval. They are part of the application.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/pest` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.
- After the feature tests pass, ask the user to run the complete suite with `php artisan test --compact`.

</laravel-boost-guidelines>
