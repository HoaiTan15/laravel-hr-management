# Employee DOCX Alignment Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Align the Employee role's authentication gate, attendance, assigned work, personal profile, PYC, and dashboard behavior with the two DOCX requirements, using direct Laravel MVC functions and Blade views.

**Architecture:** Keep the existing Laravel MVC structure and `/employee` route boundary. Controllers perform request validation and call Eloquent models directly; middleware handles active-account, role, and check-in gates; Blade renders server-side pages. Employee-owned records are always scoped through the authenticated employee or user id, while Admin/HR management remains outside this pass.

**Tech Stack:** Laravel 13, PHP 8.3+, Eloquent, Blade, PHPUnit/Laravel HTTP feature tests, Vite/Tailwind assets.

**Spec:** `docs/superpowers/specs/2026-09-19-employee-docx-source-of-truth.md`

## Global Constraints

- The updated `CAP_NHAT` DOCX is authoritative where the two documents differ.
- Scope is Employee-facing functionality only; do not add HR/Admin management workflows.
- Use server-rendered Laravel MVC and direct controller/model functions; do not add API endpoints or SPA code.
- Employee routes require `auth`, `active.user`, `role:employee`, and the employee check-in gate where workspace access is required.
- Employee code, department, position, hire date, employment status, and account role remain read-only to the employee.
- A profile-change PYC must not modify the employee row while pending.
- Every employee-owned query and route-model action must be server-side owner scoped.
- Attendance is unique by `(employee_id, work_date)` and a checkout cannot overwrite an existing checkout.
- No production behavior is added without a failing test first.

## Review Focus

- Duplicate check-in/check-out submissions: must reject without creating a duplicate or overwriting the first timestamp. Covered in Task 2.
- Cross-employee URL tampering for attendance, tasks, and PYC: must return `403` and reveal no record. Covered in Tasks 2, 3, and 5.
- Pending versus processed PYC mutation: pending owner may edit/cancel; processed owner may only view. Covered in Task 5.
- Profile-change submission with empty or protected fields: must validate the request and leave protected fields unchanged. Covered in Task 4.
- Dashboard access before check-in and after check-in: must redirect before check-in and render persisted employee data after check-in. Covered in Tasks 2 and 6.

## File Map

### Files to modify

- `app/Http/Controllers/AttendanceCheckInController.php` — enforce employee ownership, duplicate rejection, checkout state rules, and safe redirects/messages.
- `app/Http/Controllers/RoleHomeController.php` — load persisted employee dashboard widgets.
- `app/Http/Controllers/EmployeeTaskController.php` — add owner-scoped search/filter/pagination and notes/status validation.
- `app/Http/Controllers/EmployeeAttendanceController.php` — expose own-record status and calculated duration data.
- `app/Http/Controllers/EmployeeProfileController.php` — validate editable fields, protect immutable fields, and prevent duplicate pending profile requests.
- `app/Http/Controllers/EmployeeRequestController.php` — tighten employee-owned PYC validation and pending-only mutation behavior.
- `app/Models/Attendance.php` — expose safe status/duration helpers used by employee views.
- `app/Models/Request.php` — expose pending/processed state helpers and normalized payload access.
- `app/Http/Middleware/EnsureEmployeeCheckedIn.php` — preserve employee-only gate and make missing employee records fail safely.
- `routes/web.php` — keep only the employee routes needed by the spec and apply middleware consistently.
- `resources/views/employee-dashboard.blade.php` — render persisted attendance/task/PYC state and supported actions.
- `resources/views/employee-tasks.blade.php` — render search/filter/pagination controls and task notes/status.
- `resources/views/employee-task-show.blade.php` — render task details and status/notes update form.
- `resources/views/employee-attendance.blade.php` — render own attendance status, duration, and detail links.
- `resources/views/employee-attendance-show.blade.php` — render one own attendance detail and adjustment-PYC entry point.
- `resources/views/employee-profile.blade.php` — make protected fields read-only and display pending profile requests.
- `resources/views/employee-request-form.blade.php` — render only documented employee PYC fields/types.
- `resources/views/employee-request-show.blade.php` — render request detail, status, processor note, and pending actions.
- `resources/views/employee-requests.blade.php` — render own request history and status filters/pagination.
- `tests/Feature/AuthAndRoleTest.php` — extend access and check-in coverage where behavior changes.

