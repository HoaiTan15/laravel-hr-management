@extends('layouts.hr')
@section('content')
<div class="stitch-page">
    <div class="stitch-context">HR / Quản lý lương / Chi tiết</div>
    <div class="stitch-context-row"><div><h1>Lương {{ $employee->full_name }}</h1><p class="stitch-subtitle">{{ $employee->employee_code }} · {{ $employee->department?->name }} · Tháng {{ $salary->month }}/{{ $salary->year }}</p></div><a class="button button-light" href="{{ route('hr.salary.index', ['year' => $salary->year, 'month' => $salary->month]) }}">Về bảng lương</a></div>
    @include('partials.salary-adjustments')
    <p class="penalty-muted">Quy tắc tự động: đi muộn sau 08:30; về sớm trước {{ config('payroll.workday_end') }}; quên chấm công ra chỉ xét các ngày trước hôm nay. Ngày có nghỉ phép đã duyệt được bỏ qua khi tính phạt tự động. Phụ cấp tháng tính trọn mức, chưa chia theo ngày vào/nghỉ việc.</p>
    <section class="stitch-panel">
        <h2>Vi phạm HR ghi nhận trong tháng</h2>
        <p class="penalty-muted">Nhập tổng số lần và lý do; nhập 0 để bỏ ghi nhận. Mức phạt lấy từ cấu hình đang bật áp dụng cho nhân viên. Đi muộn, về sớm và quên chấm công ra được tính tự động.</p>
        @if($salary->status->value === 'draft')
        <form method="POST" action="{{ route('hr.salary.update', $employee) }}">
            @csrf @method('PATCH')
            <input type="hidden" name="year" value="{{ $salary->year }}"><input type="hidden" name="month" value="{{ $salary->month }}"><input type="hidden" name="action" value="manual">
            @foreach($manualTypes as $type => $label)
            <div class="form-grid form-grid-2"><div class="form-group"><label for="count-{{ $type }}">{{ $label }} (số lần)</label><input class="input" id="count-{{ $type }}" type="number" min="0" max="1000" step="1" name="penalties[{{ $type }}][count]" value="{{ old('penalties.'.$type.'.count', $salary->manual_penalties[$type]['count'] ?? 0) }}" required></div><div class="form-group"><label for="reason-{{ $type }}">Lý do / Căn cứ</label><input class="input" id="reason-{{ $type }}" maxlength="1000" name="penalties[{{ $type }}][reason]" value="{{ old('penalties.'.$type.'.reason', $salary->manual_penalties[$type]['reason'] ?? '') }}"></div></div>
            @endforeach
            <div class="form-actions"><button class="button" type="submit">Lưu và tính lại</button></div>
        </form>
        @else <p class="notice">Bảng lương đã khóa. Mở lại để sửa các ghi nhận.</p> @endif
    </section>
    <section class="stitch-panel" style="margin-top:20px"><h2>Duyệt bảng lương</h2><p class="penalty-muted">Chỉ chốt khi tháng đã kết thúc. Kiểm tra chấm công, ngày nghỉ và các khoản cộng/trừ trước khi chốt. Mở lại sẽ áp dụng dữ liệu và cấu hình hiện tại khi tính lại.</p>
        @if($salary->status->value !== 'paid')
        <form method="POST" class="form-actions" action="{{ route('hr.salary.update', $employee) }}" onsubmit="return confirm('Bạn đã kiểm tra và muốn cập nhật trạng thái bảng lương này?')">@csrf @method('PATCH')<input type="hidden" name="year" value="{{ $salary->year }}"><input type="hidden" name="month" value="{{ $salary->month }}"><input type="hidden" name="action" value="{{ $salary->status->value === 'draft' ? 'confirm' : 'reopen' }}"><button class="button" type="submit">{{ $salary->status->value === 'draft' ? 'Chốt bảng lương' : 'Mở lại bảng lương' }}</button></form>
        @endif
    </section>
</div>
@endsection
