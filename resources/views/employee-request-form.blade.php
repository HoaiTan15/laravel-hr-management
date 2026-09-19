@extends('hrms-layout')

@section('content')
@php($payload = $requestModel?->payload ?? [])
<div class="page">
    <div class="employee-crumb"><span>HỆ THỐNG NHÂN SỰ</span><b>/</b><strong>{{ $requestModel ? 'Chỉnh sửa phiếu yêu cầu' : 'Tạo phiếu yêu cầu' }}</strong></div>
    <h1>{{ $requestModel ? 'Chỉnh sửa Phiếu yêu cầu' : 'Tạo Phiếu yêu cầu hỗ trợ' }}</h1>
    <p class="subtitle">Gửi PYC hỗ trợ theo nghiệp vụ được quy định.</p>
    <div class="card" style="max-width:850px">
        <form method="POST" action="{{ $requestModel ? route('employee.requests.update', $requestModel) : route('employee.requests.store') }}">
            @csrf
            @if($requestModel) @method('PUT') @endif
            @php($selectedType = old('type', $defaultType ?: ($requestModel?->type->value ?? 'hardware')))
            <div class="form-group"><label>Loại PYC *</label><select class="select" name="type">
                <option value="hardware" @selected($selectedType === 'hardware')>Phần cứng</option>
                <option value="software" @selected($selectedType === 'software')>Phần mềm</option>
                <option value="account" @selected($selectedType === 'account')>Account</option>
                <option value="other" @selected($selectedType === 'other')>Khác</option>
            </select></div>
            <div class="form-group"><label>Tiêu đề *</label><input class="input" name="title" maxlength="120" required value="{{ old('title', $payload['title'] ?? '') }}"></div>
            <div class="form-group"><label>Nội dung *</label><textarea class="input textarea" name="content" maxlength="5000" required>{{ old('content', $payload['content'] ?? '') }}</textarea></div>
            <div style="display:flex;gap:10px"><a class="button light" href="{{ route('employee.requests.index') }}">Hủy bỏ</a><button class="button" type="submit">{{ $requestModel ? 'Lưu cập nhật PYC' : 'Gửi PYC' }}</button></div>
        </form>
    </div>
</div>
@endsection
