@extends('layouts.employee')

@section('content')
<div class="stitch-page">
    <div class="stitch-context">HỆ THỐNG NHÂN SỰ / Nghỉ phép / Tạo đơn</div>
    <h1>Gửi đơn nghỉ phép</h1>
    <p class="stitch-subtitle">Bạn còn <strong>{{ number_format($summary['remaining'], 1) }} ngày phép</strong> trong năm nay. Đơn vượt số ngày phép vẫn được HR xử lý; phần vượt sẽ không hưởng lương nếu được duyệt.</p>
    <section class="stitch-panel" style="max-width:900px">
        <form method="POST" action="{{ route('employee.leave.store') }}">
            @csrf
            <div class="stitch-content-grid">
                <div>
                    <div class="form-group"><label for="start_date">Từ ngày *</label><input class="input" id="start_date" type="date" name="start_date" value="{{ old('start_date') }}" required></div>
                    <div class="form-group"><label for="end_date">Đến ngày *</label><input class="input" id="end_date" type="date" name="end_date" value="{{ old('end_date') }}" required></div>
                    <div class="form-group"><label for="duration_type">Hình thức nghỉ *</label><select class="select" id="duration_type" name="duration_type" required><option value="full_day" @selected(old('duration_type', 'full_day') === 'full_day')>Cả ngày</option><option value="half_day" @selected(old('duration_type') === 'half_day')>Nửa ngày (0,5 ngày phép)</option></select></div>
                    <div class="form-group" id="half-day-period"><label for="half_day_period">Buổi nghỉ *</label><select class="select" id="half_day_period" name="half_day_period"><option value="morning" @selected(old('half_day_period') === 'morning')>Buổi sáng</option><option value="afternoon" @selected(old('half_day_period') === 'afternoon')>Buổi chiều</option></select></div>
                </div>
                <div>
                    <div class="form-group"><label for="reason">Lý do nghỉ *</label><textarea class="input textarea" id="reason" name="reason" rows="8" maxlength="2000" required>{{ old('reason') }}</textarea></div>
                    <div class="notice">Cách tính: ngày làm việc chỉ gồm Thứ Hai–Thứ Sáu; nghỉ nửa ngày tính 0,5 ngày phép, hai lần nửa ngày mới bằng một ngày.</div>
                </div>
            </div>
            <div style="display:flex;gap:10px;margin-top:18px"><a class="button button-light" href="{{ route('employee.leave.index') }}">Hủy</a><button class="button" type="submit"><span class="material-symbols-outlined">send</span>Gửi đơn cho HR</button></div>
        </form>
    </section>
</div>
<script>
    const duration = document.getElementById('duration_type');
    const endDate = document.getElementById('end_date');
    const halfDay = document.getElementById('half-day-period');
    const syncLeaveForm = () => { const isHalf = duration.value === 'half_day'; halfDay.style.display = isHalf ? 'block' : 'none'; if (isHalf) { endDate.value = document.getElementById('start_date').value; endDate.readOnly = true; } else { endDate.readOnly = false; } };
    document.getElementById('start_date').addEventListener('change', syncLeaveForm); duration.addEventListener('change', syncLeaveForm); syncLeaveForm();
</script>
@endsection
