@extends('layouts.employee')

@section('content')
@php($payload = $requestModel->payload ?? [])
<div class="page">
    <div class="employee-crumb"><span>HỆ THỐNG NHÂN SỰ</span><b>/</b><strong>Chi tiết phiếu yêu cầu</strong></div>
    <h1>#PYC-{{ str_pad($requestModel->id, 4, '0', STR_PAD_LEFT) }}: {{ $payload['title'] ?? 'Phiếu yêu cầu' }}</h1>
    <p class="subtitle">Theo dõi tiến độ xử lý và lịch sử phản hồi.</p>
    @if(session('status'))<div class="notice">✓ {{ session('status') }}</div>@endif
    <div class="grid grid-main-side">
        <div class="card">
            <div class="section-head"><h2>Nội dung yêu cầu</h2><span class="badge">{{ $requestModel->type->value }}</span></div>
            @if($requestModel->type->value === 'profile_change')
                @php($changeLabels = ['date_of_birth' => 'Ngày sinh', 'gender' => 'Giới tính', 'cccd' => 'CCCD'])
                @foreach(($payload['changes'] ?? []) as $field => $value)
                    <div class="row"><span class="row-label">{{ $changeLabels[$field] ?? $field }}</span><strong>{{ $value }}</strong></div>
                @endforeach
                <p style="line-height:1.8;margin-top:15px"><strong>Lý do:</strong> {{ $payload['reason'] ?? '—' }}</p>
            @else
                <p style="line-height:1.8">{{ $payload['content'] ?? '—' }}</p>
            @endif
            <h2 style="margin-top:25px">Lịch sử xử lý</h2>
            <div class="audit">{{ $requestModel->created_at?->format('d/m/Y H:i') }}　Đã tạo PYC</div>
            @if($requestModel->processed_at)<div class="audit">{{ $requestModel->processed_at->format('d/m/Y H:i') }}　Đã xử lý bởi {{ $requestModel->processor->name ?? 'Admin' }}: {{ $requestModel->processing_note }}</div>@endif
        </div>
        <div class="card right-card">
            <h2>Trạng thái</h2>
            <p style="margin-top:15px"><span class="badge {{ $requestModel->status->value === 'pending' ? 'orange' : ($requestModel->status->value === 'rejected' ? 'red' : 'green') }}">{{ $requestModel->status->value }}</span></p>
            @if($requestModel->status->value === 'pending')
                @if($requestModel->type->value === 'profile_change')
                    <a class="button light full" href="{{ route('employee.profile', ['change' => 1, 'change_request' => $requestModel->id]) }}">Chỉnh sửa PYC</a>
                @else
                    <a class="button light full" href="{{ route('employee.requests.edit', $requestModel) }}">Chỉnh sửa PYC</a>
                @endif
                <form method="POST" action="{{ route('employee.requests.destroy', $requestModel) }}" style="margin-top:10px">@csrf @method('DELETE')<button class="button danger full" type="submit">Hủy PYC</button></form>
            @endif
            <a class="button light full" style="margin-top:10px" href="{{ route('employee.requests.index') }}">Quay lại danh sách</a>
        </div>
    </div>
</div>
@endsection
