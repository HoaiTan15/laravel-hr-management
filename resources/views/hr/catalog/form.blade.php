@extends('layouts.hr')

@section('title', $title . ' · HUIT HRMS')
@section('content')
<div class="stitch-page">
    <div class="stitch-context">HR / {{ $catalogType === 'departments' ? 'Phòng ban' : 'Chức vụ' }} / {{ $title }}</div>
    <div class="stitch-form-heading"><h1>{{ $title }}</h1><p class="stitch-subtitle">Cập nhật danh mục dùng chung cho hồ sơ nhân sự.</p></div>
    <section class="stitch-panel stitch-form-panel">
        <form class="stitch-form" method="POST" action="{{ $formAction }}">
            @csrf
            @if($formMethod !== 'POST') @method($formMethod) @endif
            <div class="form-grid form-grid-2">
                <div class="form-group"><label for="name">Tên {{ $catalogType === 'departments' ? 'phòng ban' : 'chức vụ' }} *</label><input class="input" id="name" name="name" value="{{ old('name', $record->name) }}" required></div>
                <div class="form-group form-group-check"><label for="is_active">Trạng thái</label><label class="check-field"><input id="is_active" type="checkbox" name="is_active" value="1" @checked(old('is_active', $record->is_active))> <span>Đang hoạt động</span></label></div>
            </div>
            <div class="form-group"><label for="description">Mô tả</label><textarea class="input textarea" id="description" name="description" placeholder="Mô tả phạm vi, chức năng hoặc trách nhiệm...">{{ old('description', $record->description) }}</textarea></div>
            <div class="form-actions"><button class="button" type="submit">Lưu thay đổi</button><a class="button button-light" href="{{ route('hr.'.$catalogType) }}">Hủy</a></div>
        </form>
    </section>
</div>
@endsection
