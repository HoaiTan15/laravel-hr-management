@extends('layouts.hr')

@section('title', $title . ' · HUIT HRMS')

@section('content')
<div class="stitch-page">
    <div class="stitch-context-row"><div><div class="stitch-context">HR / Nhân viên / {{ $title }}</div><h1>{{ $title }}</h1><p class="stitch-subtitle">Quản lý hồ sơ, tài khoản và trạng thái làm việc của nhân viên.</p></div></div>
    <section class="stitch-panel">
        <form method="POST" action="{{ $formAction }}">
            @csrf
            @if($formMethod !== 'POST') @method($formMethod) @endif
            <div class="grid grid-3">
                <div class="form-group"><label for="full_name">Họ và tên *</label><input class="input" id="full_name" name="full_name" value="{{ old('full_name', $employee->full_name) }}" required></div>
                <div class="form-group"><label for="email">Email đăng nhập *</label><input class="input" id="email" type="email" name="email" value="{{ old('email', $employee->user?->email ?? $employee->email) }}" required></div>
                <div class="form-group"><label for="employee_code">Mã nhân viên *</label><input class="input" id="employee_code" name="employee_code" value="{{ old('employee_code', $employee->employee_code) }}" required></div>
                @if($formMethod === 'POST')
                    <div class="form-group"><label for="password">Mật khẩu *</label><input class="input" id="password" type="password" name="password" minlength="8" required></div>
                    <div class="form-group"><label for="password_confirmation">Nhập lại mật khẩu *</label><input class="input" id="password_confirmation" type="password" name="password_confirmation" minlength="8" required></div>
                @endif
                <div class="form-group"><label for="department_id">Phòng ban *</label><select class="select" id="department_id" name="department_id" required><option value="">Chọn phòng ban</option>@foreach($departments as $department)<option value="{{ $department->id }}" @selected((string) old('department_id', $employee->department_id) === (string) $department->id)>{{ $department->name }}</option>@endforeach</select></div>
                <div class="form-group"><label for="position_id">Chức vụ *</label><select class="select" id="position_id" name="position_id" required><option value="">Chọn chức vụ</option>@foreach($positions as $position)<option value="{{ $position->id }}" @selected((string) old('position_id', $employee->position_id) === (string) $position->id)>{{ $position->name }}</option>@endforeach</select></div>
                <div class="form-group"><label for="date_of_birth">Ngày sinh</label><input class="input" id="date_of_birth" type="date" name="date_of_birth" value="{{ old('date_of_birth', $employee->date_of_birth?->format('Y-m-d')) }}"></div>
                <div class="form-group"><label for="gender">Giới tính</label><select class="select" id="gender" name="gender"><option value="">Chưa cập nhật</option><option value="male" @selected(old('gender', $employee->gender) === 'male')>Nam</option><option value="female" @selected(old('gender', $employee->gender) === 'female')>Nữ</option><option value="other" @selected(old('gender', $employee->gender) === 'other')>Khác</option></select></div>
                <div class="form-group"><label for="cccd">CCCD</label><input class="input" id="cccd" name="cccd" value="{{ old('cccd', $employee->cccd) }}"></div>
                <div class="form-group"><label for="phone">Số điện thoại</label><input class="input" id="phone" name="phone" value="{{ old('phone', $employee->phone) }}"></div>
                <div class="form-group"><label for="hire_date">Ngày vào làm *</label><input class="input" id="hire_date" type="date" name="hire_date" value="{{ old('hire_date', $employee->hire_date?->format('Y-m-d')) }}" required></div>
                <div class="form-group"><label for="employment_status">Trạng thái</label><select class="select" id="employment_status" name="employment_status"><option value="active" @selected(old('employment_status', $employee->employment_status?->value) === 'active')>Đang làm việc</option><option value="terminated" @selected(old('employment_status', $employee->employment_status?->value) === 'terminated')>Đã nghỉ việc</option></select></div>
            </div>
            <div class="form-group"><label for="address">Địa chỉ</label><textarea class="input textarea" id="address" name="address">{{ old('address', $employee->address) }}</textarea></div>
            @if($formMethod !== 'POST')
                <div class="form-section-label">Thông tin nghỉ việc</div>
                <div class="grid grid-2">
                    <div class="form-group"><label for="termination_date">Ngày nghỉ việc</label><input class="input" id="termination_date" type="date" name="termination_date" value="{{ old('termination_date', $employee->termination_date?->format('Y-m-d')) }}"></div>
                    <div class="form-group"><label for="termination_reason">Lý do nghỉ việc</label><input class="input" id="termination_reason" name="termination_reason" value="{{ old('termination_reason', $employee->termination_reason) }}"></div>
                </div>
            @endif
            <div class="toolbar"><button class="button" type="submit">Lưu hồ sơ</button><a class="button button-light" href="{{ route('hr.employees') }}">Hủy</a></div>
        </form>
    </section>
</div>
@endsection
