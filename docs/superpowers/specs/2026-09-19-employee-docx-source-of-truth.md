# Employee Role DOCX Source-of-Truth Specification

**Date:** 2026-09-19
**Scope:** Employee role only
**Sources:**

- `D:/Dai Hoc/Nam 4/hk1/PHP/DA/Docx/Ke_hoach_va_chuc_nang_lon_Website_QLNS_Laravel_CAP_NHAT(1).docx`
- `D:/Dai Hoc/Nam 4/hk1/PHP/DA/Docx/Hệ thống HRMs_v0.docx`

## Decision

The updated planning document (`CAP_NHAT`) is authoritative where the two DOCX files differ. The older `HRMs_v0` document is used only for compatible detail. This implementation covers employee-facing behavior and the shared persistence needed to submit and track employee requests; it does not expand into HR or Admin management screens.

The application remains a server-rendered Laravel MVC application using direct controller/model functions and Blade views. No API, SPA, email notification, payroll, leave, overtime, shift, PDF export, advanced charting, or audit subsystem is added in this scope.

## Employee Capabilities

### Authentication and access

- An employee can log in and log out.
- An inactive account cannot use protected employee routes.
- Employee routes are server-side protected by authentication and employee role checks.
- An employee must have a valid employee record.
- An employee must check in before entering the employee workspace.
- Admin remains exempt from employee check-in gating; the employee scope does not alter Admin behavior.

### Attendance

- Check-in creates one attendance record for the current employee and work date.
- The database uniqueness rule is `(employee_id, work_date)`.
- A second check-in on the same day is rejected and does not create a second record.
- Check-out is allowed only for today's existing record that has a check-in and no checkout.
- A second check-out is rejected and does not overwrite the original checkout.
- The employee can view today's work status.
- The employee can view their own attendance list and a record detail.
- The employee cannot view another employee's attendance record.
- Employee attendance adjustment submission is represented by an attendance-adjustment PYC, not by directly mutating attendance data.

### Assigned work

- An employee can view only tasks assigned to their employee record.
- The list supports title search, status filtering, due-date ordering/filtering, and pagination when applicable.
- The detail shows title, description, creator, due date, status, and notes.
- The employee can update their task status and progress notes.
- Supported status values are `pending`, `in_progress`, and `completed`.
- The employee cannot assign, delete, or create tasks.

### Personal profile

- The employee can view their own employee code, name, contact fields, department, position, hire date, and employment status.
- Employee code, department, position, hire date, employment status, and account role are read-only to the employee.
- Editable profile requests are submitted as a pending profile-change PYC.
- The original employee row is unchanged while the PYC is pending.
- The PYC payload contains the requested changes and reason.
- Profile changes are applied only by the future Admin approval flow; this employee scope only submits and displays the request.

### Requests/PYC

- The employee can create a general/support PYC and track only their own PYC records.
- The employee can view PYC detail, processing status, processor note, and processing time when present.
- A pending PYC may be edited or cancelled by its owner.
- An approved, rejected, or completed PYC cannot be edited or cancelled by the employee.
- Profile-change and attendance-adjustment PYC payloads retain their business reason.
- The employee cannot approve, reject, or process a PYC.

### Dashboard

- The employee dashboard shows today's attendance state, the employee's current work items, and recent own requests.
- Dashboard data is loaded from persisted models; static placeholder metrics are not treated as business state.

## Domain Invariants

1. All employee-owned queries scope through `auth()->user()->employee` or the authenticated user's id.
2. Authorization is enforced on the server; hiding a link is not sufficient.
3. Attendance `check_out_at` must be later than or equal to `check_in_at`.
4. Check-in and check-out actions are idempotently rejected when their target state already exists.
5. PYC processing is a separate concern from employee submission. Employee actions cannot change a processed PYC.
6. A profile-change PYC never updates the employee record at creation time.
7. Validation is performed at the controller boundary before persistence.

## Route Boundary

Employee routes use the existing `/employee` prefix and must remain behind `auth`, `active.user`, `role:employee`, and the check-in gate where workspace access requires it.

The check-in and check-out actions remain shared routes because the application has shared attendance entry points. Any employee-specific behavior must still validate that the authenticated user has an employee record.

## Existing Code To Align

- `AttendanceCheckInController` currently uses `firstOrCreate` for check-in and overwrites checkout; both behaviors must become explicit rejection paths.
- `EnsureEmployeeCheckedIn` currently gates only role `employee`; no new HR feature is in scope, but employee route access must remain exact.
- `RoleHomeController` currently passes dashboard data without loading all persisted employee widgets.
- `EmployeeTaskController` currently lacks search/filter/pagination and notes validation.
- `EmployeeAttendanceController` currently lists/details own records but does not expose calculated duration/status consistently.
- `EmployeeProfileController` creates profile-change PYC but only accepts a subset of contact fields and has no pending-request guard.
- `EmployeeRequestController` currently allows the employee request type set defined by the current enum; the final set must be documented and validated without introducing undocumented PYC workflows.
- `AdminRequestController` may remain available for existing shared processing tests, but employee-only work must not add Admin UI behavior beyond what is required to keep existing tests valid.

## Acceptance Tests

The employee implementation must cover at least these behaviors from the DOCX test cases:

- Guest is redirected to login.
- Valid employee login reaches check-in when no check-in exists.
- First check-in creates exactly one record.
- Second check-in is rejected.
- Employee cannot access HR/Admin routes.
- Check-out creates the checkout timestamp.
- Second check-out is rejected and preserves the first timestamp.
- Employee attendance list/detail is owner-scoped.
- Employee can list/filter assigned tasks and update status/notes.
- Employee profile PYC is pending and leaves original profile unchanged.
- Employee can create and track own PYC.
- Employee cannot edit or cancel a processed PYC.
- Employee cannot process a PYC belonging to themselves or another user.

## Explicit Non-goals

- HR employee/department management.
- HR global attendance management and attendance correction UI.
- Recruitment and termination processing.
- Admin user-account management.
- Admin PYC approval UI implementation in this employee-only pass.
- Direct employee creation of tasks.
- API endpoints or frontend SPA conversion.
