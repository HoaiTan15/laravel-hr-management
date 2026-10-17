@extends('layouts.hr')
@section('title', $title . ' · HUIT HRMS')
@section('content')
@php($typeLabels = ['hardware' => 'Phần cứng', 'software' => 'Phần mềm', 'account' => 'Tài khoản', 'other' => 'Khác', 'profile_change' => 'Thay đổi hồ sơ'])
<div class="stitch-page">
    <div class="stitch-context">HR / Phiếu yêu cầu / Chi tiết</div>
    <div class="section-head">
        <div>
            <h1>#PYC-{{ str_pad($requestModel->id, 4, '0', STR_PAD_LEFT) }}</h1>
            <p class="stitch-subtitle">
                {{ $typeLabels[$requestModel->type?->value] ?? $requestModel->type?->value }} · {{ $requestModel->status?->value }}
            </p>
        </div>
        <a class="button button-light" href="{{ route('hr.requests') }}">Quay lại</a>
    </div>

    <section class="stitch-panel">
        <div class="stitch-key-list">
            <div><span>Người gửi</span><strong>{{ $requestModel->creator?->name ?? '—' }}</strong></div>
            <div><span>Ngày gửi</span><strong>{{ $requestModel->created_at?->format('d/m/Y H:i') }}</strong></div>
            <div><span>Trạng thái</span><strong>{{ $requestModel->status?->value }}</strong></div>
            <div><span>Người xử lý</span><strong>{{ $requestModel->processor?->name ?? 'Chưa xử lý' }}</strong></div>
        </div>

        <hr style="border:0;border-top:1px solid var(--line);margin:22px 0">
        <h2>{{ $requestModel->displayTitle() }}</h2>

        @forelse($requestModel->displayDetails() as $detail)
            <div class="form-group">
                <label>{{ $detail['label'] }}</label>
                <div class="notice">{{ $detail['value'] }}</div>
            </div>
        @empty
            <p class="muted">Phiếu chưa có nội dung chi tiết.</p>
        @endforelse

        @if($requestModel->processing_note)
            <div class="form-group">
                <label>Ghi chú xử lý</label>
                <div class="notice">{{ $requestModel->processing_note }}</div>
            </div>
        @endif

        <p class="muted">PYC được phê duyệt hoặc từ chối theo quyền xử lý của Admin.</p>
    </section>
</div>
@endsection
