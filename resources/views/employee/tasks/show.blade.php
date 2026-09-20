@extends('layouts.employee')

@section('content')
<div class="page">
    <div class="employee-crumb"><span>HỆ THỐNG NHÂN SỰ</span><b>/</b><strong>Chi tiết công việc</strong></div>
    <h1>{{ $task->title }}</h1>
    <p class="subtitle">Cập nhật tiến độ nhiệm vụ được giao.</p>
    @if(session('status'))<div class="notice">✓ {{ session('status') }}</div>@endif

    <div class="grid grid-main-side" style="margin-top:20px">
        <div class="card">
            <div class="section-head">
                <div>
                    <h2>Nội dung & mô tả công việc</h2>
                    <p>Người giao: {{ $task->creator->name ?? 'HR' }}</p>
                </div>
                <span class="badge">#TASK-{{ str_pad($task->id,4,'0',STR_PAD_LEFT) }}</span>
            </div>
            <p style="line-height:1.8">{{ $task->description ?: 'Không có mô tả chi tiết.' }}</p>
            <div class="notice">Hạn hoàn thành: <strong>{{ $task->due_at?->format('d/m/Y H:i') ?? 'Không có hạn' }}</strong></div>
            <h2 style="margin-top:25px">Ghi chú tiến độ</h2>
            <div class="audit">{{ $task->notes ?: 'Chưa có ghi chú.' }}</div>
        </div>

        <div class="card right-card">
            <h2>Cập nhật tiến độ</h2>
            <p class="muted">Chọn trạng thái mới và ghi chú kết quả khi cần.</p>
            <form method="POST" action="{{ route('employee.tasks.status',$task) }}">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="status">Trạng thái</label>
                    <select class="select" id="status" name="status" style="width:100%">
                        <option value="in_progress" @selected($task->status->value === 'in_progress')>Đang thực hiện</option>
                        <option value="completed" @selected($task->status->value === 'completed')>Hoàn thành</option>
                        <option value="stopped" @selected($task->status->value === 'stopped')>Dừng thực hiện</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="notes">Ghi chú tiến độ</label>
                    <textarea class="input textarea" id="notes" name="notes" maxlength="5000">{{ old('notes', $task->notes) }}</textarea>
                </div>
                <button class="button full" type="submit">Lưu tiến độ</button>
            </form>
            <a class="button light full" style="margin-top:10px" href="{{ route('employee.tasks.index') }}">Quay lại danh sách</a>
        </div>
    </div>
</div>
@endsection
