@extends('layouts.app')

@section('title', 'Quản lý chấm công · HUIT HRMs')

@section('content')
    <div class="page-header">
        <div>
            <div class="breadcrumb">HR / Attendance</div>
            <h1>Quản lý chấm công</h1>
        </div>
    </div>

    <section class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('hr.attendances.index') }}" style="display:flex; flex-wrap:wrap; gap:12px; margin-bottom:20px;">
                <input name="search" value="{{ request('search') }}" placeholder="Mã nhân viên hoặc họ tên" style="height:40px; min-width:260px; padding:0 12px; border:1px solid #cbd5e1; border-radius:8px;">
                <input name="date" type="date" value="{{ request('date') }}" style="height:40px; padding:0 12px; border:1px solid #cbd5e1; border-radius:8px;">
                <button class="button button-primary" type="submit">Lọc</button>
            </form>
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; font-size:14px;">
                    <thead><tr style="text-align:left; color:#64748b; background:#f8fafc;"><th style="padding:12px;">Nhân viên</th><th style="padding:12px;">Ngày</th><th style="padding:12px;">Check-in</th><th style="padding:12px;">Check-out</th><th style="padding:12px;">Chi tiết</th></tr></thead>
                    <tbody>
                        @forelse ($attendances as $attendance)
                            <tr style="border-top:1px solid #e2e8f0;"><td style="padding:12px;">{{ $attendance->employee->employee_code }} · {{ $attendance->employee->full_name }}</td><td style="padding:12px;">{{ $attendance->work_date->format('d/m/Y') }}</td><td style="padding:12px;">{{ $attendance->check_in_at->format('H:i') }}</td><td style="padding:12px;">{{ $attendance->check_out_at?->format('H:i') ?? 'Chưa check-out' }}</td><td style="padding:12px;"><a style="color:#004385; font-weight:600;" href="{{ route('hr.attendances.show', $attendance) }}">Xem</a></td></tr>
                        @empty
                            <tr><td colspan="5" style="padding:24px; text-align:center; color:#64748b;">Chưa có dữ liệu chấm công.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="margin-top:20px;">{{ $attendances->links() }}</div>
        </div>
    </section>
@endsection