### Files to create

- `tests/Concerns/CreatesEmployee.php` — reusable test fixture for an employee, department, position, and active user.
- `tests/Feature/EmployeeAttendanceTest.php` — attendance red/green tests.
- `tests/Feature/EmployeeTaskTest.php` — task ownership, filtering, status, and notes tests.
- `tests/Feature/EmployeeProfileTest.php` — profile visibility and profile-change PYC tests.
- `tests/Feature/EmployeeRequestTest.php` — general PYC ownership and pending/processed mutation tests.
- `tests/Feature/EmployeeDashboardTest.php` — persisted dashboard rendering and check-in gate tests.

### Files intentionally not modified for new business behavior

- `app/Http/Controllers/AttendanceManagementController.php`
- `app/Http/Controllers/AdminRequestController.php`
- `resources/views/admin-requests.blade.php`
- `resources/views/admin-request-show.blade.php`
- HR management routes and views
- `database/migrations/*` unless a failing test proves an existing schema invariant cannot be represented safely

## Implementation Tasks

### Task 1: Establish employee test fixtures and baseline route contract

**Files:**
- Create: `tests/Concerns/CreatesEmployee.php`
- Create: `tests/Feature/EmployeeDashboardTest.php`
- Modify: `tests/Feature/AuthAndRoleTest.php`
- Test: `tests/Feature/EmployeeDashboardTest.php`, `tests/Feature/AuthAndRoleTest.php`

**Interfaces:**
- Produces `createEmployee(array $userOverrides = [], array $employeeOverrides = []): Employee` for feature tests.
- Keeps the existing route names `employee.home`, `attendance.check-in`, and `logout` stable.

- [ ] **Step 1: Write the failing persisted-dashboard tests.** Assert that a checked-in employee sees their own task title and own request title, while a second employee's records do not appear.

```php
public function test_checked_in_employee_dashboard_uses_only_persisted_own_data(): void
{
    $employee = $this->createEmployee();
    $other = $this->createEmployee();
    Attendance::create(['employee_id' => $employee->id, 'work_date' => today(), 'check_in_at' => now()]);
    Task::create(['assigned_to' => $employee->id, 'created_by' => $other->user_id, 'title' => 'Own task', 'status' => 'pending']);
    Task::create(['assigned_to' => $other->id, 'created_by' => $other->user_id, 'title' => 'Other task', 'status' => 'pending']);
    RequestModel::create(['created_by' => $employee->user_id, 'type' => 'general', 'payload' => ['title' => 'Own request'], 'status' => 'pending']);
    RequestModel::create(['created_by' => $other->user_id, 'type' => 'general', 'payload' => ['title' => 'Other request'], 'status' => 'pending']);

    $this->actingAs($employee->user)->get(route('employee.home'))
        ->assertOk()
        ->assertSee('Own task')
        ->assertSee('Own request')
        ->assertDontSee('Other task')
        ->assertDontSee('Other request');
}
```

- [ ] **Step 2: Run the focused tests and verify the failure is caused by static/non-scoped dashboard data.**

Run: `php artisan test tests/Feature/EmployeeDashboardTest.php tests/Feature/AuthAndRoleTest.php`

Expected: FAIL with the new persisted-data assertions, not a PHP parse error.

- [ ] **Step 3: Add the reusable fixture and implement the smallest dashboard data contract.** The fixture creates an active `User`, `Department`, `Position`, and `Employee`; `RoleHomeController::employee()` loads today's attendance, the employee's five latest tasks, and five latest own requests.

- [ ] **Step 4: Run the focused tests and the full existing suite.**

Run: `php artisan test tests/Feature/EmployeeDashboardTest.php tests/Feature/AuthAndRoleTest.php`  
Run: `php artisan test`

Expected: focused tests and all existing tests pass.

