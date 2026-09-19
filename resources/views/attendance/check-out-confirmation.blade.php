@extends('hrms-layout')

@section('content')
<div class="modal-backdrop">
    <div class="modal">
        <div class="modal-icon">↩</div>
        <span class="badge">HỆ THỐNG CHẤM CÔNG HUIT</span>
        <h2>Xác nhận Check-out</h2>
        <p>Bạn đã Check-in lúc {{ $attendance->check_in_at?->format('H:i') }} và chưa Check-out hôm nay.</p>
        <p>Vui lòng Check-out thủ công để kết thúc ca làm việc. Sau đó bạn có thể đăng xuất.</p>
        <form method="POST" action="{{ route('attendance.check-out') }}">
            @csrf
            <button class="button full" type="submit">Xác nhận Check-out</button>
        </form>
        <a class="button light full" style="margin-top:12px" href="{{ route('employee.home') }}">Quay lại Dashboard</a>
    </div>
</div>
@endsection
