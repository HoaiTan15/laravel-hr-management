@extends('layouts.employee')

@section('title', 'Check-in hôm nay')

@section('content')
<div class="stitch-page attendance-page">
    <div class="page-header">
        <div>
            <div class="breadcrumb">Workspace <span>/</span> Attendance</div>
            <h1>Check-in hôm nay</h1>
            <p class="page-description">Theo dõi trạng thái ca làm việc của bạn trong hôm nay.</p>
        </div>
    </div>

    <section class="card attendance-card">
        <div class="card-body">
            <div class="stitch-mandatory-checkin">
                <div class="stitch-mandatory-checkin-icon" aria-hidden="true"><span class="material-symbols-outlined">badge</span></div>
                <div class="attendance-copy">
                    <span class="attendance-eyebrow">Attendance</span>
                    <h2>Chấm công đầu ngày</h2>
                    <p class="lead">Nhân viên HR/Employee phải hoàn tất check-in trước khi bắt đầu làm việc.</p>
                </div>

                @if(!$attendance || !$attendance->check_in_at)
                    <form method="POST" action="{{ route('attendance.check-in.store') }}">
                        @csrf
                        <button class="button button-primary attendance-action" type="submit"><span class="material-symbols-outlined">login</span>Check-in ngay</button>
                    </form>
                @elseif($attendance->check_out_at)
                    <div class="attendance-result attendance-result-success">
                        <span class="material-symbols-outlined">check_circle</span>
                        <span>Đã hoàn tất ca làm việc.</span>
                        <a class="attendance-inline-link" href="{{ route('employee.home') }}">Về Dashboard <span class="material-symbols-outlined">arrow_forward</span></a>
                    </div>
                @else
                    <div class="attendance-result attendance-result-working">
                        <div class="attendance-result-label"><span class="attendance-status-dot"></span><strong>Đang làm việc</strong></div>
                        <a class="button button-outline attendance-action" href="{{ route('attendance.check-out') }}">Kết thúc ca <span class="material-symbols-outlined">logout</span></a>
                    </div>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection
