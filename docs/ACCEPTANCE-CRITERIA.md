# Acceptance Criteria

Use these as Given/When/Then checks during implementation. They are a checklist, not implemented tests yet.

- [ ] **TC01 — Mandatory check-in:** Given an active Employee has not checked in today, when they request a workspace feature, then access is redirected to the check-in screen and no workspace action runs.
- [ ] **TC02 — First check-in:** Given an active Employee has no attendance today, when they submit a valid check-in, then exactly one attendance record is created for today's date.
- [ ] **TC03 — Duplicate check-in:** Given an Employee already has today's attendance, when they submit check-in again, then the request is rejected and no second record is created.
- [ ] **TC04 — HR boundary:** Given an Employee is authenticated, when they request `/hr/employees`, then the backend returns 403 or the documented denial and performs no HR action.
- [ ] **TC05 — HR listing:** Given an authorized HR user has employees matching different names/departments/pages, when they search, filter, and paginate, then results are correct and active filters persist across page links.
- [ ] **TC06 — Attendance adjustment reason:** Given HR edits a checkout, when no adjustment reason is supplied, then validation fails and the original attendance remains unchanged.
- [ ] **TC07 — Profile PYC:** Given an Employee submits an allowed profile change, when the request is valid, then a pending PYC is created and the original profile remains unchanged.
- [ ] **TC08 — Approve PYC:** Given Admin views a pending PYC, when Admin approves it, then the permitted profile fields update and the PYC records approved status, handler, and processing time.
- [ ] **TC09 — Reject PYC:** Given Admin views a pending PYC, when Admin rejects it with a reason, then the PYC records rejected status/handler/time and the original profile remains unchanged.
- [ ] **TC10 — PYC idempotency:** Given a PYC is already approved, rejected, or completed, when anyone attempts to process it again, then the request is rejected and no data is overwritten.
- [ ] **TC11 — Recruitment:** Given an authorized HR user submits a valid recruitment process, when HR completes it, then the appropriate employee/account process is created or activated according to the approved workflow and the process history remains.
- [ ] **TC12 — Termination:** Given an authorized HR user completes a valid termination process, when it takes effect, then the employee status/account access changes as specified while employee, attendance, and process history remain.
- [ ] **TC13 — Admin attendance denial:** Given Admin is authenticated, when Admin requests HR attendance management, then the backend denies access.
- [ ] **TC14 — Admin check-in exemption:** Given Admin is authenticated and has no attendance record, when Admin requests the Admin dashboard, then access is granted without a check-in requirement.
- [ ] **TC15 — Login:** Given valid credentials for an active user, when they submit login, then the session authenticates and redirects to the correct role area.
- [ ] **TC16 — Inactive account:** Given an inactive account, when it attempts login or a protected request, then access is denied without exposing sensitive account details.
- [ ] **TC17 — Check-out order:** Given an Employee has no check-in or has already checked out, when they submit checkout, then validation rejects the request without creating an invalid timestamp.
- [ ] **TC18 — CSRF and validation:** Given a state-changing form lacks a valid CSRF token or has invalid fields, when submitted, then Laravel rejects it and no mutation occurs.
