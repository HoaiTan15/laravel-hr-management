@extends('layouts.employee')

@section('content')
<div class="stitch-dashboard">
    <div class="stitch-page">
        <div class="stitch-context-row">
            <div class="stitch-context">
                <span>HỆ THỐNG NHÂN SỰ</span>
                <span class="stitch-context-slash">/</span>
                <span>Không gian làm việc cá nhân</span>
            </div>
            <div class="stitch-date">
                <span></span> Hôm nay: {{ $weekdayLabels[now()->dayOfWeek] }}, {{ now()->format('d/m/Y') }}
            </div>
        </div>

        @if($pendingProfileChange)
            <div class="notice">PYC thay đổi hồ sơ đang chờ xử lý. Hồ sơ gốc chưa thay đổi.</div>
        @endif

        <section class="stitch-summary-grid">
            <article class="stitch-summary-card">
                <div class="stitch-summary-head"><span>Trạng thái hôm nay</span><div class="stitch-summary-icon primary"><span class="material-symbols-outlined">badge</span></div></div>
                <div class="stitch-summary-value stitch-status-value">
                    @if($attendance?->check_out_at) Đã Check-out
                    @elseif($attendance?->check_in_at) Đang làm việc
                    @else Chưa Check-in
                    @endif
                </div>
                <span class="stitch-summary-caption">Theo dõi ca làm việc hôm nay</span>
            </article>
            <article class="stitch-summary-card">
                <div class="stitch-summary-head"><span>Giờ Check-in</span><div class="stitch-summary-icon primary"><span class="material-symbols-outlined">login</span></div></div>
                <div class="stitch-summary-value">{{ $attendance?->check_in_at?->format('H:i') ?? '—' }} <small>{{ $attendance?->check_in_at ? 'Hôm nay' : 'Chưa ghi nhận' }}</small></div>
                <span class="stitch-summary-caption"><span class="material-symbols-outlined">verified</span> Dữ liệu chấm công cá nhân</span>
            </article>
            <article class="stitch-summary-card">
                <div class="stitch-summary-head"><span>Công việc hôm nay</span><div class="stitch-summary-icon secondary"><span class="material-symbols-outlined">assignment_turned_in</span></div></div>
                <div class="stitch-summary-value">{{ $taskCount }} <small>công việc</small></div>
                <span class="stitch-summary-tag">{{ $tasksDueToday }} việc đến hạn hôm nay</span>
            </article>
            <article class="stitch-summary-card">
                <div class="stitch-summary-head"><span>Phiếu đang xử lý</span><div class="stitch-summary-icon primary"><span class="material-symbols-outlined">pending_actions</span></div></div>
                <div class="stitch-summary-value">{{ $pendingRequestCount }} <small>phiếu</small></div>
                <span class="stitch-summary-caption">PYC của riêng bạn</span>
            </article>
        </section>

        <div class="stitch-content-grid">
            <div class="stitch-main-column">
                <section class="stitch-panel">
                    <div class="stitch-panel-head">
                        <div class="stitch-panel-title"><div class="stitch-panel-icon primary"><span class="material-symbols-outlined">checklist</span></div><div><h2>Công việc hôm nay</h2><p>Nhiệm vụ chuyên môn được giao gần đây</p></div></div>
                        <span class="stitch-count-pill">{{ $tasks->count() }} mục</span>
                    </div>
                    <div class="stitch-table-wrap"><table class="stitch-table"><thead><tr><th>Tên công việc</th><th>Hạn hoàn thành</th><th>Trạng thái</th></tr></thead><tbody>
                        @forelse($tasks as $task)
                            <tr><td><a class="stitch-task-link" href="{{ route('employee.tasks.show', $task) }}"><span class="stitch-task-dot {{ $task->status->value }}"></span>{{ $task->title }}</a></td><td class="stitch-muted">{{ $task->due_at?->format('d/m/Y') ?? 'Không có hạn' }}</td><td><span class="stitch-status-pill {{ $task->status->value }}">{{ ['in_progress' => 'Đang thực hiện', 'completed' => 'Hoàn thành', 'stopped' => 'Dừng thực hiện'][$task->status->value] ?? $task->status->value }}</span></td></tr>
                        @empty
                            <tr><td colspan="3" class="stitch-empty">Bạn chưa được giao công việc nào.</td></tr>
                        @endforelse
                    </tbody></table></div>
                    <div class="stitch-panel-foot"><span>Hiển thị {{ $tasks->count() }} nhiệm vụ gần nhất</span><a href="{{ route('employee.tasks.index') }}">Xem tất cả công việc <span class="material-symbols-outlined">arrow_forward</span></a></div>
                </section>

                <section class="stitch-panel">
                    <div class="stitch-panel-head"><div class="stitch-panel-title"><div class="stitch-panel-icon neutral"><span class="material-symbols-outlined">receipt_long</span></div><div><h2>Phiếu yêu cầu gần đây</h2><p>Lịch sử các PYC do bạn gửi</p></div></div><span class="stitch-count-label">{{ $requests->count() }} phiếu mới nhất</span></div>
                    <div class="stitch-table-wrap"><table class="stitch-table request-table"><thead><tr><th>Mã phiếu</th><th>Loại yêu cầu</th><th>Ngày gửi</th><th>Trạng thái</th></tr></thead><tbody>
                        @forelse($requests as $record)
                            <tr><td><a class="stitch-request-code" href="{{ route('employee.requests.show', $record) }}">#PYC-{{ str_pad($record->id, 4, '0', STR_PAD_LEFT) }}</a></td><td>{{ $record->payload['title'] ?? ($requestTypeLabels[$record->type->value] ?? $record->type->value) }}</td><td class="stitch-muted">{{ $record->created_at?->format('d/m/Y') }}</td><td><span class="stitch-status-pill {{ $record->status->value }}">{{ ['pending' => 'Đang xử lý', 'completed' => 'Hoàn thành', 'rejected' => 'Từ chối'][$record->status->value] ?? $record->status->value }}</span></td></tr>
                        @empty
                            <tr><td colspan="4" class="stitch-empty">Bạn chưa tạo phiếu yêu cầu nào.</td></tr>
                        @endforelse
                    </tbody></table></div>
                    <div class="stitch-panel-foot"><span>Chỉ hiển thị PYC của tài khoản này</span><a href="{{ route('employee.requests.index') }}">Xem tất cả phiếu yêu cầu <span class="material-symbols-outlined">arrow_forward</span></a></div>
                </section>
            </div>

            <div class="stitch-side-column">
                <section class="stitch-panel stitch-attendance-panel"><div class="stitch-side-head"><div class="stitch-panel-icon primary"><span class="material-symbols-outlined">badge</span></div><div><h3>Trạng thái làm việc hôm nay</h3><span>Bản ghi theo dõi ca trực</span></div></div><div class="stitch-key-list"><div><span><span class="material-symbols-outlined">calendar_today</span>Ngày làm việc:</span><strong>{{ now()->format('d/m/Y') }}</strong></div><div><span><span class="material-symbols-outlined green-text">login</span>Check-in:</span><strong class="stitch-time-value">{{ $attendance?->check_in_at?->format('H:i') ?? 'Chưa Check-in' }}</strong></div><div><span><span class="material-symbols-outlined">logout</span>Check-out:</span><em>{{ $attendance?->check_out_at?->format('H:i') ?? 'Chưa Check-out' }}</em></div><div><span><span class="material-symbols-outlined secondary-text">timelapse</span>Thời gian làm việc:</span><strong class="secondary-text">{{ $attendance?->check_in_at && !$attendance?->check_out_at ? 'Đang tính' : ($attendance?->check_out_at ? 'Đã hoàn tất' : 'Chưa bắt đầu') }}</strong></div><div><span><span class="material-symbols-outlined green-text">verified_user</span>Trạng thái:</span><span class="stitch-status-pill {{ $attendance?->check_out_at ? 'completed' : ($attendance?->check_in_at ? 'in_progress' : 'pending') }}">{{ $attendance?->check_out_at ? 'Đã Check-out' : ($attendance?->check_in_at ? 'Đang làm việc' : 'Chưa Check-in') }}</span></div></div></section>@if($attendance?->check_in_at && !$attendance?->check_out_at)<form method="POST" action="{{ route('attendance.check-out') }}" class="stitch-checkout-form stitch-checkout-outside">@csrf<button class="button full stitch-checkout-panel-button" type="submit"><span class="material-symbols-outlined">logout</span>Check-out</button></form>@elseif($attendance?->check_out_at)<button class="button full stitch-checkout-panel-button is-complete stitch-checkout-outside" type="button" disabled><span class="material-symbols-outlined">check_circle</span>Đã Check-out</button>@endif

                <section class="stitch-panel stitch-profile-panel"><div class="stitch-profile-head"><h3>Hồ sơ cá nhân</h3><span>{{ $employee->employee_code ?? '—' }}</span></div><div class="stitch-profile-card"><div class="stitch-profile-avatar">{{ strtoupper(substr($employee->full_name ?? 'NV', 0, 2)) }}</div><div><strong>{{ $employee->full_name ?? 'Nhân viên' }}</strong><span>{{ $employee->department->name ?? 'Chưa cập nhật phòng ban' }}</span><small>{{ $employee->email ?? 'Chưa cập nhật email' }}</small></div></div><div class="stitch-profile-rows"><div><span>Mã nhân viên:</span><strong>{{ $employee->employee_code ?? '—' }}</strong></div><div><span>Phòng ban:</span><strong>{{ $employee->department->name ?? '—' }}</strong></div><div><span>Chức vụ:</span><strong>{{ $employee->position->name ?? '—' }}</strong></div></div><a class="stitch-profile-link" href="{{ route('employee.profile') }}"><span class="material-symbols-outlined">badge</span>Xem hồ sơ</a></section>
            </div>
        </div>

        @if(($show ?? null) === 'checkin')
            <div class="modal-backdrop"><div class="modal"><div class="modal-icon"><span class="material-symbols-outlined">how_to_reg</span></div><span class="badge">HỆ THỐNG CHẤM CÔNG HUIT</span><h2>Chấm công đầu ngày</h2><p>Xin chào, {{ auth()->user()->name }}<br>Bạn chưa Check-in hôm nay. Vui lòng Check-in trước khi bắt đầu làm việc.</p><div class="modal-info"><div class="row"><span class="row-label">Mã nhân viên</span><strong>{{ $employee->employee_code ?? '—' }}</strong></div><div class="row"><span class="row-label">Chức vụ</span><strong>{{ $employee->position->name ?? '—' }}</strong></div><div class="row"><span class="row-label">Phòng ban</span><strong>{{ $employee->department->name ?? '—' }}</strong></div><div class="row"><span class="row-label">Giờ hiện tại</span><strong>{{ now()->format('H:i') }}</strong></div></div><form method="POST" action="{{ route('attendance.check-in.store') }}">@csrf<button class="button full" type="submit"><span class="material-symbols-outlined">login</span>Check-in ngay</button></form><form method="POST" action="{{ route('logout') }}" style="margin-top:12px">@csrf<button class="stitch-modal-logout" type="submit"><span class="material-symbols-outlined">logout</span>Đăng xuất</button></form></div></div>
        @elseif(($show ?? null) === 'checkin-success')
            <div class="modal-backdrop"><div class="modal success"><div class="modal-icon"><span class="material-symbols-outlined">check</span></div><h2>Check-in thành công</h2><p>Bạn đã Check-in lúc {{ now()->format('H:i') }}.</p><a class="button full" href="{{ route('employee.home') }}">Vào Dashboard</a></div></div>
        @elseif(($show ?? null) === 'checkout-success')
            <div class="modal-backdrop"><div class="modal success"><div class="modal-icon"><span class="material-symbols-outlined">check</span></div><h2>Check-out thành công</h2><p>Ca làm việc hôm nay đã được kết thúc. Bạn có thể đăng xuất.</p><form method="POST" action="{{ route('logout') }}">@csrf<button class="button full" type="submit"><span class="material-symbols-outlined">logout</span>Đăng xuất</button></form><a class="button light full" style="margin-top:12px" href="{{ route('employee.home') }}">Về Dashboard</a></div></div>
        @endif
    </div>
</div>
@endsection