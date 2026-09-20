# Business Flows

These flows describe expected behavior only. They are not implementation code. Exact fields and statuses must follow [DATABASE.md](DATABASE.md).

## 1. Login

1. Visitor submits credentials.
2. Laravel validates the request.
3. Valid credentials authenticate the user and regenerate the session.
4. Inactive accounts are denied.
5. The user is redirected to the role dashboard or the intended URL.
6. Invalid credentials return a safe error without revealing which credential failed.

## 2. Role authorization

1. An authenticated user requests a route.
2. Authentication, active-account, and role middleware run.
3. If the role is allowed, the request continues.
4. Otherwise, no controller action runs and the user receives a 403/documented denial.

## 3. Employee mandatory check-in

1. An HR/Employee user requests a workspace route requiring attendance.
2. The system checks today's attendance.
3. Without a check-in, the user is redirected to the check-in screen.
4. The check-in screen and logout remain accessible.
5. Admin is exempt from this check.
6. The workday starts at 08:00; check-in after 08:30 is classified as late.
7. When an HR/Employee user logs out with an open attendance, the system requires checkout confirmation before ending the session.

## 4. First check-in

1. An active HR/Employee submits check-in.
2. The server validates identity and current work date.
3. The system creates one attendance row with `employee_id`, `work_date`, and `check_in_at`.
4. The user is redirected to their workspace with success feedback.

## 5. Duplicate same-day check-in

1. A user submits check-in again on a date with an existing attendance row.
2. Application validation and the database composite unique constraint reject it.
3. No second row is created.
4. The user receives a clear error.

## 6. Check-out

1. A checked-in HR/Employee submits check-out.
2. The server finds today's attendance and verifies check-in exists.
3. It rejects a missing record, duplicate checkout, or checkout before check-in.
4. A valid checkout stores `check_out_at`.
5. Any HR correction requires an authorization check and a reason.

## 7. Employee profile change creates PYC

1. Employee submits an allowed profile change request.
2. The server validates the proposed values and creates a pending PYC containing the requested data.
3. The original profile remains unchanged.
4. The employee can see the pending status.

## 8. Admin approves PYC

1. Admin opens a pending PYC.
2. Admin reviews the request and confirms approval.
3. The system verifies it is still pending.
4. In one transaction, the approved profile changes are applied, the PYC is marked approved/completed, and handler/time/note are stored.
5. The employee sees the updated profile.

## 9. Admin rejects PYC

1. Admin opens a pending PYC and supplies a processing reason.
2. The system verifies it is still pending.
3. The PYC becomes rejected and records handler/time/reason.
4. The original profile remains unchanged.

## 10. Processed PYC cannot be processed again

1. A user submits an action for a non-pending PYC.
2. The server rejects the action before mutation.
3. No profile or processing metadata is overwritten.

## 11. HR recruitment

1. HR creates and validates a recruitment personnel process.
2. The process progresses through its documented statuses.
3. On completion, the system creates/activates the appropriate employee/account records in a transaction, if that workflow is confirmed.
4. The process remains as historical evidence.

## 12. HR termination

1. HR submits a termination process with effective date and reason.
2. The system validates authorization and process state.
3. On completion, the employee becomes terminated/inactive; the employee row and historical attendance/process data remain.
4. The account/workspace access is disabled according to the agreed policy.

## 13. Employee tasks

1. An authorized creator assigns a task to an HR/Employee owner.
2. The owner sees only permitted tasks and updates allowed status/notes.
3. Server-side authorization prevents editing another user's task without permission.

## 14. Employee requests

1. Employee submits a validated request/PYC.
2. The request starts as pending.
3. The employee can view its status and processing note.
4. Only the designated handler can process it, and processing is one-time.
