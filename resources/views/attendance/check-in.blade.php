@extends('layouts.employee')

@section('title', 'Check-in hôm nay')

@section('content')
<div class="page-header">
    <div>
        <div class="breadcrumb">Workspace / Attendance</div>
        <h1>Check-in hôm nay</h1>
    </div>
</div>

<section class="card" style="max-width:640px;">
    <div class="card-body">
        <div class="stitch-mandatory-checkin">
            <div class="stitch-mandatory-checkin-icon">🪪</div>
            <h2>Chấm công đầu ngày</h2>
            <p class="lead">Nhân viên HR/Employee phải hoàn tất check-in trước khi bắt đầu làm việc.</p>

            @if(!$attendance || !$attendance->check_in_at)
                <form method="POST" action="{{ route('attendance.check-in.store') }}">
                    @csrf
                    <button class="button button-primary" type="submit">Check-in ngay</button>
                </form>
            @elseif($attendance->check_out_at)
                <div class="success"><span class="material-symbols-outlined">check</span> Đã hoàn tất ca làm việc. <a href="{{ route('employee.home') }}">Về Dashboard</a></div>
            @else
                <div class="working">
                    <strong>Đang làm việc</strong>
                    <a href="{{ route('attendance.check-out') }}">Kết thúc ca ngay</a>
                </div>
            @endif
        </div>
    </div>
</section>
@endsection