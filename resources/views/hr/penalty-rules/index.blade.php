@extends('layouts.hr')

@section('content')
<div class="stitch-page penalty-page">
    <div class="stitch-context">HR / Cấu hình tính lương / Tiền phạt</div>
    <div class="stitch-context-row penalty-heading">
        <div><h1>Cấu hình tiền phạt</h1><p class="stitch-subtitle">Quản lý các mức phạt theo nội quy và phạm vi áp dụng trong công ty.</p></div>
        <a class="button" href="{{ route('hr.penalty-rules.create') }}"><span class="material-symbols-outlined">add</span>Thêm cấu hình</a>
    </div>
    <div class="stitch-summary-grid">
        <article class="stitch-summary-card"><div class="stitch-summary-head"><span>Tổng cấu hình</span><div class="stitch-summary-icon primary"><span class="material-symbols-outlined">rule</span></div></div><div class="stitch-summary-value">{{ $totalRules }}</div><span class="stitch-summary-caption">Quy định tiền phạt đã thiết lập</span></article>
        <article class="stitch-summary-card"><div class="stitch-summary-head"><span>Đang bật</span><div class="stitch-summary-icon secondary"><span class="material-symbols-outlined">check_circle</span></div></div><div class="stitch-summary-value">{{ $activeRules }}</div><span class="stitch-summary-caption">{{ $totalRules - $activeRules }} cấu hình đang tắt</span></article>
        <article class="stitch-summary-card"><div class="stitch-summary-head"><span>Toàn công ty</span><div class="stitch-summary-icon primary"><span class="material-symbols-outlined">corporate_fare</span></div></div><div class="stitch-summary-value">{{ $companyRules }}</div><span class="stitch-summary-caption">Phạm vi bao gồm tất cả nhân viên</span></article>
    </div>
    <section class="stitch-panel penalty-panel" aria-label="Danh sách cấu hình tiền phạt">
        <div class="stitch-panel-head"><div><h2>Danh sách cấu hình</h2><p class="penalty-muted">Thiết lập theo toàn công ty, phòng ban hoặc từng nhân viên.</p></div><span class="penalty-result-count">{{ $rules->total() }} kết quả</span></div>
        <form class="penalty-filters" method="GET" action="{{ route('hr.penalty-rules.index') }}">
            <div class="form-group penalty-search"><label for="search">Tìm cấu hình</label><input class="input" id="search" name="search" maxlength="150" value="{{ request('search') }}" placeholder="Nhập tên cấu hình..."></div>
            <div class="form-group"><label for="filter-type">Loại vi phạm</label><select class="select" id="filter-type" name="type"><option value="">Tất cả loại vi phạm</option>@foreach($types as $value => $label)<option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>@endforeach</select></div>
            <div class="form-group"><label for="filter-department">Phòng ban</label><select class="select" id="filter-department" name="department_id"><option value="">Tất cả phòng ban</option>@foreach($departments as $department)<option value="{{ $department->id }}" @selected((string) request('department_id') === (string) $department->id)>{{ $department->name }}</option>@endforeach</select></div>
            <div class="form-group"><label for="filter-status">Trạng thái</label><select class="select" id="filter-status" name="status"><option value="">Tất cả trạng thái</option><option value="active" @selected(request('status') === 'active')>Đang bật</option><option value="inactive" @selected(request('status') === 'inactive')>Đang tắt</option></select></div>
            <div class="form-group penalty-page-size"><label for="per-page">Số dòng</label><select class="select" id="per-page" name="per_page">@foreach([10, 20, 50] as $size)<option value="{{ $size }}" @selected((int) request('per_page', 20) === $size)>{{ $size }} / trang</option>@endforeach</select></div>
            <div class="penalty-filter-actions"><button class="button" type="submit">Lọc</button><a class="button button-light" href="{{ route('hr.penalty-rules.index') }}" aria-label="Đặt lại bộ lọc"><span class="material-symbols-outlined">restart_alt</span></a></div>
        </form>
        @if(request('department_id'))<p class="penalty-filter-hint">Bao gồm cấu hình toàn công ty và cấu hình áp dụng cho phòng ban hoặc nhân viên thuộc phòng ban đã chọn.</p>@endif
        <div class="stitch-table-wrap"><table class="stitch-table penalty-table">
            <thead><tr><th scope="col">#</th><th scope="col">Cấu hình / Loại vi phạm</th><th scope="col">Mức phạt mỗi lần</th><th scope="col">Phạm vi áp dụng</th><th scope="col">Trạng thái</th><th scope="col">Thao tác</th></tr></thead>
            <tbody>
            @forelse($rules as $rule)
                <tr>
                    <td>{{ $rules->firstItem() + $loop->index }}</td>
                    <td class="penalty-name"><strong>{{ $rule->name }}</strong><span class="penalty-muted">{{ $types[$rule->type] }}</span>@if($rule->description)<small class="penalty-description" title="{{ $rule->description }}">{{ Str::limit($rule->description, 100) }}</small>@endif</td>
                    <td><strong class="penalty-amount">{{ number_format((float) $rule->amount, 0, ',', '.') }} <small>đ</small></strong><span class="penalty-muted">Mỗi lần vi phạm</span></td>
                    <td class="penalty-scope"><span class="penalty-scope-label"><span class="material-symbols-outlined">{{ ['company' => 'corporate_fare', 'departments' => 'domain', 'employees' => 'person'][$rule->scope] }}</span>{{ $scopes[$rule->scope] }}</span>
                        @if($rule->scope === 'company')<span class="penalty-muted">Tất cả nhân viên</span>
                        @else
                            @php($targets = $rule->scope === 'departments' ? $rule->departments->pluck('name') : $rule->employees->map(fn ($person) => $person->full_name.' · '.$person->employee_code))
                            <span class="penalty-muted">{{ $targets->take(2)->implode(', ') }}</span>
                            @if($targets->count() > 2)<details class="penalty-more"><summary>Xem thêm {{ $targets->count() - 2 }}</summary><span>{{ $targets->slice(2)->implode(', ') }}</span></details>@endif
                        @endif
                    </td>
                    <td><span class="penalty-status {{ $rule->is_active ? 'is-active' : '' }}"><span></span>{{ $rule->is_active ? 'Đang bật' : 'Đang tắt' }}</span></td>
                    <td><div class="penalty-row-actions"><a class="penalty-icon-button" href="{{ route('hr.penalty-rules.edit', $rule) }}" aria-label="Sửa {{ $rule->name }}" title="Chỉnh sửa"><span class="material-symbols-outlined">edit</span></a><form method="POST" action="{{ route('hr.penalty-rules.destroy', $rule) }}" data-delete-penalty="{{ $rule->name }}">@csrf @method('DELETE')<button class="penalty-icon-button is-danger" type="submit" aria-label="Xóa {{ $rule->name }}" title="Xóa cấu hình"><span class="material-symbols-outlined">delete</span></button></form></div></td>
                </tr>
            @empty
                <tr><td colspan="6"><div class="penalty-empty"><span class="material-symbols-outlined">{{ $totalRules ? 'search_off' : 'rule' }}</span><h3>{{ $totalRules ? 'Không tìm thấy cấu hình' : 'Chưa có cấu hình tiền phạt' }}</h3><p>{{ $totalRules ? 'Thử thay đổi hoặc đặt lại bộ lọc để xem kết quả.' : 'Thêm cấu hình đầu tiên theo nội quy của công ty.' }}</p><a class="button button-light" href="{{ $totalRules ? route('hr.penalty-rules.index') : route('hr.penalty-rules.create') }}">{{ $totalRules ? 'Đặt lại bộ lọc' : 'Thêm cấu hình' }}</a></div></td></tr>
            @endforelse
            </tbody>
        </table></div>
        <div class="penalty-footer"><span>Hiển thị {{ $rules->firstItem() ?? 0 }}–{{ $rules->lastItem() ?? 0 }} / {{ $rules->total() }} cấu hình</span><nav class="penalty-pagination" aria-label="Phân trang cấu hình">@if($rules->onFirstPage())<span class="penalty-icon-button is-disabled" aria-disabled="true"><span class="material-symbols-outlined">chevron_left</span></span>@else<a class="penalty-icon-button" href="{{ $rules->previousPageUrl() }}" aria-label="Trang trước"><span class="material-symbols-outlined">chevron_left</span></a>@endif<span>Trang {{ $rules->currentPage() }} / {{ $rules->lastPage() }}</span>@if($rules->hasMorePages())<a class="penalty-icon-button" href="{{ $rules->nextPageUrl() }}" aria-label="Trang sau"><span class="material-symbols-outlined">chevron_right</span></a>@else<span class="penalty-icon-button is-disabled" aria-disabled="true"><span class="material-symbols-outlined">chevron_right</span></span>@endif</nav></div>
    </section>
    <p class="penalty-page-note"><span class="material-symbols-outlined">info</span>Cấu hình đang bật được áp dụng vào lương nháp: tự tính đi muộn, về sớm và quên chấm công ra; các vi phạm khác do HR ghi nhận. Đi muộn sau 08:30; về sớm trước {{ config('payroll.workday_end') }}.</p>
</div>
@if($editingRule)
    @include('hr.penalty-rules.form')
@endif
<script>
    document.querySelectorAll('[data-delete-penalty]').forEach(form => {
        form.addEventListener('submit', event => {
            if (!window.confirm(`Xóa cấu hình “${form.dataset.deletePenalty}”? Thao tác này không thể hoàn tác.`)) {
                event.preventDefault();
            }
        });
    });
</script>
@endsection

