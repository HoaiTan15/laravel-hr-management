<?php

namespace App\Services;

use App\Enums\RequestStatus;
use App\Enums\RequestType;
use App\Models\Employee;
use App\Models\Request as EmployeeRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class RequestProcessingService
{
    public function process(EmployeeRequest $requestModel, User $processor, string $status, string $note): void
    {
        DB::transaction(function () use ($requestModel, $processor, $status, $note): void {
            $lockedRequest = EmployeeRequest::whereKey($requestModel->id)->lockForUpdate()->firstOrFail();
            abort_unless($lockedRequest->status === RequestStatus::PENDING, 409);

            if ($status === RequestStatus::COMPLETED->value && $lockedRequest->type === RequestType::PROFILE_CHANGE) {
                $data = Validator::make($lockedRequest->payload ?? [], [
                    'changes' => ['required', 'array:date_of_birth,gender,cccd', 'min:1'],
                    'changes.date_of_birth' => ['sometimes', 'required', 'date'],
                    'changes.gender' => ['sometimes', 'required', 'string', 'max:20'],
                    'changes.cccd' => ['sometimes', 'required', 'string', 'max:20'],
                ])->validate();

                $employee = Employee::where('user_id', $lockedRequest->created_by)->lockForUpdate()->first();
                if (! $employee) {
                    throw ValidationException::withMessages(['status' => 'Không tìm thấy hồ sơ nhân viên của người gửi phiếu.']);
                }

                $employee->update($data['changes']);
            }

            $lockedRequest->update([
                'status' => $status,
                'processed_by' => $processor->id,
                'processed_at' => now(),
                'processing_note' => $note,
            ]);
        });
    }
}
