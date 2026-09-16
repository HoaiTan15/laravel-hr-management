<?php

namespace Database\Seeders;

use App\Enums\PersonnelProcessStatus;
use App\Enums\PersonnelProcessType;
use App\Enums\RequestStatus;
use App\Enums\RequestType;
use App\Enums\TaskStatus;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\PersonnelProcess;
use App\Models\Request;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $hr = User::where('email', 'hr@example.test')->firstOrFail();
        $admin = User::where('email', 'admin@example.test')->firstOrFail();
        $employeeOne = Employee::where('employee_code', 'EMP-0002')->firstOrFail();
        $employeeTwo = Employee::where('employee_code', 'EMP-0003')->firstOrFail();

        foreach ([
            [$employeeOne, '2026-09-15', '09:00:00', '17:30:00'],
            [$employeeTwo, '2026-09-15', '08:45:00', '17:15:00'],
        ] as [$employee, $date, $checkIn, $checkOut]) {
            Attendance::updateOrCreate(
                ['employee_id' => $employee->id, 'work_date' => $date],
                ['check_in_at' => "$date $checkIn", 'check_out_at' => "$date $checkOut"],
            );
        }

        Task::updateOrCreate(
            ['assigned_to' => $employeeOne->id, 'title' => 'Review onboarding checklist'],
            ['created_by' => $hr->id, 'description' => 'Review the fictional new-hire checklist.', 'status' => TaskStatus::IN_PROGRESS, 'due_at' => '2026-09-30 17:00:00'],
        );
        Task::updateOrCreate(
            ['assigned_to' => $employeeTwo->id, 'title' => 'Prepare monthly report'],
            ['created_by' => $hr->id, 'description' => 'Prepare a sample monthly operations report.', 'status' => TaskStatus::PENDING, 'due_at' => '2026-10-05 17:00:00'],
        );

        Request::updateOrCreate(
            ['created_by' => $employeeOne->user_id, 'type' => RequestType::PROFILE_CHANGE],
            ['payload' => ['phone' => '0900000099'], 'status' => RequestStatus::PENDING],
        );
        Request::updateOrCreate(
            ['created_by' => $employeeTwo->user_id, 'type' => RequestType::PROFILE_CHANGE],
            ['payload' => ['address' => 'Fictional District'], 'status' => RequestStatus::APPROVED, 'processed_by' => $admin->id, 'processed_at' => '2026-09-10 10:00:00', 'processing_note' => 'Approved demo request.'],
        );
        Request::updateOrCreate(
            ['created_by' => $employeeOne->user_id, 'type' => RequestType::PROFILE_CHANGE, 'status' => RequestStatus::REJECTED],
            ['payload' => ['phone' => '0900000088'], 'processed_by' => $admin->id, 'processed_at' => '2026-09-11 10:00:00', 'processing_note' => 'Rejected demo request.'],
        );

        PersonnelProcess::updateOrCreate(
            ['employee_id' => $employeeOne->id, 'type' => PersonnelProcessType::RECRUITMENT],
            ['created_by' => $hr->id, 'status' => PersonnelProcessStatus::COMPLETED, 'effective_date' => '2025-01-15', 'reason' => 'Demo recruitment history.', 'completed_by' => $hr->id, 'completed_at' => '2025-01-15 09:00:00'],
        );
        PersonnelProcess::updateOrCreate(
            ['employee_id' => $employeeTwo->id, 'type' => PersonnelProcessType::TERMINATION],
            ['created_by' => $hr->id, 'status' => PersonnelProcessStatus::IN_PROGRESS, 'effective_date' => '2026-12-31', 'reason' => 'Future fictional process example.'],
        );
    }
}
