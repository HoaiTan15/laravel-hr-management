@extends('layouts.hr')

@php
    $statusLabels = ['active' => 'Đang làm việc', 'terminated' => 'Đã nghỉ việc'];
@endphp

@section('title', $title . ' · HUIT HRMS')

@section('content')
<div class="stitch-page">
    <div class="stitch-context-row">
        <div>
            <div class="stitch-context">HR / Phòng ban / Chi tiết</div>
            <h1>{{ $department->name }}</h1>
            <p class="stitch-subtitle">{{ $department->description ?: 'Quản lý nhân sự, chức vụ và trạng thái làm việc trong phòng ban.' }}</p>
        </div>
        <div class="toolbar">
            <a class="button" href="{{ route('hr.employees.create') }}">+ Thêm nhân sự</a>
            <a class="button button-light" href="{{ route('hr.departments.edit', $department) }}">Sửa phòng ban</a>
            <a class="button button-light" href="{{ route('hr.departments') }}">Quay lại</a>
        </div>
    </div>

    <div class="stitch-summary-grid">
        <article class="stitch-summary-card"><div class="stitch-summary-head"><span>Tổng nhân sự</span><div class="stitch-summary-icon primary"><span class="material-symbols-outlined">groups</span></div></div><div class="stitch-summary-value">{{ $totalEmployees }}</div><span class="stitch-summary-caption">Nhân sự thuộc phòng ban</span></article>
        <article class="stitch-summary-card"><div class="stitch-summary-head"><span>Đang làm việc</span><div class="stitch-summary-icon primary"><span class="material-symbols-outlined">person_check</span></div></div><div class="stitch-summary-value">{{ $activeEmployees }}</div><span class="stitch-summary-caption">Tài khoản đang hoạt động</span></article>
        <article class="stitch-summary-card"><div class="stitch-summary-head"><span>Đã nghỉ việc</span><div class="stitch-summary-icon primary"><span class="material-symbols-outlined">person_off</span></div></div><div class="stitch-summary-value">{{ $terminatedEmployees }}</div><span class="stitch-summary-caption">Lưu trong lịch sử</span></article>
        <article class="stitch-summary-card"><div class="stitch-summary-head"><span>Chức vụ</span><div class="stitch-summary-icon primary"><span class="material-symbols-outlined">badge</span></div></div><div class="stitch-summary-value">{{ $positionCount }}</div><span class="stitch-summary-caption">Đang được phân bổ</span></article>
    </div>

    <section class="stitch-panel">
        <div class="section-head">
            <div><h2>Danh sách nhân sự</h2><p class="stitch-subtitle">Xem hồ sơ, phân công và cập nhật từng nhân sự trong phòng ban.</p></div>
        </div>
        <form class="hr-demo-toolbar" method="GET" action="{{ route('hr.departments.show', $department) }}">
            <input class="input" name="search" value="{{ request('search') }}" placeholder="Tìm theo mã, họ tên hoặc email...">
            <select class="select" name="status">
                <option value="">Tất cả trạng thái</option>
                <option value="active" @selected(request('status') === 'active')>Đang làm việc</option>
                <option value="terminated" @selected(request('status') === 'terminated')>Đã nghỉ việc</option>
            </select>
            <button class="button button-light" type="submit">Lọc danh sách</button>
        </form>

        <div class="stitch-table-wrap">
            <table class="stitch-table">
                <thead><tr><th>Mã NV</th><th>Nhân sự</th><th>Chức vụ</th><th>Liên hệ</th><th>Ngày vào làm</th><th>Trạng thái</th><th>Thao tác</th></tr></thead>
                <tbody>
                @forelse($employees as $employee)
                    <tr>
                        <td><strong>{{ $employee->employee_code }}</strong></td>
                        <td><strong>{{ $employee->full_name }}</strong><br><small>{{ $employee->user?->email ?? $employee->email ?? 'Chưa có email' }}</small></td>
                        <td>{{ $employee->position?->name ?? '—' }}</td>
                        <td>{{ $employee->phone ?: '—' }}</td>
                        <td>{{ $employee->hire_date?->format('d/m/Y') ?? '—' }}</td>
                        <td><span class="stitch-status-pill {{ $employee->employment_status?->value === 'active' ? 'completed' : 'rejected' }}">{{ $statusLabels[$employee->employment_status?->value] ?? '—' }}</span></td>
                        <td><a class="button" href="{{ route('hr.employees.show', $employee) }}">Chi tiết</a> <a class="button button-light" href="{{ route('hr.employees.edit', $employee) }}">Quản lý</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="stitch-empty">Chưa có nhân sự phù hợp trong phòng ban này.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($employees->hasPages())<div class="admin-pagination">{{ $employees->links() }}</div>@endif
    </section>
</div>
@endsection
