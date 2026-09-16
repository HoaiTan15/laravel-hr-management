# Database Design

This is the requirements-level database design for `hr_management`. It is a design contract for the implementation phases, not an executed migration set. Field names marked **confirm** must be agreed before migrations are written.

## General rules

- Use Laravel migrations and Eloquent; do not depend on manually created tables.
- Use an unsigned big integer primary key unless a later requirement justifies another key.
- Add `created_at` and `updated_at` to business tables unless the table is explicitly immutable.
- Use foreign keys with deliberate `cascade`, `restrict`, or `nullOnDelete` behavior. Document any exception in the migration.
- Do not physically delete an employee when termination history must be retained. Prefer an employment status and termination fields; use soft deletes only if the final workflow requires them.
- All dates/times use the application's configured timezone and are stored consistently.

## `users`

Purpose: authentication accounts and role assignment.

| Field | Purpose |
|---|---|
| `id` | Primary key |
| `name` | Display name |
| `email` | Login/contact identity; unique if email login is used |
| `username` | Optional login identity; unique if username login is used (**confirm one login identity**) |
| `password` | Laravel-hashed password |
| `role` | `admin`, `hr`, or `employee` |
| `is_active` | Account access status |
| `remember_token` | Laravel session remember token |
| timestamps | Creation/update audit timestamps |

Do not require both email and username as login identities without deciding the login contract. The role must be validated against the supported enum values.

## `departments`

Purpose: organizational departments.

| Field | Purpose |
|---|---|
| `id` | Primary key |
| `name` | Department name; uniqueness policy **confirm** |
| `description` | Optional description if needed by the UI |
| `is_active` | Whether the department can receive new assignments |
| timestamps | Creation/update timestamps |

## `positions`

Purpose: job titles/positions.

| Field | Purpose |
|---|---|
| `id` | Primary key |
| `name` | Position name; uniqueness policy **confirm** |
| `description` | Optional description if needed |
| `is_active` | Whether the position can receive new assignments |
| timestamps | Creation/update timestamps |

## `employees`

Purpose: employee profile and employment state, linked to an account and organization.

| Field | Purpose |
|---|---|
| `id` | Primary key |
| `user_id` | Foreign key to `users`; one-to-one if every employee has an account |
| `department_id` | Foreign key to `departments` |
| `position_id` | Foreign key to `positions` |
| `employee_code` | Human-readable identifier; **UNIQUE** |
| `full_name` | Employee name |
| `date_of_birth` | Date of birth |
| `gender` | Supported values **confirm** |
| `email` | Employee contact email; uniqueness policy **confirm** |
| `phone` | Phone number |
| `address` | Address |
| `hire_date` | Employment start date |
| `employment_status` | At least active/terminated; final values **confirm** |
| `termination_date` | Nullable end date |
| `termination_reason` | Nullable retained history |
| timestamps | Creation/update timestamps |

Recommended relationships: `Employee belongsTo User`, `Department`, and `Position`; each parent `hasMany` employees. Decide whether profile email duplicates `users.email` before migration.

## `attendances`

Purpose: daily check-in/check-out records.

| Field | Purpose |
|---|---|
| `id` | Primary key |
| `employee_id` | Foreign key to `employees` |
| `work_date` | Local working date |
| `check_in_at` | First check-in timestamp |
| `check_out_at` | Nullable check-out timestamp |
| `adjustment_reason` | Required when an authorized HR adjustment changes attendance |
| `adjusted_by` | Nullable foreign key to the user who adjusted it |
| timestamps | Creation/update timestamps |

Add a composite **UNIQUE(`employee_id`, `work_date`)** constraint. Application validation must reject a second check-in and a check-out before check-in; the database unique constraint remains the final duplicate guard. Admin must not receive attendance-management permission.

## `tasks`

Purpose: work items assigned to HR or Employees.

