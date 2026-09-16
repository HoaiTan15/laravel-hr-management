# Development Plan

Implement one phase at a time. Do not begin a later phase until the current phase's acceptance criteria and tests pass. Database and workflow decisions listed in [DATABASE.md](DATABASE.md) must be confirmed before Phase 2 migrations.

## Phase 1 — Authentication, roles, and authorization foundation

**Goal:** establish login/logout, role representation, active-account checks, middleware aliases, policies/gates where needed, and role dashboards/routes.

**Expected components:** `app/Models/User.php`, auth controllers/requests/views, `app/Http/Middleware/`, `bootstrap/app.php`, `routes/web.php`, policies, feature tests.

**Dependencies:** clean Laravel skeleton; confirmed login identity and role values.

**Acceptance criteria:** users can log out; inactive users are denied; Admin/HR/Employee route boundaries are enforced by direct requests; Admin is not subject to check-in middleware; no role is authorized by menu visibility alone.

**Tests:** login success/failure, logout, inactive user, each role's allowed/denied routes, Admin check-in exemption.

## Phase 2 — Migrations, models, relationships, and seeders

**Goal:** create the requirements-approved schema and Eloquent relationships from an empty `hr_management` database.

**Expected components:** ordered `database/migrations/`, `app/Models/`, factories, `DatabaseSeeder`, casts/scopes, relationship tests.

**Dependencies:** Phase 1 identity decisions and all unresolved schema decisions resolved.

**Acceptance criteria:** migrations run from an empty database; required foreign keys and unique constraints exist; employee termination preserves history; seeders provide safe development users and reference data.

**Tests:** migration/integrity checks, relationship tests, duplicate employee code and duplicate attendance constraints.

## Phase 3 — Employee, department, and position management

**Goal:** HR CRUD with server-side validation, authorization, search, filter, and pagination.

**Expected components:** HR controllers, Form Requests, policies, Blade list/form/detail views, query scopes, feature tests.

**Dependencies:** Phases 1–2.

**Acceptance criteria:** HR can manage allowed records; invalid input is rejected; employee search/filter/pagination works and retains query parameters; unauthorized roles receive 403.

**Tests:** CRUD, validation, authorization, search/filter/pagination.

## Phase 4 — Attendance and mandatory check-in

**Goal:** employee/HR check-in and check-out, same-day uniqueness, and checked-in workspace middleware.

**Expected components:** attendance model/controller/requests/policy, `checked.in.today` middleware, attendance views, tests.

**Dependencies:** Phases 1–2 and Employee relationship.

**Acceptance criteria:** first check-in creates one row; duplicate check-in is rejected; check-out cannot precede check-in; HR adjustments require a reason; Admin cannot manage attendance; Admin bypasses the prerequisite.

**Tests:** TC01–TC06 and boundary/time validation tests.

## Phase 5 — Tasks, profile, and requests/PYC

**Goal:** personal tasks, profile display/update rules, request creation, and pending-state workflow.

**Expected components:** Task/Profile/Request controllers, Form Requests, policies/services as complexity requires, Blade views, tests.

**Dependencies:** Phases 1–4; finalized request payload and handler rules.

**Acceptance criteria:** employees access only their permitted data; profile changes create pending PYC without immediate mutation; validation and ownership are enforced.

**Tests:** task ownership, profile validation, request creation/state/visibility.

## Phase 6 — HR attendance management, personnel processes, and account management

**Goal:** HR attendance administration, recruitment/termination workflows, and Admin user management.

**Expected components:** HR/Admin controllers, personnel service/transactions, Form Requests, policies, views, tests.

**Dependencies:** Phases 1–5; finalized recruitment/termination process states.

**Acceptance criteria:** HR can complete approved personnel workflows; termination retains history and controls access; Admin can manage accounts and process PYC; Admin cannot manage HR attendance.

**Tests:** recruitment, termination, account active state, transaction rollback, PYC approval/rejection/idempotency.

## Phase 7 — Dashboards, reporting, and Excel export

**Goal:** role dashboards, simple statistics, search/filter/pagination refinements, and explicitly approved Excel export.

**Expected components:** dashboard queries/services, Blade widgets, export classes/package chosen at implementation time, feature tests.

**Dependencies:** all data modules stable; export requirements and package compatibility confirmed.

**Acceptance criteria:** each role sees only its metrics; filters are preserved; exported data respects authorization and filters.

**Tests:** dashboard authorization/data, export content and access.

## Phase 8 — UI polish

**Goal:** responsive Blade layout, navigation, validation/flash states, accessibility, and demo readiness.

**Expected components:** `resources/views/layouts/`, components, CSS/JS, browser/manual checks, UI-focused feature tests where useful.

**Dependencies:** stable routes and workflows.

**Acceptance criteria:** responsive role-aware navigation; forms show errors; no sensitive data is exposed; key flows are demoable in Laragon.

**Tests:** final regression suite, HTTP smoke checks, manual acceptance checklist.
