# Common AI Instructions for Eclipse PHP packages

## Purpose of the file

This file contains common instructions for AI systems working on Eclipse PHP packages. It can be included in `AGENTS.md`
of any other package.
This lowers the maintenance burden and keeps the instructions consistent across packages.

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
