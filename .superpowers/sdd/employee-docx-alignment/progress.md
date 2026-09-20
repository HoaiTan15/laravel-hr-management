# SDD ledger — plan: docs/superpowers/plans/2026-09-19-employee-docx-alignment.md

Execution mode: native inline.

Ruling: no automatic Git commit or push — explicit user instruction overrides the plan's original commit steps; all implementation and documentation changes remain in the working tree.

Pre-flight: Task 1 dashboard consumes the employee fixture and route contract; Task 2 attendance state is consumed by the dashboard and workspace gate; Tasks 3–5 provide the persisted task/profile/PYC data consumed by Task 6 views; Task 7 consumes all prior routes, controllers, views, and tests.

Ruling: CAP_NHAT defines employee PYC concepts as support and profile-change requests but does not prescribe PHP enum spellings — use `support` and `profile_change` as direct slugs for those named concepts; reject current undocumented values `general`, `leave`, `equipment`, and `attendance_adjustment`.

Ruling: CAP_NHAT assigns attendance adjustment to HR, so employee screens will not expose an attendance-adjustment PYC route or action; the employee can only view own attendance and submit the documented support/profile-change PYC types.

Ruling: CAP_NHAT's Employee screen list includes Dashboard, Công việc, Phiếu yêu cầu, Hồ sơ cá nhân, and the mandatory check-in flow, while attendance history/detail is listed under HR management — removed the previously added Employee attendance list/detail route, controller, views, and model-only helpers; cost if wrong: the earlier requested employee history screen would need to be restored, but it would violate the newer source-of-truth document.

Task 1: complete — `EmployeeDashboardTest` 1/1 and full suite 14/14 after replacing static dashboard values with employee-scoped persisted attendance/tasks/requests.

Task 2: complete — `EmployeeAttendanceTest` 4/4, attendance/auth focused suite 15/15, full suite 18/18. Duplicate check-in/check-out rejection, timestamp preservation, ownership guard, and status/duration helpers verified RED→GREEN.

Task 3: complete — `EmployeeTaskTest` 3/3 and full suite 21/21. Owner-scoped search/status/date query, pagination, status/notes persistence, and cross-owner update rejection verified RED→GREEN.

Task 4: complete — `EmployeeProfileTest` 4/4 and full suite 25/25. Editable contact/profile fields are submitted as a pending `profile_change` PYC, immutable fields are ignored/read-only, original data remains unchanged, duplicate pending requests are rejected, and the pending state is displayed.

Task 5: complete — `EmployeeRequestTest` 6/6 and full suite 30/30. Only the support endpoint and profile-change screen create the two CAP_NHAT PYC types; own-request visibility, pending edit/cancel, processed immutability, undocumented-type rejection, profile-flow bypass rejection, and employee denial of Admin processing are verified RED→GREEN.

Task 6: complete — route list contains only CAP_NHAT Employee screens plus shared attendance entry; Blade cache, PHP lint, Vite production build, and full suite 30/30 pass.

Final review: self-review (no subagent tool used). Reviewed route boundary, request enum/input values, owner scoping, check-in gate, pending PYC lifecycle, static dashboard removal, and deleted CAP_NHAT-out-of-scope Employee attendance history screens.

Final: fixed profile-change PYC bypass — `test_profile_change_type_cannot_bypass_profile_change_form` RED→GREEN; full suite 30/30.

Final verification: `php artisan test --compact` → 30 tests, 30 passed, 102 assertions; `php artisan route:list` → 34 routes; `php artisan view:cache` → success; `git diff --check` → clean; stale-reference scan → none; PHP lint → no syntax errors; `npm run build` → success with optional `fontaine` warning only.

Follow-up: fixed authenticated entry at `/` — `HomeController` now routes each authenticated role to its landing route, so Employee reaches the existing `employee.checked.in` gate; added regression coverage. Focused test passed, full suite now 31/31 with 106 assertions.

Source-of-truth correction: user explicitly selected `Hệ thống HRMs_v0.docx` as the business standard. Employee check-in is therefore a mandatory dashboard overlay, with non-dashboard workspace routes still gated; support PYC values are `hardware`, `software`, `account`, and `other`, while profile change remains the separate documented profile workflow. Focused check-in/dashboard suite passed 15/15; PYC suite passed 7/7.
