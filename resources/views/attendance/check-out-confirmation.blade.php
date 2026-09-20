@extends('layouts.employee')

@section('title', 'Xác nhận Check-out')

@section('content')
<div class="page-header">
    <div>
        <div class="breadcrumb">Workspace / Attendance</div>
        <h1>Xác nhận Check-out</h1>
    </div>
</div>

<section class="card" style="max-width:480px;">
    <div class="card-body">
        <div class="stitch-checkout-confirmation">
            <h2>Check-out thủ công</h2>
            <p>Bạn đã check-in lúc {{ $attendance->check_in_at->format('H:i') }}.</p>
            <p class="lead">Sau khi xác nhận check-out, ca làm việc sẽ kết thúc và bạn có thể đăng xuất.</p>

            <form method="POST" action="{{ route('attendance.check-out') }}">
                @csrf
                <button class="button button-primary" type="submit">Xác nhận Check-out</button>
            </form>

            <a class="button light full" style="margin-top:12px" href="{{ route('employee.home') }}">Quay lại Dashboard</a>
        </div>
    </div>
</section>
@endsection