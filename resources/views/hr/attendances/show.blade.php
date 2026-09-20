@extends('layouts.app')

@section('title', 'Chi tiết chấm công · HUIT HRMs')

@section('content')
    <div class="page-header">
        <div>
            <div class="breadcrumb">HR / Attendance / Detail</div>
            <h1>Chi tiết chấm công</h1>
        </div>
    </div>

    <section class="card" style="max-width:760px;">
        <div class="card-body">
            <p><strong>Nhân viên:</strong> {{ $attendance->employee->full_name }} ({{ $attendance->employee->employee_code }})</p>
            <p><strong>Ngày:</strong> {{ $attendance->work_date->format('d/m/Y') }}</p>
            <p><strong>Trạng thái:</strong> {{ $status }}</p>
            <form method="POST" action="{{ route('hr.attendances.update', $attendance) }}">
                @csrf
                @method('PATCH')
                <div class="form-field"><label for="check_in_at">Check-in</label><input id="check_in_at" name="check_in_at" type="datetime-local" value="{{ $attendance->check_in_at?->format('Y-m-d\\TH:i') }}"></div>
                <div class="form-field"><label for="check_out_at">Check-out</label><input id="check_out_at" name="check_out_at" type="datetime-local" value="{{ $attendance->check_out_at?->format('Y-m-d\\TH:i') }}"></div>
                <div class="form-field"><label for="adjustment_reason">Lý do điều chỉnh bắt buộc</label><input id="adjustment_reason" name="adjustment_reason" required></div>
                <button class="button button-primary" type="submit">Lưu điều chỉnh</button>
            </form>
        </div>
    </section>
@endsection