- [ ] **Step 5: Commit the fixture/dashboard contract.**

```text
git add tests/Concerns/CreatesEmployee.php tests/Feature/EmployeeDashboardTest.php tests/Feature/AuthAndRoleTest.php app/Http/Controllers/RoleHomeController.php resources/views/employee-dashboard.blade.php
git commit -m "test: establish employee dashboard contract"
```

### Task 2: Enforce Employee attendance state transitions

**Files:**
- Create: `tests/Feature/EmployeeAttendanceTest.php`
- Modify: `app/Http/Controllers/AttendanceCheckInController.php`
- Modify: `app/Http/Middleware/EnsureEmployeeCheckedIn.php`
- Modify: `app/Models/Attendance.php`
- Modify: `resources/views/employee-dashboard.blade.php`
- Modify: `resources/views/employee-attendance.blade.php`
- Modify: `resources/views/employee-attendance-show.blade.php`
- Test: `tests/Feature/EmployeeAttendanceTest.php`, `tests/Feature/AuthAndRoleTest.php`

**Interfaces:**
- `AttendanceCheckInController::store()` creates exactly one current-day record or redirects back with an error when one exists.
- `AttendanceCheckInController::checkout()` updates only a current-day record with a null `check_out_at`; repeated checkout redirects with an error.
- `Attendance::durationInMinutes(): ?int` and `Attendance::workStatus(): string` are view helpers with no persistence side effects.

- [ ] **Step 1: Write failing tests for first/duplicate check-in, checkout, duplicate checkout, and cross-owner detail access.** Assert record count and the original checkout timestamp remain unchanged.

```php
public function test_employee_cannot_check_in_twice_on_the_same_day(): void
{
    $employee = $this->createEmployee();
    $this->actingAs($employee->user)->post(route('attendance.check-in.store'))->assertRedirect();
    $this->actingAs($employee->user)->post(route('attendance.check-in.store'))
        ->assertSessionHasErrors('attendance');
    $this->assertDatabaseCount('attendances', 1);
}

public function test_employee_cannot_check_out_twice_or_overwrite_first_checkout(): void
{
    $employee = $this->createEmployee();
    $first = now()->addMinutes(10);
    Attendance::create(['employee_id' => $employee->id, 'work_date' => today(), 'check_in_at' => now()]);

    Carbon::setTestNow($first);
    $this->actingAs($employee->user)->post(route('attendance.check-out'))->assertRedirect();
    $saved = Attendance::firstOrFail()->check_out_at;
    Carbon::setTestNow($first->copy()->addHour());
    $this->actingAs($employee->user)->post(route('attendance.check-out'))
        ->assertSessionHasErrors('attendance');
    $this->assertTrue($saved->equalTo(Attendance::firstOrFail()->check_out_at));
}
```

- [ ] **Step 2: Run the attendance tests to verify expected RED failures.**

Run: `php artisan test tests/Feature/EmployeeAttendanceTest.php`

Expected: FAIL because duplicate check-in currently uses `firstOrCreate` and checkout currently overwrites an existing timestamp.

- [ ] **Step 3: Implement explicit state checks and model helpers.** Use an employee-record guard, `exists()`/`whereNull('check_out_at')`, validation errors on the `attendance` key, and preserve the first checkout. Do not add attendance correction mutation for Employee.

- [ ] **Step 4: Update attendance views to use `workStatus()` and `durationInMinutes()` and show the check-in/check-out action only for the valid current state.**

- [ ] **Step 5: Run focused and full tests.**

Run: `php artisan test tests/Feature/EmployeeAttendanceTest.php tests/Feature/AuthAndRoleTest.php`  
Run: `php artisan test`

Expected: all pass.

- [ ] **Step 6: Commit attendance transitions.**

```text
git add app/Http/Controllers/AttendanceCheckInController.php app/Http/Middleware/EnsureEmployeeCheckedIn.php app/Models/Attendance.php resources/views/employee-dashboard.blade.php resources/views/employee-attendance.blade.php resources/views/employee-attendance-show.blade.php tests/Feature/EmployeeAttendanceTest.php
git commit -m "feat: enforce employee attendance transitions"
```

