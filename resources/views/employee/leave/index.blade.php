@extends('layouts.employee')

@section('content')
<div class="stitch-page">
    <div class="stitch-context-row">
        <div><div class="stitch-context">HỆ THỐNG NHÂN SỰ / Nghỉ phép</div><h1>Lịch sử nghỉ phép</h1><p class="stitch-subtitle">Gửi đơn nghỉ phép và theo dõi kết quả HR xử lý.</p></div>
        <a class="button" href="{{ route('employee.leave.create') }}"><span class="material-symbols-outlined">add</span>Gửi đơn nghỉ phép</a>
    </div>

    @if(session('success'))<div class="notice">{{ session('success') }}</div>@endif
    @if(session('warning'))<div class="notice alert-error">{{ session('warning') }}</div>@endif

    <div class="stitch-summary-grid">
        <article class="stitch-summary-card"><div class="stitch-summary-head"><span>Ngày phép năm {{ $year }}</span><div class="stitch-summary-icon primary"><span class="material-symbols-outlined">event_available</span></div></div><div class="stitch-summary-value">{{ number_format($summary['quota'], 1) }} <small>ngày</small></div><span class="stitch-summary-caption">Số ngày phép được hưởng trong năm</span></article>
        <article class="stitch-summary-card"><div class="stitch-summary-head"><span>Đã sử dụng</span><div class="stitch-summary-icon secondary"><span class="material-symbols-outlined">event_busy</span></div></div><div class="stitch-summary-value">{{ number_format($summary['used'], 1) }} <small>ngày</small></div><span class="stitch-summary-caption">Chỉ tính đơn đã duyệt</span></article>
        <article class="stitch-summary-card"><div class="stitch-summary-head"><span>Còn lại</span><div class="stitch-summary-icon primary"><span class="material-symbols-outlined">calendar_month</span></div></div><div class="stitch-summary-value">{{ number_format($summary['remaining'], 1) }} <small>ngày</small></div><span class="stitch-summary-caption">Nửa ngày được tính 0,5 ngày phép</span></article>
        <article class="stitch-summary-card"><div class="stitch-summary-head"><span>Đang chờ duyệt</span><div class="stitch-summary-icon secondary"><span class="material-symbols-outlined">pending_actions</span></div></div><div class="stitch-summary-value">{{ number_format($summary['pending'], 1) }} <small>ngày</small></div><span class="stitch-summary-caption">Chưa trừ vào ngày phép</span></article>
    </div>

    <section class="stitch-panel">
        <div class="stitch-panel-head">
            <div class="stitch-panel-title"><div class="stitch-panel-icon primary"><span class="material-symbols-outlined">history</span></div><div><h2>Đơn nghỉ phép của tôi</h2><p>Thứ Bảy và Chủ Nhật không được tính là ngày làm việc.</p></div></div>
            <form method="GET"><select class="select" name="year" onchange="this.form.submit()">@for($option = now()->year - 2; $option <= now()->year + 1; $option++)<option value="{{ $option }}" @selected($year === $option)>{{ $option }}</option>@endfor</select></form>
        </div>
        <div class="stitch-table-wrap"><table class="stitch-table"><thead><tr><th>Thời gian</th><th>Loại</th><th>Số ngày phép</th><th>Lý do</th><th>Trạng thái</th><th></th></tr></thead><tbody>
            @forelse($leaves as $leave)
                <tr><td><strong>{{ $leave->start_date->format('d/m/Y') }}</strong>@if(!$leave->start_date->isSameDay($leave->end_date))<br>đến {{ $leave->end_date->format('d/m/Y') }}@endif</td><td>{{ $leave->duration_type->value === 'half_day' ? 'Nửa ngày ('.($leave->half_day_period === 'morning' ? 'sáng' : 'chiều').')' : 'Cả ngày' }}</td><td>{{ number_format((float) $leave->requested_days, 1) }}@if($leave->status->value === 'approved')<br><small class="stitch-muted">Có lương: {{ number_format((float) $leave->paid_leave_days, 1) }}</small>@endif</td><td>{{ Str::limit($leave->reason, 60) }}</td><td><span class="stitch-status-pill {{ $leave->status->value === 'approved' ? 'completed' : ($leave->status->value === 'rejected' ? 'rejected' : 'pending') }}">{{ ['pending' => 'Đang chờ duyệt', 'approved' => 'Đã duyệt', 'rejected' => 'Từ chối'][$leave->status->value] }}</span>@if($leave->unpaid_leave_days > 0)<br><small class="stitch-muted">Không lương: {{ number_format((float) $leave->unpaid_leave_days, 1) }} ngày</small>@endif</td><td><a class="button button-light" href="{{ route('employee.leave.show', $leave) }}">Chi tiết</a></td></tr>
            @empty
                <tr><td colspan="6" class="stitch-empty">Bạn chưa có đơn nghỉ phép trong năm {{ $year }}.</td></tr>
            @endforelse
        </tbody></table></div>
    </section>
</div>
@endsection
