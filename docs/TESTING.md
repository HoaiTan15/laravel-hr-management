# Testing Strategy

Tests are intentionally not implemented for the HR modules in this handoff preparation. This document defines what the next developer must add as each phase is built.

## Test layers

### Feature tests

Use Laravel feature tests for HTTP behavior, authentication, middleware, authorization, validation, database mutations, redirects, flash messages, and complete workflows. Use factories and the test database; do not depend on local Laragon data.

### Unit tests

Use unit tests for isolated policies, value/status rules, service methods, and date/time invariants that do not need a full HTTP request. Keep tests deterministic and avoid real external services.

### Database tests

Use `RefreshDatabase` only with an isolated test database. Test migrations, relationships, foreign keys, unique indexes, casts, and transaction rollback. Never point automated tests at a developer's shared database.

## Module mapping

| Module | Required coverage |
|---|---|
| Authentication | Valid/invalid login, logout, session regeneration, inactive account denial, password hashing |
| Roles | Admin/HR/Employee allowed and denied routes, direct URL and forged request checks |
| Active middleware | Inactive user and terminated employee behavior |
| Check-in middleware | HR/Employee blocked before check-in, Admin bypass, check-in route remains accessible |
| Employee/Department/Position CRUD | Authorization, validation, relationships, search, filters, pagination, retained query parameters |
| Attendance | First check-in, duplicate same-day check-in, check-out ordering, duplicate checkout, HR adjustment reason, Admin denial |
| Tasks | Creation/assignment ownership, status transitions, permitted updates, visibility |
| Profile | Display authorization, allowed fields, validation, no unauthorized edits |
| Requests/PYC | Pending creation, original profile preservation, Admin approval/rejection, handler/time/note, idempotency |
| Personnel processes | Recruitment/termination authorization, status transitions, transaction behavior, historical retention |
| Account management | Admin-only access, active/inactive transitions, password handling |
| Dashboard | Role-specific access and data scope |
| Search/filter/pagination | Correct results, combinations, parameter persistence, authorization scope |
| Export | Authorized rows only, filter parity, output format/content, denial for unauthorized roles |

## Acceptance mapping

Implement the scenarios in [ACCEPTANCE-CRITERIA.md](ACCEPTANCE-CRITERIA.md) as feature tests, especially TC01–TC14. Each regression test should arrange a minimal factory graph, perform one meaningful request, and assert both the response and database state.

## Test commands

```bash
php artisan test
php artisan test --testsuite=Feature
php artisan test --filter=AttendanceTest
```

Before submitting a change, run the focused tests and then the full suite. Also run `php artisan route:list`, `composer validate`, and `composer dump-autoload` when routes or namespaces change.
