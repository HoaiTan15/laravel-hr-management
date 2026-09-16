# Architecture

## Scope

This repository is a Laravel 13 application for the HR Management system. This document defines the implementation boundaries; it does not implement HR features.

## Request flow

1. `public/index.php` boots Laravel.
2. `bootstrap/app.php` configures routing, middleware, and exception handling.
3. `routes/web.php` maps named HTTP routes to controllers.
4. Middleware handles authentication, account/employee status, role authorization, and check-in prerequisites.
5. Form Requests authorize and validate incoming data.
6. Controllers coordinate the use case and return a view, redirect, or response.
7. Models and Eloquent relationships query and persist data.
8. Blade renders presentation only.

## Responsibilities

- **Routes:** URL, HTTP verb, route name, controller action, and middleware composition. Keep business logic out of route closures.
- **Middleware:** Cross-cutting request gates such as authentication, active account, role, active employee, and mandatory check-in. Middleware must enforce backend access; menus are not security controls.
- **Controllers:** Thin orchestration layer. Accept validated input, call models/services, and return a consistent response. Do not contain large reusable workflows.
- **Form Requests:** Request authorization and validation rules. Use one request class per meaningful write operation when rules are non-trivial.
- **Models:** Eloquent attributes, casts, relationships, scopes, and small model-level invariants. Avoid putting unrelated workflows in models.
- **Policies/Gates:** Resource-level authorization, especially ownership and per-record decisions. Use role middleware for broad route boundaries and policies for individual records.
- **Services:** Optional application services for multi-step workflows or transactions, such as PYC approval and personnel processes. Do not create service classes for trivial CRUD.
- **Views:** Blade templates under `resources/views`. Views format data and submit forms; they do not query the database or implement authorization decisions.
- **Migrations:** Versioned schema changes. They must work on an empty `hr_management` database and must not rely on manual SQL.
- **Seeders/Factories:** Repeatable development and test data only. Never store real credentials.
- **Tests:** Feature tests for HTTP/auth/authorization/workflows and unit tests for isolated rules or services.

## Authentication and authorization

Use Laravel's authentication stack and hashed passwords. Every protected route uses `auth`; role middleware and policies add authorization. Account and employee active-state checks are separate from authentication. The checked-in-today rule applies to HR and Employee workspace routes, while Admin bypasses that prerequisite. See [AUTHORIZATION.md](AUTHORIZATION.md).

## Route organization

Use `routes/web.php` with named routes and grouped middleware. Keep URL groups explicit (`/admin`, `/hr`, and shared authenticated routes). Route names use dot notation by resource, for example `hr.employees.index` and `attendance.check-in`. Avoid route closures for application behavior.

## Data and validation rules

Use Eloquent relationships and query scopes first; use the query builder for clear aggregate or reporting queries. Raw SQL requires a documented reason. Validate all input server-side with Form Requests. Use transactions for workflows that update multiple records and must succeed or fail together.

## Response conventions

- GET pages return `view(...)`.
- Successful state changes redirect to a named route with a localized flash message.
- Validation failures use Laravel's normal redirect-back error bag.
- Unauthorized users receive Laravel's authorization response (normally 403); unauthenticated users are redirected to login.
- Do not expose exception details or credentials in production responses.

## Blade conventions

Use layouts, components, sections, and includes instead of duplicated HTML. Escape output with `{{ }}` by default. Use `{!! !!}` only for explicitly trusted, sanitized content. Include `@csrf` in state-changing forms and use method spoofing for PUT/PATCH/DELETE. Keep queries, raw SQL, and business workflows out of Blade.

## Naming

Use Laravel conventions: singular PascalCase models (`Employee`), plural snake_case tables (`employees`), StudlyCase controllers (`EmployeeController`), descriptive Form Requests (`StoreEmployeeRequest`), and snake_case migration filenames. PHP classes use namespaces under `App\` and PSR-4 paths.

## Testing boundaries

Feature tests should exercise the application through HTTP and the database. Unit tests should cover isolated services, policies, and domain rules. Every authorization and attendance invariant should have a regression test. See [TESTING.md](TESTING.md).
