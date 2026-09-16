# Roles and Authorization

Authorization is enforced on the backend with authentication middleware, role middleware, active-state middleware, and policies where a decision depends on a specific record. Hiding a menu item is only a UI convenience and is never sufficient protection.

## Roles

### Admin

Allowed:

- Admin dashboard.
- View, approve, reject, and inspect PYC/requests.
- Manage user accounts and account active state.
- View their own profile.

Not allowed:

- HR attendance management.
- Mandatory check-in/check-out prerequisite. Admin is explicitly exempt from `checked.in.today`.
- HR-only employee, department, recruitment, or termination operations unless the requirements are later changed.

### HR

Allowed:

- HR dashboard.
- Their own check-in/check-out.
- Tasks.
- Employee, department, and position management.
- Attendance management, including authorized checkout adjustments with a reason.
- Recruitment and termination processes.
- Requests/PYC according to the defined workflow.
- Their own profile.

HR must be active before entering HR workspace routes. The current Auth + Role foundation applies the mandatory check-in middleware only to Employee routes; HR check-in enforcement remains part of the later Attendance phase.

### Employee

Allowed:

- Employee dashboard.
- Their own check-in/check-out.
- Their own tasks.
- Their own requests/PYC.
- Their own profile.

Employees cannot access HR employee, department, position, attendance-management, recruitment, termination, or Admin account-management routes. They must be active and checked in before protected workspace routes.

## Middleware model

- `auth`: user must be authenticated.
- `role`: user role must be one of the roles allowed by the route group.
- `active.user`: account must be active.
- `active.employee`: HR/Employee must have an active employment record; terminated employees cannot use employee workspace functions.
- `checked.in.today`: HR/Employee must have a same-day attendance record with check-in. Admin bypasses this middleware explicitly.

Register aliases in Laravel's middleware configuration and compose middleware at route-group level. Keep `checked.in.today` off login, logout, check-in, check-out, Admin routes, and any route required to reach the check-in screen.

## Route authorization rules

1. Authentication comes before role and active-state checks.
2. Broad role boundaries belong on route groups.
3. Record ownership and individual actions belong in policies or explicit authorization checks.
4. Unauthorized access must return a 403 or the documented redirect; never perform the action.
5. Every state-changing endpoint validates both input and authorization.
6. Admin approval/rejection checks that the PYC is still pending before processing.

## Security expectations

Passwords use Laravel's hashing facilities. Account status is checked on every authenticated request where required. Authorization tests must cover direct URL access and forged form submissions, not only visible navigation.
