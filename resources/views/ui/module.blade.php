@extends('layouts.hr')

@php
    $statusLabels = [
        'active' => 'Đang làm việc', 'terminated' => 'Đã nghỉ việc',
        'in_progress' => 'Đang xử lý', 'completed' => 'Hoàn thành',
        'rejected' => 'Từ chối', 'pending' => 'Đang chờ xử lý', 'draft' => 'Bản nháp', 'stopped' => 'Đã dừng',
    ];
    $requestTypeLabels = [
        'hardware' => 'Phần cứng', 'software' => 'Phần mềm', 'account' => 'Tài khoản',
        'other' => 'Khác', 'profile_change' => 'Thay đổi hồ sơ',
    ];
    $stats = match ($active) {
        'employees' => [
            ['label' => 'Tổng nhân viên', 'value' => $totalEmployees, 'note' => 'Hồ sơ trong hệ thống'],
            ['label' => 'Đang làm việc', 'value' => $activeEmployees, 'note' => 'Đang hoạt động'],
            ['label' => 'Phòng ban', 'value' => $departmentCount, 'note' => 'Đang hoạt động'],
            ['label' => 'Hồ sơ cần cập nhật', 'value' => $pendingProfiles, 'note' => 'Thiếu thông tin liên hệ'],
        ],
        'departments' => [
            ['label' => 'Phòng ban', 'value' => $departmentCount, 'note' => 'Trong hệ thống'],
            ['label' => 'Đang hoạt động', 'value' => $activeDepartments, 'note' => 'Đơn vị đang sử dụng'],
            ['label' => 'Nhân sự phân bổ', 'value' => $assignedEmployees, 'note' => 'Nhân viên hiện tại'],
            ['label' => 'Chưa có nhân sự', 'value' => $emptyDepartments, 'note' => 'Cần phân bổ'],
        ],
        'positions' => [
            ['label' => 'Tổng chức vụ', 'value' => $positionCount, 'note' => 'Trong danh mục'],
            ['label' => 'Đang sử dụng', 'value' => $usedPositions, 'note' => 'Có nhân sự'],
            ['label' => 'Vị trí trống', 'value' => $vacantPositions, 'note' => 'Chưa phân bổ'],
            ['label' => 'Đang hoạt động', 'value' => $activePositions, 'note' => 'Có thể sử dụng'],
        ],
        'tasks' => [
            ['label' => 'Tổng công việc', 'value' => $taskCount, 'note' => 'Trong hệ thống'],
            ['label' => 'Đang thực hiện', 'value' => $inProgressTasks, 'note' => 'Cần theo dõi'],
            ['label' => 'Hoàn thành', 'value' => $completedTasks, 'note' => 'Đã cập nhật'],
            ['label' => 'Quá hạn', 'value' => $overdueTasks, 'note' => 'Cần nhắc việc'],
        ],
        'requests' => [
            ['label' => 'PYC chờ xử lý', 'value' => $pendingRequests, 'note' => 'Cần phản hồi'],
            ['label' => 'Đã hoàn thành', 'value' => $completedRequests, 'note' => 'Đã xử lý'],
            ['label' => 'Từ chối', 'value' => $rejectedRequests, 'note' => 'Theo trạng thái'],
            ['label' => 'Thời gian xử lý', 'value' => $averageProcessing, 'note' => 'Trung bình'],
        ],
        'recruitment' => [
            ['label' => 'Đang tuyển', 'value' => $openRecruitment, 'note' => 'Quy trình mở'],
            ['label' => 'Hồ sơ tuyển dụng', 'value' => $newCandidates, 'note' => 'Trong hệ thống'],
            ['label' => 'Đang xử lý', 'value' => $interviews, 'note' => 'Cần theo dõi'],
            ['label' => 'Đã hoàn thành', 'value' => $hiredCandidates, 'note' => 'Đã tiếp nhận'],
        ],
        'termination' => [
            ['label' => 'Đang xử lý', 'value' => $processingTerminations, 'note' => 'Cần hoàn tất'],
            ['label' => 'Sắp nghỉ việc', 'value' => $upcomingTerminations, 'note' => 'Theo ngày hiệu lực'],
            ['label' => 'Đã hoàn tất', 'value' => $completedTerminations, 'note' => 'Đã cập nhật'],
            ['label' => 'Chờ bàn giao', 'value' => $waitingHandover, 'note' => 'Cần theo dõi'],
        ],
        'profile' => [
            ['label' => 'Mã nhân viên', 'value' => $employee?->employee_code ?? '—', 'note' => 'Tài khoản HR'],
            ['label' => 'Phòng ban', 'value' => $employee?->department?->name ?? '—', 'note' => 'Đơn vị công tác'],
            ['label' => 'Vai trò', 'value' => strtoupper($user->role->value), 'note' => 'Quyền quản lý'],
            ['label' => 'Trạng thái', 'value' => $user->is_active ? 'Hoạt động' : 'Đã khóa', 'note' => 'Tài khoản'],
        ],
        default => [],
    };
