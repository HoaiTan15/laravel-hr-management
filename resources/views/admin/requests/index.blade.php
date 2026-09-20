@extends('layouts.admin')

@section('content')
    <div class="stitch-page admin-page">
        <div class="stitch-context-row">
            <div class="stitch-context"><span class="stitch-kicker">Admin</span><span class="stitch-context-slash">/</span><span>Phiếu yêu cầu</span></div>
            <span class="stitch-date"><span></span> Cập nhật theo thời gian thực</span>
        </div>

        <div class="hr-module-heading">
            <div><h1>Quản lý phiếu yêu cầu</h1><p>Xem, kiểm tra và xử lý các yêu cầu được gửi từ nhân viên trong hệ thống.</p></div>
            <div class="stitch-summary-icon secondary"><span class="material-symbols-outlined">inbox</span></div>
        </div>

        <div class="stitch-summary-grid hr-module-summary">
            <div class="stitch-summary-card"><div class="stitch-summary-head"><span>Tổng phiếu</span><span class="stitch-summary-icon"><span class="material-symbols-outlined">receipt_long</span></span></div><div class="stitch-summary-value">{{ $requests->count() }} <small>phiếu</small></div><div class="stitch-summary-caption">Trong danh sách hiện tại</div></div>
            <div class="stitch-summary-card"><div class="stitch-summary-head"><span>Chờ xử lý</span><span class="stitch-summary-icon secondary"><span class="material-symbols-outlined">pending_actions</span></span></div><div class="stitch-summary-value">{{ $requests->where('status.value', 'pending')->count() }} <small>phiếu</small></div><div class="stitch-summary-caption"><span class="material-symbols-outlined">schedule</span>Cần rà soát</div></div>
            <div class="stitch-summary-card"><div class="stitch-summary-head"><span>Đã hoàn thành</span><span class="stitch-summary-icon"><span class="material-symbols-outlined">task_alt</span></span></div><div class="stitch-summary-value">{{ $requests->where('status.value', 'completed')->count() }} <small>phiếu</small></div><div class="stitch-summary-caption"><span class="material-symbols-outlined">check_circle</span>Đã xử lý</div></div>
            <div class="stitch-summary-card"><div class="stitch-summary-head"><span>Từ chối</span><span class="stitch-summary-icon secondary"><span class="material-symbols-outlined">cancel</span></span></div><div class="stitch-summary-value">{{ $requests->where('status.value', 'rejected')->count() }} <small>phiếu</small></div><div class="stitch-summary-caption">Theo trạng thái hiện tại</div></div>
        </div>

        <div class="stitch-panel hr-demo-panel">
            <div class="stitch-panel-head"><div class="stitch-panel-title"><span class="stitch-panel-icon primary"><span class="material-symbols-outlined">assignment</span></span><div><h2>Danh sách phiếu yêu cầu</h2><p>Thông tin mẫu theo giao diện Stitch HRMS</p></div></div><span class="stitch-count-pill">{{ $requests->count() }} phiếu</span></div>
            <div class="stitch-table-wrap"><table class="stitch-table"><thead><tr><th>Mã phiếu</th><th>Người gửi</th><th>Nội dung</th><th>Ngày gửi</th><th>Trạng thái</th><th></th></tr></thead><tbody>
                @forelse($requests as $record)
                    @php($status = $record->status->value)
                    <tr><td><a class="stitch-request-code" href="{{ route('admin.requests.show', $record) }}">#PYC-{{ str_pad($record->id, 4, '0', STR_PAD_LEFT) }}</a></td><td><div class="hr-demo-person"><span class="avatar">{{ strtoupper(substr($record->creator->name ?? 'N', 0, 1)) }}</span><div><strong>{{ $record->creator->name ?? '—' }}</strong><small>{{ $record->creator->email ?? 'Không có email' }}</small></div></div></td><td>{{ $record->displayTitle() }}</td><td>{{ $record->created_at?->format('d/m/Y') }}</td><td><span class="stitch-status-pill {{ $status }}">{{ $status === 'pending' ? 'Đang xử lý' : ($status === 'rejected' ? 'Từ chối' : 'Hoàn thành') }}</span></td><td><a class="stitch-profile-link admin-table-action" href="{{ route('admin.requests.show', $record) }}">Xem chi tiết <span class="material-symbols-outlined">arrow_forward</span></a></td></tr>
                @empty
                    <tr><td colspan="6" class="stitch-empty">Chưa có phiếu yêu cầu.</td></tr>
                @endforelse
            </tbody></table></div>
        </div>
    </div>
@endsection
