@extends('layouts.hr')

@section('title', $title . ' · HUIT HRMS')
@section('content')
<div class="stitch-page">
    <div class="stitch-context">HR / Công việc / {{ $title }}</div>
    <div class="stitch-form-heading"><h1>{{ $title }}</h1><p class="stitch-subtitle">Giao việc và theo dõi tiến độ thực hiện của nhân viên.</p></div>
    <section class="stitch-panel stitch-form-panel">
        <form class="stitch-form" method="POST" action="{{ $formAction }}">
            @csrf
            @if($formMethod !== 'POST') @method($formMethod) @endif
            <div class="form-grid form-grid-2">
                <div class="form-group"><label for="title">Tên công việc *</label><input class="input" id="title" name="title" value="{{ old('title', $task->title) }}" required></div>
                <div class="form-group"><label for="assigned_to">Người thực hiện *</label><select class="select" id="assigned_to" name="assigned_to" required><option value="">Chọn nhân viên</option>@foreach($employees as $employee)<option value="{{ $employee->id }}" @selected((string) old('assigned_to', $task->assigned_to) === (string) $employee->id)>{{ $employee->full_name }} · {{ $employee->department?->name ?? '—' }}</option>@endforeach</select></div>
                <div class="form-group"><label for="status">Trạng thái *</label><select class="select" id="status" name="status" required>@foreach(['pending' => 'Chờ xử lý', 'in_progress' => 'Đang thực hiện', 'completed' => 'Hoàn thành', 'stopped' => 'Đã dừng'] as $value => $label)<option value="{{ $value }}" @selected(old('status', $task->status?->value) === $value)>{{ $label }}</option>@endforeach</select></div>
                <div class="form-group"><label for="due_at">Hạn hoàn thành</label><input class="input" id="due_at" type="datetime-local" name="due_at" value="{{ old('due_at', $task->due_at?->format('Y-m-d\TH:i')) }}"></div>
            </div>
            <div class="form-group"><label for="description">Mô tả công việc</label><textarea class="input textarea textarea-lg" id="description" name="description" placeholder="Nêu rõ mục tiêu, phạm vi và kết quả cần đạt...">{{ old('description', $task->description) }}</textarea></div>
            <div class="form-group"><label for="notes">Ghi chú nội bộ</label><textarea class="input textarea" id="notes" name="notes" placeholder="Thông tin bổ sung cho người thực hiện...">{{ old('notes', $task->notes) }}</textarea></div>
            <div class="form-actions"><button class="button" type="submit">Lưu công việc</button><a class="button button-light" href="{{ route('hr.tasks') }}">Hủy</a></div>
        </form>
    </section>
</div>
@endsection