### Task 3: Align assigned-task listing and progress updates

**Files:**
- Create: `tests/Feature/EmployeeTaskTest.php`
- Modify: `app/Http/Controllers/EmployeeTaskController.php`
- Modify: `resources/views/employee-tasks.blade.php`
- Modify: `resources/views/employee-task-show.blade.php`
- Modify: `routes/web.php` only if a named notes/status endpoint is required by the existing form contract
- Test: `tests/Feature/EmployeeTaskTest.php`

**Interfaces:**
- `GET /employee/tasks` accepts `q`, `status`, `due_from`, and `due_to`; results are owner-scoped and paginated.
- The task update action accepts `status` in `pending|in_progress|completed` and nullable `notes` with the existing task ownership check.

- [ ] **Step 1: Write failing tests for owner scope, title/status filtering, pagination, status validation, notes persistence, and forbidden cross-owner update.**

```php
public function test_employee_can_filter_own_tasks_and_save_status_and_notes(): void
{
    $employee = $this->createEmployee();
    $other = $this->createEmployee();
    Task::create(['assigned_to' => $employee->id, 'created_by' => $other->user_id, 'title' => 'Prepare report', 'status' => 'pending']);
    Task::create(['assigned_to' => $employee->id, 'created_by' => $other->user_id, 'title' => 'Ignore completed', 'status' => 'completed']);
    Task::create(['assigned_to' => $other->id, 'created_by' => $other->user_id, 'title' => 'Other report', 'status' => 'pending']);

    $this->actingAs($employee->user)->get(route('employee.tasks.index', ['q' => 'Prepare', 'status' => 'pending']))
        ->assertOk()->assertSee('Prepare report')->assertDontSee('Other report')->assertDontSee('Ignore completed');

    $task = Task::where('title', 'Prepare report')->firstOrFail();
    $this->actingAs($employee->user)->put(route('employee.tasks.status', $task), ['status' => 'in_progress', 'notes' => 'Started review'])
        ->assertRedirect(route('employee.tasks.show', $task));
    $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => 'in_progress', 'notes' => 'Started review']);
}
```

- [ ] **Step 2: Run the focused test and verify RED because the current list ignores filters and the update action ignores notes.**

Run: `php artisan test tests/Feature/EmployeeTaskTest.php`

- [ ] **Step 3: Implement query filters, stable due-date ordering, pagination, and notes validation without adding task creation/deletion.** Keep the current owner authorization as a server-side guard.

- [ ] **Step 4: Update list/detail forms to expose only documented employee actions.**

- [ ] **Step 5: Run the focused test and then the full suite.**

Run: `php artisan test tests/Feature/EmployeeTaskTest.php`  
Run: `php artisan test`

- [ ] **Step 6: Commit task behavior.**

```text
git add app/Http/Controllers/EmployeeTaskController.php resources/views/employee-tasks.blade.php resources/views/employee-task-show.blade.php tests/Feature/EmployeeTaskTest.php
git commit -m "feat: align employee task progress flow"
```

### Task 4: Protect the Employee profile and create profile-change PYC

**Files:**
- Create: `tests/Feature/EmployeeProfileTest.php`
- Modify: `app/Http/Controllers/EmployeeProfileController.php`
- Modify: `app/Models/Request.php`
- Modify: `resources/views/employee-profile.blade.php`
- Test: `tests/Feature/EmployeeProfileTest.php`

**Interfaces:**
- `GET /employee/profile` shows immutable identity/employment data and the employee's own pending profile-change request state.
- `POST /employee/profile/change-request` accepts only documented editable contact fields plus a required reason and creates a pending request without changing `employees`.

- [ ] **Step 1: Write failing tests for read-only profile fields, pending profile request, unchanged employee row, duplicate pending request rejection, and cross-user isolation.**

