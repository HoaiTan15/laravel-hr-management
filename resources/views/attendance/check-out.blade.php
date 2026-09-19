@extends('layouts.app')

@section('title', 'Xác nhận check-out · HUIT HRMs')

@section('content')
    <div class="page-header">
        <div>
            <div class="breadcrumb">Workspace / Attendance</div>
            <h1>Xác nhận check-out</h1>
        </div>
    </div>

    <section class="card" style="max-width:640px;">
        <div class="card-body">
            <h2 style="margin:0 0 8px; font-size:18px;">Bạn muốn check-out hôm nay?</h2>
            <p style="margin:0 0 20px; color:#64748b; font-size:14px;">Check-in: {{ $attendance->check_in_at->format('d/m/Y H:i') }}</p>
            <div style="display:flex; gap:12px;">
                <form method="POST" action="{{ route('attendance.checkout') }}">
                    @csrf
                    <button class="button button-primary" type="submit">Xác nhận check-out</button>
                </form>
                <a class="button button-outline" href="{{ url()->previous() }}">Quay lại</a>
            </div>
        </div>
    </section>
@endsection