@endphp

@section('title', $title . ' · HUIT HRMS')
@section('content')
<div class="stitch-page">
    <div class="stitch-context-row">
        <div><div class="stitch-context">HR / Workspace / {{ $sectionTitle }}</div><h1>{{ $sectionTitle }}</h1><p class="stitch-subtitle">{{ $description }}</p></div>
        <div class="toolbar">
            @if($active === 'employees') <a class="button" href="{{ route('hr.employees.create') }}">+ Thêm nhân viên</a>
            @elseif($active === 'departments') <a class="button" href="{{ route('hr.departments.create') }}">+ Thêm phòng ban</a>
            @elseif($active === 'positions') <a class="button" href="{{ route('hr.positions.create') }}">+ Thêm chức vụ</a>
            @elseif($active === 'tasks') <a class="button" href="{{ route('hr.tasks.create') }}">+ Giao công việc</a>
            @elseif($active === 'recruitment') <a class="button" href="{{ route('hr.recruitment.create') }}">+ Tạo quy trình</a>
            @elseif($active === 'termination') <a class="button" href="{{ route('hr.termination.create') }}">+ Tạo quy trình</a>
            @endif
        </div>
    </div>

    @if($stats)
        <div class="stitch-summary-grid">@foreach($stats as $stat)<article class="stitch-summary-card"><div class="stitch-summary-head"><span>{{ $stat['label'] }}</span><div class="stitch-summary-icon primary"><span class="material-symbols-outlined">monitoring</span></div></div><div class="stitch-summary-value">{{ $stat['value'] }}</div><span class="stitch-summary-caption">{{ $stat['note'] }}</span></article>@endforeach</div>
    @endif

    @if($active === 'employees')
        <section class="stitch-panel"><form class="hr-demo-toolbar" method="GET" action="{{ route('hr.employees') }}"><input class="input" name="search" value="{{ request('search') }}" placeholder="Tìm theo mã, họ tên hoặc email..."><select class="select" name="department_id"><option value="">Tất cả phòng ban</option>@foreach($departments as $department)<option value="{{ $department->id }}" @selected((string) request('department_id') === (string) $department->id)>{{ $department->name }}</option>@endforeach</select><button class="button button-light" type="submit">Lọc</button></form><div class="stitch-table-wrap"><table class="stitch-table"><thead><tr><th>Mã NV</th><th>Họ tên</th><th>Phòng ban</th><th>Chức vụ</th><th>Trạng thái</th><th>Thao tác</th></tr></thead><tbody>@forelse($employees as $employeeRecord)<tr><td><strong>{{ $employeeRecord->employee_code }}</strong></td><td><strong>{{ $employeeRecord->full_name }}</strong><br><small>{{ $employeeRecord->email ?: $employeeRecord->user?->email }}</small></td><td>{{ $employeeRecord->department?->name ?? '—' }}</td><td>{{ $employeeRecord->position?->name ?? '—' }}</td><td><span class="stitch-status-pill {{ $employeeRecord->employment_status?->value === 'active' ? 'completed' : 'rejected' }}">{{ $statusLabels[$employeeRecord->employment_status?->value] ?? '—' }}</span></td><td><a class="button button-light" href="{{ route('hr.employees.show', $employeeRecord) }}">Chi tiết</a> <a class="button button-light" href="{{ route('hr.employees.edit', $employeeRecord) }}">Sửa</a></td></tr>@empty<tr><td colspan="6" class="stitch-empty">Chưa có nhân viên phù hợp.</td></tr>@endforelse</tbody></table></div>@if($employees->hasPages())<div class="admin-pagination">{{ $employees->links() }}</div>@endif</section>
    @elseif($active === 'departments')
        <section class="stitch-panel"><div class="stitch-table-wrap"><table class="stitch-table"><thead><tr><th>Tên phòng ban</th><th>Mô tả</th><th>Số nhân sự</th><th>Trạng thái</th><th>Thao tác</th></tr></thead><tbody>@forelse($departments as $department)<tr><td role="link" tabindex="0" style="cursor:pointer" onclick="window.location.href='{{ route('hr.departments.show', $department) }}'" onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); window.location.href='{{ route('hr.departments.show', $department) }}'; }"><strong>{{ $department->name }}</strong></td><td>{{ $department->description ?: '—' }}</td><td><strong>{{ $department->employees_count }}</strong> người</td><td>{{ $department->is_active ? 'Hoạt động' : 'Đã khóa' }}</td><td><a class="button button-light" href="{{ route('hr.departments.edit', $department) }}">Sửa</a><form style="display:inline" method="POST" action="{{ route('hr.departments.destroy', $department) }}" onsubmit="return confirm('Xóa phòng ban này?')">@csrf @method('DELETE')<button class="button danger" type="submit">Xóa</button></form></td></tr>@empty<tr><td colspan="5" class="stitch-empty">Chưa có phòng ban.</td></tr>@endforelse</tbody></table></div></section>
    @elseif($active === 'positions')
        <section class="stitch-panel"><div class="stitch-table-wrap"><table class="stitch-table"><thead><tr><th>Chức vụ</th><th>Mô tả</th><th>Nhân sự hiện tại</th><th>Trạng thái</th><th>Thao tác</th></tr></thead><tbody>@forelse($positions as $position)<tr><td role="link" tabindex="0" style="cursor:pointer" onclick="window.location.href='{{ route('hr.positions.show', $position) }}'" onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); window.location.href='{{ route('hr.positions.show', $position) }}'; }"><strong>{{ $position->name }}</strong></td><td>{{ $position->description ?: '—' }}</td><td><strong>{{ $position->employees_count }}</strong> người</td><td>{{ $position->is_active ? 'Đang sử dụng' : 'Đã khóa' }}</td><td><a class="button button-light" href="{{ route('hr.positions.edit', $position) }}">Sửa</a><form style="display:inline" method="POST" action="{{ route('hr.positions.destroy', $position) }}" onsubmit="return confirm('Xóa chức vụ này?')">@csrf @method('DELETE')<button class="button danger" type="submit">Xóa</button></form></td></tr>@empty<tr><td colspan="5" class="stitch-empty">Chưa có chức vụ.</td></tr>@endforelse</tbody></table></div></section>
    @elseif($active === 'tasks')
        <section class="stitch-panel"><div class="stitch-table-wrap"><table class="stitch-table"><thead><tr><th>Công việc</th><th>Người thực hiện</th><th>Hạn</th><th>Trạng thái</th><th>Thao tác</th></tr></thead><tbody>@forelse($tasks as $task)<tr><td role="link" tabindex="0" style="cursor:pointer" onclick="window.location.href='{{ route('hr.tasks.show', $task) }}'" onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); window.location.href='{{ route('hr.tasks.show', $task) }}'; }"><strong>{{ $task->title }}</strong><br><small>{{ $task->description ?: '—' }}</small></td><td>{{ $task->assignee?->full_name ?? '—' }}</td><td>{{ $task->due_at?->format('d/m/Y H:i') ?? '—' }}</td><td>{{ $statusLabels[$task->status?->value] ?? $task->status?->value }}</td><td><a class="button button-light" href="{{ route('hr.tasks.edit', $task) }}">Sửa</a><form style="display:inline" method="POST" action="{{ route('hr.tasks.destroy', $task) }}" onsubmit="return confirm('Xóa công việc này?')">@csrf @method('DELETE')<button class="button danger" type="submit">Xóa</button></form></td></tr>@empty<tr><td colspan="5" class="stitch-empty">Chưa có công việc.</td></tr>@endforelse</tbody></table></div></section>
    @elseif($active === 'requests')
        <section class="stitch-panel"><form class="hr-demo-toolbar" method="GET" action="{{ route('hr.requests') }}"><select class="select" name="status"><option value="">Tất cả trạng thái</option>@foreach(['pending' => 'Đang chờ xử lý', 'completed' => 'Hoàn thành', 'rejected' => 'Từ chối'] as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select><select class="select" name="type"><option value="">Tất cả loại</option>@foreach($requestTypeLabels as $value => $label)<option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>@endforeach</select><button class="button button-light" type="submit">Lọc</button></form><div class="stitch-table-wrap"><table class="stitch-table"><thead><tr><th>Mã phiếu</th><th>Người gửi</th><th>Loại</th><th>Ngày gửi</th><th>Trạng thái</th><th></th></tr></thead><tbody>@forelse($requests as $requestRecord)<tr><td>#PYC-{{ str_pad($requestRecord->id, 4, '0', STR_PAD_LEFT) }}</td><td>{{ $requestRecord->creator?->name ?? '—' }}</td><td>{{ $requestTypeLabels[$requestRecord->type?->value] ?? $requestRecord->type?->value }}</td><td>{{ $requestRecord->created_at?->format('d/m/Y') }}</td><td>{{ $statusLabels[$requestRecord->status?->value] ?? $requestRecord->status?->value }}</td><td><a class="button button-light" href="{{ route('hr.requests.show', $requestRecord) }}">Xem chi tiết</a></td></tr>@empty<tr><td colspan="6" class="stitch-empty">Chưa có phiếu yêu cầu.</td></tr>@endforelse</tbody></table></div></section>
    @elseif(in_array($active, ['recruitment', 'termination'], true))
        <section class="stitch-panel"><div class="stitch-table-wrap"><table class="stitch-table"><thead><tr><th>Nhân viên/ứng viên</th><th>Phòng ban/chức vụ</th><th>Ngày hiệu lực</th><th>Lý do</th><th>Trạng thái</th><th></th></tr></thead><tbody>@forelse($processes as $process)<tr><td>{{ $process->employee?->full_name ?? 'Chưa gắn nhân viên' }}<br><small>{{ $process->employee?->employee_code ?? '—' }}</small></td><td>{{ $process->employee?->department?->name ?? '—' }} / {{ $process->employee?->position?->name ?? '—' }}</td><td>{{ $process->effective_date?->format('d/m/Y') ?? '—' }}</td><td>{{ $process->reason ?: '—' }}</td><td>{{ $statusLabels[$process->status?->value] ?? $process->status?->value }}</td><td><a class="button button-light" href="{{ route('hr.processes.show', $process) }}">Chi tiết</a></td></tr>@empty<tr><td colspan="6" class="stitch-empty">Chưa có quy trình.</td></tr>@endforelse</tbody></table></div></section>
    @elseif($active === 'profile')
        <section class="stitch-content-grid"><div class="stitch-panel"><h2>Hồ sơ cá nhân HR</h2><form method="POST" action="{{ route('hr.profile.update') }}">@csrf @method('PATCH')<div class="form-group"><label>Họ và tên</label><input class="input" name="name" value="{{ old('name', $user->name) }}" required></div><div class="form-group"><label>Email</label><input class="input" type="email" name="email" value="{{ old('email', $user->email) }}" required></div><div class="form-group"><label>Số điện thoại</label><input class="input" name="phone" value="{{ old('phone', $employee?->phone) }}"></div><div class="form-group"><label>Địa chỉ</label><textarea class="input textarea" name="address">{{ old('address', $employee?->address) }}</textarea></div><button class="button" type="submit">Cập nhật hồ sơ</button></form></div><div class="stitch-panel"><h2>Quyền truy cập</h2><div class="stitch-key-list"><div><span>Vai trò</span><strong>HR</strong></div><div><span>Tài khoản</span><strong>{{ $user->is_active ? 'Đang hoạt động' : 'Đã khóa' }}</strong></div><div><span>Mã nhân viên</span><strong>{{ $employee?->employee_code ?? '—' }}</strong></div></div></div></section>
    @endif
</div>
@endsection
