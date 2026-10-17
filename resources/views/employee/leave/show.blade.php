@extends('layouts.employee')

@section('content')
<div class="stitch-page">
    <div class="stitch-context">HỆ THỐNG NHÂN SỰ / Nghỉ phép / Chi tiết</div>
    <h1>Chi tiết đơn nghỉ phép</h1>
    <p class="stitch-subtitle">Theo dõi trạng thái duyệt và phần lương của đơn này.</p>
    @if(session('success'))<div class="notice">{{ session('success') }}</div>@endif
    <section class="stitch-content-grid">
        <div class="stitch-panel"><h2>Kết quả xử lý</h2>@if($leave->status->value === 'approved')<div class="stitch-key-list"><div><span>Nghỉ có lương</span><strong>{{ number_format((float) $leave->paid_leave_days, 1) }} ngày</strong></div><div><span>Nghỉ không lương</span><strong>{{ number_format((float) $leave->unpaid_leave_days, 1) }} ngày</strong></div></div>@elseif($leave->status->value === 'pending')<p class="stitch-subtitle">HR chưa xử lý đơn này. Số ngày phép chỉ được trừ sau khi đơn được duyệt.</p>@else<p class="stitch-subtitle">Đơn bị từ chối nên không trừ ngày phép và không ảnh hưởng lương.</p>@endif@if($leave->review_note)<div class="notice" style="margin-top:18px"><strong>Phản hồi HR:</strong><br>{{ $leave->review_note }}</div>@endif@if($leave->reviewer)<p class="stitch-muted" style="margin-top:16px">Xử lý bởi {{ $leave->reviewer->name }} lúc {{ $leave->reviewed_at?->format('d/m/Y H:i') }}</p>@endif<a class="button button-light" style="margin-top:20px" href="{{ route('employee.leave.index') }}">Quay lại lịch sử</a></div>
    </section>
</div>
@endsection