```php
public function test_profile_change_creates_pending_request_without_changing_employee(): void
{
    $employee = $this->createEmployee(['phone' => '0900000000']);

    $this->actingAs($employee->user)->post(route('employee.profile.change-request'), [
        'phone' => '0900000001', 'email' => 'new@example.test', 'address' => 'New address', 'reason' => 'Contact changed',
    ])->assertRedirect(route('employee.profile'));

    $this->assertDatabaseHas('requests', ['created_by' => $employee->user_id, 'type' => 'profile_change', 'status' => 'pending']);
    $this->assertSame('0900000000', $employee->fresh()->phone);
}
```

- [ ] **Step 2: Run the profile tests and verify RED for missing pending guard/field contract.**

Run: `php artisan test tests/Feature/EmployeeProfileTest.php`

- [ ] **Step 3: Implement protected-field validation and a single pending profile-change request rule.** Store requested editable values under a normalized `changes` payload and preserve `reason`; return a validation error when another pending profile-change request exists.

- [ ] **Step 4: Update the profile view so protected data is not rendered as editable inputs and pending request details are visible.**

- [ ] **Step 5: Run focused and full tests.**

Run: `php artisan test tests/Feature/EmployeeProfileTest.php`  
Run: `php artisan test`

- [ ] **Step 6: Commit profile/PYC submission behavior.**

```text
git add app/Http/Controllers/EmployeeProfileController.php app/Models/Request.php resources/views/employee-profile.blade.php tests/Feature/EmployeeProfileTest.php
git commit -m "feat: protect employee profile changes behind requests"
```

### Task 5: Align Employee PYC ownership and lifecycle

**Files:**
- Create: `tests/Feature/EmployeeRequestTest.php`
- Modify: `app/Http/Controllers/EmployeeRequestController.php`
- Modify: `app/Models/Request.php`
- Modify: `resources/views/employee-request-form.blade.php`
- Modify: `resources/views/employee-request-show.blade.php`
- Modify: `resources/views/employee-requests.blade.php`
- Test: `tests/Feature/EmployeeRequestTest.php`

**Interfaces:**
- Employee request queries are scoped to `created_by = auth()->id()`.
- Pending requests may be edited or cancelled only by their owner.
- Processed requests are read-only to the employee.
- Employee cannot call any Admin processing endpoint; existing role middleware remains authoritative.

- [ ] **Step 1: Write failing tests for creating own PYC, listing only own PYC, pending edit/cancel, processed mutation rejection, and invalid undocumented type rejection.**

```php
public function test_employee_cannot_edit_or_cancel_processed_request(): void
{
    $employee = $this->createEmployee();
    $record = RequestModel::create([
        'created_by' => $employee->user_id, 'type' => 'general', 'payload' => ['title' => 'Equipment'],
        'status' => 'approved', 'processed_at' => now(),
    ]);

    $this->actingAs($employee->user)->get(route('employee.requests.edit', $record))->assertForbidden();
    $this->actingAs($employee->user)->delete(route('employee.requests.destroy', $record))->assertForbidden();
    $this->assertDatabaseHas('requests', ['id' => $record->id, 'status' => 'approved']);
}
```

- [ ] **Step 2: Run the request tests and verify RED for any lifecycle/visibility gaps.**

Run: `php artisan test tests/Feature/EmployeeRequestTest.php`

- [ ] **Step 3: Implement normalized payload validation, request status helpers, owner scoping, and pending-only mutation.** Keep the current documented request types only; do not add v0-only task or Admin workflows.

- [ ] **Step 4: Update request list/detail/form views to show state and actions accurately, including processor note and processed time when present.**

- [ ] **Step 5: Run focused and full tests.**

Run: `php artisan test tests/Feature/EmployeeRequestTest.php`  
Run: `php artisan test`

- [ ] **Step 6: Commit PYC lifecycle behavior.**

```text
git add app/Http/Controllers/EmployeeRequestController.php app/Models/Request.php resources/views/employee-request-form.blade.php resources/views/employee-request-show.blade.php resources/views/employee-requests.blade.php tests/Feature/EmployeeRequestTest.php
git commit -m "feat: enforce employee request lifecycle"
```

### Task 6: Complete the Employee dashboard and screen flow

