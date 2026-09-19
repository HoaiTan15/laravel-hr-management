@extends('layouts.app')

@section('title', 'Check-in · HUIT HRMs')

@section('content')
    <div class="page-header">
        <div>
            <div class="breadcrumb">Workspace / Attendance</div>
            <h1>Check-in hôm nay</h1>
        </div>
    </div>

    <section class="card" style="max-width:640px;">
        <div class="card-body">
            @if ($attendance?->check_in_at)
                <h2 style="margin:0 0 8px; font-size:18px;">Bạn đã check-in</h2>
                <p style="margin:0 0 20px; color:#64748b; font-size:14px;">Thời gian: {{ $attendance->check_in_at->format('d/m/Y H:i') }}</p>
                @if ($attendance->check_out_at)
                    <p style="margin:0; color:#047857;">Đã check-out lúc {{ $attendance->check_out_at->format('H:i') }}.</p>
                @else
                    <a class="button button-primary" href="{{ route('attendance.checkout.confirmation') }}">Check-out</a>
                @endif
            @else
                <h2 style="margin:0 0 8px; font-size:18px;">Bạn chưa check-in hôm nay</h2>
                <p style="margin:0 0 20px; color:#64748b; font-size:14px;">Vui lòng check-in để truy cập workspace.</p>
                <form method="POST" action="{{ route('attendance.check-in.store') }}">
                    @csrf
                    <button class="button button-primary" type="submit">Check-in ngay</button>
                </form>
            @endif
        </div>
    </section>
@endsection
