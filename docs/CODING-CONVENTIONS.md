# Coding Conventions

## PHP and Laravel

- Follow PSR-12 and the repository's Laravel/Pint configuration.
- Use strict, descriptive types where compatible with Laravel conventions.
- Keep one class per file and follow PSR-4 namespaces under `App\`.
- Prefer constructor injection and framework contracts over service-locator calls in reusable code.
- Use Laravel naming conventions: `Employee`, `EmployeeController`, `StoreEmployeeRequest`, `CreateEmployeesTable`, and `employees`.

## Routes and controllers

- Name routes and use HTTP verbs correctly.
- Group routes by role/module and attach middleware explicitly.
- Keep controllers thin: validate, authorize, call a model/service, then return a view or redirect.
- Use named redirects and consistent flash messages.
- Do not put SQL, large workflows, or reusable business rules in route closures.

## Form Requests and validation

- Validate every user-controlled value on the server.
- Use Form Requests for non-trivial create/update operations.
- Use database-aware rules (`exists`, `unique`) with the correct scoped records.
- Normalize input only where the domain requires it; do not silently discard user data.
- Validation failures must preserve the form's error feedback through Laravel's error bag.

## Authorization

- Use middleware for route-wide authentication/role boundaries.
- Use policies or explicit authorization checks for individual records and ownership.
- Never rely on disabled links, hidden menu items, or JavaScript for access control.
- Add a feature test for every important allow/deny rule.

## Models and database

- Keep table/column names conventional and relationships explicit.
- Use `$fillable` or guarded assignment deliberately; never mass-assign uncontrolled request data.
- Use casts for dates, booleans, enums, and JSON payloads.
- Prefer Eloquent relationships/scopes and Query Builder for ordinary queries. Avoid raw SQL unless it is necessary, reviewed, and parameterized.
- Use transactions for multi-record business workflows. Keep database constraints as a final invariant, not just application validation.
- Do not hard-code credentials, tokens, or environment-specific URLs.
- Do not delete employee history when the requirement requires retention.

## Migrations and seeders

- Make migrations reversible where practical and safe.
- Use foreign keys and unique indexes explicitly.
- Keep migration order dependency-safe and test from an empty database.
- Seeders are repeatable development data and use hashed demo passwords. Never seed production secrets.
- Do not use destructive migration commands against a database with data unless explicitly approved.

## Blade and frontend

- Use layouts, components, `@extends`, `@section`, and `@include` to reduce duplication.
- Keep business logic and queries out of views.
- Escape output with `{{ }}` by default; use raw output only for trusted/sanitized content.
- Include `@csrf` in state-changing forms and use method spoofing as needed.
- Use role-aware navigation only as presentation; backend authorization remains mandatory.
- Keep frontend JavaScript progressive and do not move backend behavior into a SPA.

## Naming and formatting

- Variables and methods use camelCase; database columns use snake_case.
- Boolean names should read clearly (`is_active`, `has_checked_in`).
- Blade files use lowercase kebab/snake conventions consistent with their view directory.
- Run formatting, syntax checks, tests, and `composer dump-autoload` before handoff.

## Review checklist

Reviewers should check authorization, validation, mass assignment, transaction boundaries, database constraints, escaped output, test coverage, and accidental secrets before approving a change.
