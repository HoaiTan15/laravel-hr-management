@extends('layouts.hr')

@php($statusLabels = ['pending' => 'Chờ xử lý', 'in_progress' => 'Đang thực hiện', 'completed' => 'Hoàn thành', 'stopped' => 'Đã dừng'])

@section('title', $title . ' · HUIT HRMS')
@section('content')
<div class="stitch-page task-detail-page">
    <div class="stitch-context-row"><div><div class="stitch-context">HR / Công việc / Chi tiết</div><h1>{{ $task->title }}</h1><p class="stitch-subtitle">Theo dõi người thực hiện, tiến độ và nội dung công việc.</p></div><div class="toolbar"><a class="button" href="{{ route('hr.tasks.edit', $task) }}">Chỉnh sửa</a><a class="button button-light" href="{{ route('hr.tasks') }}">Quay lại</a></div></div>
    <div class="stitch-content-grid task-detail-grid">
        <section class="stitch-panel task-detail-panel"><div class="task-panel-heading"><div><h2>Thông tin công việc</h2><p>Thông tin phân công và tiến độ</p></div><span class="task-status-badge task-status-{{ $task->status?->value }}">{{ $statusLabels[$task->status?->value] ?? $task->status?->value }}</span></div><div class="stitch-key-list task-detail-list"><div><span>Người thực hiện</span><strong>{{ $task->assignee?->full_name ?? '—' }}</strong></div><div><span>Phòng ban</span><strong>{{ $task->assignee?->department?->name ?? '—' }}</strong></div><div><span>Chức vụ</span><strong>{{ $task->assignee?->position?->name ?? '—' }}</strong></div><div><span>Người giao</span><strong>{{ $task->creator?->name ?? '—' }}</strong></div><div><span>Hạn hoàn thành</span><strong>{{ $task->due_at?->format('d/m/Y H:i') ?? 'Không đặt hạn' }}</strong></div><div><span>Ngày tạo</span><strong>{{ $task->created_at?->format('d/m/Y H:i') ?? '—' }}</strong></div><div><span>Cập nhật gần nhất</span><strong>{{ $task->updated_at?->format('d/m/Y H:i') ?? '—' }}</strong></div></div></section>
        <section class="stitch-panel task-detail-panel task-content-panel"><div class="task-panel-heading"><div><h2>Nội dung công việc</h2><p>Mục tiêu và thông tin bổ sung</p></div></div><div class="task-detail-block"><h3>Mô tả</h3><div class="task-notice">{{ $task->description ?: 'Chưa có mô tả công việc.' }}</div></div><div class="task-detail-block"><h3>Ghi chú</h3><div class="task-notice">{{ $task->notes ?: 'Chưa có ghi chú.' }}</div></div></section>
    </div>
</div>
@endsection