| Field | Purpose |
|---|---|
| `id` | Primary key |
| `assigned_to` | Foreign key to the responsible employee/user (**confirm owner model**) |
| `created_by` | Foreign key to creator where needed |
| `title` | Task title |
| `description` | Task details |
| `status` | At least pending/in-progress/completed; final values **confirm** |
| `due_at` | Optional due date/time |
| `notes` | Optional notes |
| timestamps | Creation/update timestamps |

The owner relationship must be chosen before migration: employee-based ownership is recommended for HR domain consistency.

## `requests` / PYC

Purpose: employee/HR requests requiring workflow review.

| Field | Purpose |
|---|---|
| `id` | Primary key |
| `created_by` | Foreign key to requesting user/employee (**confirm canonical owner**) |
| `type` | Request category, such as profile change (**confirm enum/catalog**) |
| `payload` | Requested changes; JSON structure requires validation and documented schema |
| `status` | `pending`, `approved`, `rejected`, and optionally `completed` |
| `processed_by` | Nullable foreign key to handling user |
| `processed_at` | Nullable processing timestamp |
| `processing_note` | Reason/note for approval or rejection |
| timestamps | Creation/update timestamps |

A pending request must not mutate the original profile. Processing must be idempotent: an already processed request cannot be approved or rejected again. Admin is the designated handler according to the current requirements.

## `personnel_processes`

Purpose: HR recruitment and termination processes while retaining a personnel history.

| Field | Purpose |
|---|---|
| `id` | Primary key |
| `employee_id` | Nullable/required foreign key depending on recruitment stage (**confirm**) |
| `created_by` | Foreign key to HR user |
| `type` | Recruitment or termination |
| `status` | Draft/in-progress/completed/rejected values **confirm** |
| `effective_date` | Date the process takes effect |
| `reason` | Business reason/notes |
| `completed_by` | Nullable HR user |
| `completed_at` | Nullable completion timestamp |
| timestamps | Creation/update timestamps |

Completion must update employment state through a transaction and preserve historical records. Do not physically delete a terminated employee. If recruitment creates an account, the account creation and process completion must have a defined transaction boundary.

## Relationship summary

- User `hasOne` Employee; Employee `belongsTo` User.
- Department `hasMany` Employees; Employee `belongsTo` Department.
- Position `hasMany` Employees; Employee `belongsTo` Position.
- Employee `hasMany` Attendances, Tasks (if employee-owned), Requests, and PersonnelProcesses.
- Attendance `belongsTo` Employee and optionally the adjusting User.
- Request `belongsTo` creator and optional processor User.
- PersonnelProcess `belongsTo` Employee (when applicable), creator, and completer.

## Implemented Phase 2 decisions

The Phase 2 database implementation makes the following smallest decisions where the requirements used **confirm**:

1. Email is the canonical login identity; no username column is required. Employee contact email is optional and is not required to duplicate `users.email`.
2. Roles are `admin`, `hr`, and `employee`. Status values are represented as application-backed enums stored in portable string columns: employee (`active`, `terminated`), task (`pending`, `in_progress`, `completed`), request (`pending`, `approved`, `rejected`, `completed`), and personnel process (`draft`, `in_progress`, `completed`, `rejected`). Request type is currently `profile_change`; personnel-process types are `recruitment` and `termination`.
3. Tasks are employee-owned through `assigned_to`; creators and request/process handlers are user-owned through their respective foreign keys.
4. `personnel_processes.employee_id` is nullable to support recruitment records before an employee exists. Employee rows are never physically deleted by this database design; termination is retained through employment status, date, reason, and process history.
5. Foreign keys use restrict-on-delete for employee history and required ownership, and null-on-delete for optional handler references. `employee_code` is unique and `attendances` enforces unique `(employee_id, work_date)` at the database level.

PYC payload contents remain a JSON contract validated by the future request workflow; this phase stores the requested data and processing metadata without implementing approval/rejection behavior.
