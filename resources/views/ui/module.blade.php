@extends('layouts.hr')

@php
    $active = match (true) {
        request()->routeIs('hr.employees') => 'employees',
        request()->routeIs('hr.departments') => 'departments',
        request()->routeIs('hr.positions') => 'positions',
        request()->routeIs('hr.tasks') => 'tasks',
        request()->routeIs('hr.requests') => 'requests',
        request()->routeIs('hr.recruitment') => 'recruitment',
        request()->routeIs('hr.termination') => 'termination',
        request()->routeIs('hr.profile') => 'profile',
        request()->routeIs('hr.attendance.index', 'hr.attendances.*') => 'attendance',
        request()->routeIs('hr.home', 'hr.dashboard') => 'dashboard',
        default => $active ?? '',
    };

    $statusLabels = [
        'active' => 'Đang làm việc',
        'terminated' => 'Đã nghỉ việc',
        'in_progress' => 'Đang xử lý',
        'completed' => 'Hoàn thành',
        'rejected' => 'Từ chối',
        'pending' => 'Đang chờ xử lý',
        'draft' => 'Bản nháp',
        'stopped' => 'Đã dừng',
    ];
    $requestTypeLabels = [
        'hardware' => 'Phần cứng',
        'software' => 'Phần mềm',
        'account' => 'Tài khoản',
        'other' => 'Khác',
        'profile_change' => 'Thay đổi hồ sơ',
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
    <div class="stitch-page hr-module-page">
        <div class="stitch-context-row"><div class="stitch-context"><span>CỔNG NHÂN SỰ</span><span class="stitch-context-slash">/</span><span>{{ $title }}</span></div><span class="stitch-kicker">HR WORKSPACE</span></div>
        <div class="hr-module-heading"><div><h1>{{ $title }}</h1><p>{{ $description }}</p></div><div class="stitch-summary-icon primary"><span class="material-symbols-outlined">{{ $icon }}</span></div></div>

        <section class="stitch-summary-grid hr-module-summary">
            @foreach($stats as $stat)
                <article class="stitch-summary-card"><div class="stitch-summary-head"><span>{{ $stat['label'] }}</span><div class="stitch-summary-icon {{ $loop->index === 2 ? 'secondary' : '' }}"><span class="material-symbols-outlined">{{ $loop->index === 0 ? $icon : ($loop->index === 1 ? 'trending_up' : ($loop->index === 2 ? 'pending_actions' : 'verified')) }}</span></div></div><div class="stitch-summary-value">{{ $stat['value'] }}</div><span class="stitch-summary-caption">{{ $stat['note'] }}</span></article>
            @endforeach
        </section>

        @if($active === 'employees')
            <section class="stitch-panel hr-demo-panel">
                <div class="stitch-panel-head"><div class="stitch-panel-title"><span class="stitch-panel-icon primary"><span class="material-symbols-outlined">groups</span></span><div><h2>Danh sách nhân viên</h2><p>Dữ liệu nhân sự trực tiếp từ database</p></div></div><span class="stitch-count-pill">{{ $employees->total() }} hồ sơ</span></div>
                <form class="hr-demo-toolbar" method="GET" action="{{ route('hr.employees') }}"><input class="input" name="search" value="{{ request('search') }}" placeholder="Tìm theo mã, họ tên hoặc email..."><select class="select" name="department_id"><option value="">Tất cả phòng ban</option>@foreach($departments as $department)<option value="{{ $department->id }}" @selected((string) request('department_id') === (string) $department->id)>{{ $department->name }}</option>@endforeach</select><button class="button button-light" type="submit"><span class="material-symbols-outlined">filter_alt</span>Lọc</button></form>
                <div class="stitch-table-wrap"><table class="stitch-table"><thead><tr><th>Mã NV</th><th>Họ tên</th><th>Phòng ban</th><th>Chức vụ</th><th>Trạng thái</th></tr></thead><tbody>@forelse($employees as $employeeRecord)<tr><td><strong>{{ $employeeRecord->employee_code }}</strong></td><td><div class="hr-demo-person"><span class="avatar">{{ strtoupper(substr($employeeRecord->full_name, 0, 2)) }}</span><div><strong>{{ $employeeRecord->full_name }}</strong><small>{{ $employeeRecord->email ?: $employeeRecord->user?->email }}</small></div></div></td><td>{{ $employeeRecord->department?->name ?? '—' }}</td><td>{{ $employeeRecord->position?->name ?? '—' }}</td><td><span class="stitch-status-pill {{ $employeeRecord->employment_status?->value === 'active' ? 'completed' : 'rejected' }}">{{ $statusLabels[$employeeRecord->employment_status?->value] ?? '—' }}</span></td></tr>@empty<tr><td colspan="5" class="stitch-empty">Chưa có nhân viên phù hợp.</td></tr>@endforelse</tbody></table></div>
                @if($employees->hasPages())<div class="admin-pagination">{{ $employees->links() }}</div>@endif
            </section>
        @elseif($active === 'departments')
            <section class="stitch-panel hr-demo-panel"><div class="stitch-panel-head"><div class="stitch-panel-title"><span class="stitch-panel-icon primary"><span class="material-symbols-outlined">corporate_fare</span></span><div><h2>Danh sách phòng ban</h2><p>Cơ cấu tổ chức và nhân sự theo đơn vị</p></div></div><span class="stitch-count-pill">{{ $departments->count() }} phòng ban</span></div><div class="stitch-table-wrap"><table class="stitch-table"><thead><tr><th>Tên phòng ban</th><th>Mô tả</th><th>Số nhân sự</th><th>Trạng thái</th><th>Cập nhật</th></tr></thead><tbody>@forelse($departments as $department)<tr><td><strong>{{ $department->name }}</strong></td><td class="stitch-muted">{{ $department->description ?: '—' }}</td><td>{{ $department->employees_count }}</td><td><span class="stitch-status-pill {{ $department->is_active ? 'completed' : 'rejected' }}">{{ $department->is_active ? 'Hoạt động' : 'Đã khóa' }}</span></td><td class="stitch-muted">{{ $department->updated_at?->format('d/m/Y') }}</td></tr>@empty<tr><td colspan="5" class="stitch-empty">Chưa có phòng ban.</td></tr>@endforelse</tbody></table></div></section>
        @elseif($active === 'positions')
            <section class="stitch-panel hr-demo-panel"><div class="stitch-panel-head"><div class="stitch-panel-title"><span class="stitch-panel-icon primary"><span class="material-symbols-outlined">badge</span></span><div><h2>Danh mục chức vụ</h2><p>Quản lý các vị trí đang được sử dụng</p></div></div><span class="stitch-count-pill">{{ $positions->count() }} chức vụ</span></div><div class="stitch-table-wrap"><table class="stitch-table"><thead><tr><th>Chức vụ</th><th>Mô tả</th><th>Nhân sự hiện tại</th><th>Trạng thái</th></tr></thead><tbody>@forelse($positions as $position)<tr><td><strong>{{ $position->name }}</strong></td><td class="stitch-muted">{{ $position->description ?: '—' }}</td><td>{{ $position->employees_count }}</td><td><span class="stitch-status-pill {{ $position->is_active ? 'completed' : 'rejected' }}">{{ $position->is_active ? 'Đang sử dụng' : 'Đã khóa' }}</span></td></tr>@empty<tr><td colspan="4" class="stitch-empty">Chưa có chức vụ.</td></tr>@endforelse</tbody></table></div></section>
        @elseif($active === 'tasks')
            <section class="stitch-panel hr-demo-panel"><div class="stitch-panel-head"><div class="stitch-panel-title"><span class="stitch-panel-icon primary"><span class="material-symbols-outlined">checklist</span></span><div><h2>Công việc toàn bộ phận</h2><p>Nhiệm vụ được lưu trong database</p></div></div><span class="stitch-count-pill">{{ $taskCount }} công việc</span></div><div class="stitch-table-wrap"><table class="stitch-table"><thead><tr><th>Công việc</th><th>Người thực hiện</th><th>Hạn hoàn thành</th><th>Trạng thái</th></tr></thead><tbody>@forelse($tasks as $task)<tr><td><strong>{{ $task->title }}</strong><br><small class="stitch-muted">{{ $task->description ?: '—' }}</small></td><td>{{ $task->assignee?->full_name ?? '—' }}</td><td>{{ $task->due_at?->format('d/m/Y') ?? '—' }}</td><td><span class="stitch-status-pill {{ $task->status->value }}">{{ $statusLabels[$task->status->value] ?? $task->status->value }}</span></td></tr>@empty<tr><td colspan="4" class="stitch-empty">Chưa có công việc.</td></tr>@endforelse</tbody></table></div></section>
        @elseif($active === 'requests')
            <section class="stitch-panel hr-demo-panel"><div class="stitch-panel-head"><div class="stitch-panel-title"><span class="stitch-panel-icon primary"><span class="material-symbols-outlined">receipt_long</span></span><div><h2>Phiếu yêu cầu</h2><p>Danh sách PYC do nhân sự gửi</p></div></div><span class="stitch-count-pill">{{ $pendingRequests }} đang chờ</span></div><div class="stitch-table-wrap"><table class="stitch-table"><thead><tr><th>Mã phiếu</th><th>Người gửi</th><th>Loại yêu cầu</th><th>Ngày gửi</th><th>Trạng thái</th></tr></thead><tbody>@forelse($requests as $requestRecord)<tr><td><strong>#PYC-{{ str_pad($requestRecord->id, 4, '0', STR_PAD_LEFT) }}</strong></td><td>{{ $requestRecord->creator?->name ?? '—' }}</td><td>{{ $requestTypeLabels[$requestRecord->type->value] ?? $requestRecord->type->value }}</td><td>{{ $requestRecord->created_at?->format('d/m/Y') }}</td><td><span class="stitch-status-pill {{ $requestRecord->status->value }}">{{ $statusLabels[$requestRecord->status->value] ?? $requestRecord->status->value }}</span></td></tr>@empty<tr><td colspan="5" class="stitch-empty">Chưa có phiếu yêu cầu.</td></tr>@endforelse</tbody></table></div></section>
        @elseif($active === 'recruitment')
            <section class="stitch-panel hr-demo-panel"><div class="stitch-panel-head"><div class="stitch-panel-title"><span class="stitch-panel-icon primary"><span class="material-symbols-outlined">person_add</span></span><div><h2>Quy trình tuyển dụng</h2><p>Dữ liệu personnel process từ database</p></div></div><span class="stitch-count-pill">{{ $processes->count() }} hồ sơ</span></div><div class="stitch-table-wrap"><table class="stitch-table"><thead><tr><th>Nhân viên/Ứng viên</th><th>Vị trí</th><th>Ngày hiệu lực</th><th>Lý do</th><th>Trạng thái</th></tr></thead><tbody>@forelse($processes as $process)<tr><td><strong>{{ $process->employee?->full_name ?? '—' }}</strong><br><small class="stitch-muted">{{ $process->employee?->employee_code ?? '—' }}</small></td><td>{{ $process->employee?->position?->name ?? '—' }}</td><td>{{ $process->effective_date?->format('d/m/Y') ?? '—' }}</td><td>{{ $process->reason ?: '—' }}</td><td><span class="stitch-status-pill {{ $process->status->value }}">{{ $statusLabels[$process->status->value] ?? $process->status->value }}</span></td></tr>@empty<tr><td colspan="5" class="stitch-empty">Chưa có hồ sơ tuyển dụng.</td></tr>@endforelse</tbody></table></div></section>
        @elseif($active === 'termination')
            <section class="stitch-panel hr-demo-panel"><div class="stitch-panel-head"><div class="stitch-panel-title"><span class="stitch-panel-icon primary"><span class="material-symbols-outlined">person_remove</span></span><div><h2>Quy trình thôi việc</h2><p>Hồ sơ bàn giao và nghỉ việc từ database</p></div></div><span class="stitch-count-pill">{{ $processes->count() }} hồ sơ</span></div><div class="stitch-table-wrap"><table class="stitch-table"><thead><tr><th>Nhân viên</th><th>Phòng ban</th><th>Ngày hiệu lực</th><th>Lý do</th><th>Tiến độ</th></tr></thead><tbody>@forelse($processes as $process)<tr><td><strong>{{ $process->employee?->full_name ?? '—' }}</strong><br><small class="stitch-muted">{{ $process->employee?->employee_code ?? '—' }}</small></td><td>{{ $process->employee?->department?->name ?? '—' }}</td><td>{{ $process->effective_date?->format('d/m/Y') ?? '—' }}</td><td>{{ $process->reason ?: '—' }}</td><td><span class="stitch-status-pill {{ $process->status->value }}">{{ $statusLabels[$process->status->value] ?? $process->status->value }}</span></td></tr>@empty<tr><td colspan="5" class="stitch-empty">Chưa có hồ sơ thôi việc.</td></tr>@endforelse</tbody></table></div></section>
        @elseif($active === 'profile')
            <section class="stitch-content-grid hr-profile-grid"><div class="stitch-panel"><div class="stitch-profile-head"><h3>Hồ sơ cá nhân</h3><span>{{ $employee?->employee_code ?? 'HR' }}</span></div><div class="stitch-profile-card"><div class="stitch-profile-avatar">{{ strtoupper(substr($user->name, 0, 2)) }}</div><div><strong>{{ $user->name }}</strong><span>{{ $employee?->position?->name ?? 'HR' }} · {{ $employee?->department?->name ?? '—' }}</span><small>{{ $user->email }}</small></div></div><div class="stitch-profile-rows"><div><span>Họ và tên:</span><strong>{{ $user->name }}</strong></div><div><span>Email:</span><strong>{{ $user->email }}</strong></div><div><span>Số điện thoại:</span><strong>{{ $employee?->phone ?? '—' }}</strong></div><div><span>Ngày vào làm:</span><strong>{{ $employee?->hire_date?->format('d/m/Y') ?? '—' }}</strong></div></div></div><div class="stitch-panel"><div class="stitch-panel-head"><div class="stitch-panel-title"><span class="stitch-panel-icon primary"><span class="material-symbols-outlined">verified_user</span></span><div><h2>Quyền truy cập</h2><p>Thông tin tài khoản hệ thống</p></div></div></div><div class="stitch-key-list"><div><span>Vai trò</span><span class="stitch-status-pill in_progress">HR</span></div><div><span>Tài khoản</span><strong>{{ $user->is_active ? 'Đang hoạt động' : 'Đã khóa' }}</strong></div><div><span>Email xác thực</span><strong>{{ $user->email_verified_at ? 'Đã xác thực' : 'Chưa xác thực' }}</strong></div><div><span>Phạm vi</span><strong>Quản lý nhân sự</strong></div></div></div></section>
        @endif
    </div>
@endsection