**Files:**
- Modify: `app/Http/Controllers/RoleHomeController.php`
- Modify: `routes/web.php`
- Modify: `resources/views/employee-dashboard.blade.php`
- Modify: `resources/views/hrms-layout.blade.php`
- Modify: `resources/views/employee-attendance.blade.php`
- Modify: `resources/views/employee-attendance-show.blade.php`
- Modify: `resources/views/employee-tasks.blade.php`
- Modify: `resources/views/employee-task-show.blade.php`
- Modify: `resources/views/employee-profile.blade.php`
- Modify: `resources/views/employee-requests.blade.php`
- Modify: `resources/views/employee-request-form.blade.php`
- Modify: `resources/views/employee-request-show.blade.php`
- Test: `tests/Feature/EmployeeDashboardTest.php`, all Employee feature tests

**Interfaces:**
- Navigation links resolve only to the named Employee routes already covered by tests.
- All workspace pages share the Employee layout and preserve the check-in gate.

- [ ] **Step 1: Add failing assertions for navigation links, current attendance state, task/request counts, and no static placeholder values when persisted data is present.**

- [ ] **Step 2: Run the Employee feature suite and observe the RED assertions.**

Run: `php artisan test tests/Feature/EmployeeDashboardTest.php tests/Feature/EmployeeAttendanceTest.php tests/Feature/EmployeeTaskTest.php tests/Feature/EmployeeProfileTest.php tests/Feature/EmployeeRequestTest.php`

- [ ] **Step 3: Implement only the Blade/layout and route wiring needed to expose the already-tested controllers.** Do not introduce new business modules or client-side API calls.

- [ ] **Step 4: Run the Employee feature suite, full PHPUnit suite, and Vite production build.**

Run: `php artisan test tests/Feature/EmployeeDashboardTest.php tests/Feature/EmployeeAttendanceTest.php tests/Feature/EmployeeTaskTest.php tests/Feature/EmployeeProfileTest.php tests/Feature/EmployeeRequestTest.php`  
Run: `php artisan test`  
Run: `npm run build`

- [ ] **Step 5: Commit the Employee screen flow.**

```text
git add routes/web.php app/Http/Controllers/RoleHomeController.php resources/views/employee-dashboard.blade.php resources/views/hrms-layout.blade.php resources/views/employee-attendance.blade.php resources/views/employee-attendance-show.blade.php resources/views/employee-tasks.blade.php resources/views/employee-task-show.blade.php resources/views/employee-profile.blade.php resources/views/employee-requests.blade.php resources/views/employee-request-form.blade.php resources/views/employee-request-show.blade.php tests/Feature/EmployeeDashboardTest.php
git commit -m "feat: complete employee role screen flow"
```

### Task 7: Final Employee-flow review and verification

**Files:**
- Modify: only files required by a failing verification check
- Test: all `tests/Feature/*Employee*Test.php`, `tests/Feature/AuthAndRoleTest.php`, full suite

- [ ] **Step 1: Re-read the specification and create a requirement checklist from every Employee bullet.**

- [ ] **Step 2: Run route and application checks.**

Run: `php artisan route:list`  
Run: `php artisan test`

- [ ] **Step 3: Verify every Employee acceptance case through feature tests and inspect the final diff.** Check that no HR/Admin management behavior, API endpoint, or undocumented v0-only Employee workflow was introduced.

- [ ] **Step 4: Run the final asset build.**

Run: `npm run build`

- [ ] **Step 5: If all checks pass, report exact command output counts and the remaining non-goals.** If a requirement is genuinely contradictory between the DOCX files and is not resolved by the `CAP_NHAT` precedence rule, stop and ask the user before changing code.

## Self-Review Checklist

- Every Employee route has an explicit role/ownership guard.
- Every new behavior has a test that was observed failing before implementation.
- Duplicate attendance actions preserve database invariants.
- Profile-change requests do not update the employee row while pending.
- Processed PYC records are immutable to their employee owner.
- Dashboard values come from persisted records.
- The final diff contains no API or unrequested HR/Admin functionality.
