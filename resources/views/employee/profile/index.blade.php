@extends('layouts.employee')

@section('content')
<div class="page">
    <div class="employee-crumb"><span>HỆ THỐNG NHÂN SỰ</span><b>/</b><strong>Hồ sơ cá nhân</strong></div>
    <h1>Hồ sơ cá nhân</h1>
    <p class="subtitle">Thông tin nhân sự và thông tin liên hệ của bạn.</p>

    @if(session('status'))
        <div class="notice">✓ {{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="notice">{{ $errors->first() }}</div>
    @endif
    @if($pendingProfileChange)
        <div class="notice">PYC thay đổi hồ sơ đang chờ xử lý. Hồ sơ gốc chưa thay đổi.</div>
    @endif

    <div class="grid grid-main-side" style="margin-top:20px">
        <div class="card">
            <div class="person" style="margin-bottom:25px">
                <span class="avatar" style="width:70px;height:70px;font-size:24px">
                    {{ strtoupper(substr($employee->full_name, 0, 2)) }}
                </span>
                <div>
                    <h2>{{ $employee->full_name }}</h2>
                    <div class="muted">{{ $employee->employee_code }} · {{ $employee->position->name ?? 'Nhân viên' }}</div>
                </div>
            </div>

            <h2>Thông tin nhân sự & tổ chức</h2>
            <div class="row"><span class="row-label">Mã nhân viên</span><strong>{{ $employee->employee_code }}</strong></div>
            <div class="row"><span class="row-label">Phòng ban</span><strong>{{ $employee->department->name ?? '—' }}</strong></div>
            <div class="row"><span class="row-label">Chức vụ</span><strong>{{ $employee->position->name ?? '—' }}</strong></div>
            <div class="row"><span class="row-label">Ngày vào làm</span><strong>{{ $employee->hire_date?->format('d/m/Y') }}</strong></div>
            <div class="row"><span class="row-label">Trạng thái</span><span class="badge green">{{ $employee->employment_status->value }}</span></div>

            <h2 style="margin-top:28px">Thông tin cá nhân</h2>
            <div class="row"><span class="row-label">Ngày sinh</span><strong>{{ $employee->date_of_birth?->format('d/m/Y') ?? 'Chưa cập nhật' }}</strong></div>
            <div class="row"><span class="row-label">Giới tính</span><strong>{{ $employee->gender ?: 'Chưa cập nhật' }}</strong></div>
            <div class="row"><span class="row-label">CCCD</span><strong>{{ $employee->cccd ?: 'Chưa cập nhật' }}</strong></div>
        </div>

        <div class="card right-card">
            <h2>Thông tin liên hệ</h2>
            <p class="muted">Email cá nhân, số điện thoại, địa chỉ và avatar được cập nhật trực tiếp.</p>
            <form method="POST" action="{{ route('employee.profile.update') }}">
                @csrf
                @method('PUT')
                <div class="form-group"><label>Email cá nhân</label><input class="input" type="email" name="email" value="{{ old('email', $employee->email) }}"></div>
                <div class="form-group"><label>Số điện thoại</label><input class="input" name="phone" value="{{ old('phone', $employee->phone) }}"></div>
                <div class="form-group"><label>Địa chỉ</label><textarea class="input textarea" name="address">{{ old('address', $employee->address) }}</textarea></div>
                <div class="form-group"><label>Avatar</label><input class="input" name="avatar" value="{{ old('avatar', $employee->avatar) }}"></div>
                <button class="button full" type="submit">Lưu thông tin liên hệ</button>
            </form>

            <div class="notice" style="margin-top:16px">Mã nhân viên, role, phòng ban, chức vụ, ngày vào làm và trạng thái không được Employee tự sửa.</div>
            <a class="button light full" style="margin-top:12px" href="{{ route('employee.profile', ['change' => 1, 'change_request' => $pendingProfileChange?->id]) }}">{{ $pendingProfileChange ? 'Chỉnh sửa PYC thay đổi hồ sơ' : 'Yêu cầu thay đổi ngày sinh, giới tính hoặc CCCD' }}</a>
        </div>
    </div>
</div>

@if($showChangeRequest)
    @php($changeValues = $profileChangeRequest?->payload['changes'] ?? [])
    <div class="modal-backdrop">
        <div class="modal" style="text-align:left">
            <h2>{{ $profileChangeRequest ? 'Chỉnh sửa yêu cầu thay đổi hồ sơ' : 'Yêu cầu thay đổi hồ sơ' }}</h2>
            <p>Hồ sơ gốc chưa thay đổi cho đến khi PYC được xử lý.</p>
            <form method="POST" action="{{ $profileChangeRequest ? route('employee.profile.change-request.update', $profileChangeRequest) : route('employee.profile.change-request') }}">
                @csrf
                @if($profileChangeRequest) @method('PUT') @endif
                <div class="form-group"><label>Ngày sinh mới</label><input class="input" type="date" name="date_of_birth" value="{{ old('date_of_birth', $changeValues['date_of_birth'] ?? '') }}"></div>
                <div class="form-group"><label>Giới tính mới</label><input class="input" name="gender" value="{{ old('gender', $changeValues['gender'] ?? '') }}"></div>
                <div class="form-group"><label>CCCD mới</label><input class="input" name="cccd" value="{{ old('cccd', $changeValues['cccd'] ?? '') }}"></div>
                <div class="form-group"><label>Lý do thay đổi *</label><textarea class="input textarea" name="reason" required>{{ old('reason', $profileChangeRequest?->payload['reason'] ?? '') }}</textarea></div>
                <div style="display:flex;gap:10px">
                    <a class="button light" href="{{ route('employee.profile') }}">Hủy</a>
                    <button class="button" type="submit">Gửi PYC thay đổi</button>
                </div>
            </form>
        </div>
    </div>
@endif
@endsection
