@extends('layouts.employee')

@section('title', 'Xác nhận Check-out')

@section('content')
<div class="stitch-page attendance-page">
    <div class="page-header">
        <div>
            <div class="breadcrumb">Workspace <span>/</span> Attendance</div>
            <h1>Xác nhận Check-out</h1>
            <p class="page-description">Hoàn tất ca làm việc trước khi rời khỏi hệ thống.</p>
        </div>
    </div>

    <section class="card attendance-card attendance-checkout-card">
        <div class="card-body">
            <div class="stitch-checkout-confirmation">
                <div class="checkout-icon" aria-hidden="true"><span class="material-symbols-outlined">logout</span></div>
                <span class="attendance-eyebrow">Kết thúc ca làm việc</span>
                <h2>Check-out thủ công</h2>
                <div class="checkout-time"><span class="material-symbols-outlined">schedule</span><span>Bạn đã check-in lúc <strong>{{ $attendance->check_in_at->format('H:i') }}</strong>.</span></div>
                <p class="lead">Sau khi xác nhận check-out, ca làm việc sẽ kết thúc và bạn có thể đăng xuất.</p>

                <form method="POST" action="{{ route('attendance.check-out') }}">
                    @csrf
                    <button class="button button-primary attendance-action attendance-action-wide" type="submit"><span class="material-symbols-outlined">check_circle</span>Xác nhận Check-out</button>
                </form>

                <a class="button button-light attendance-action attendance-action-wide" href="{{ route('employee.home') }}">Quay lại Dashboard</a>
            </div>
        </div>
    </section>
</div>
@endsection
