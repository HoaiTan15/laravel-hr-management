@php
    $selectedDepartments = collect(session()->hasOldInput() ? old('department_ids', []) : $editingRule->departments->modelKeys())->map(fn ($id) => (string) $id)->all();
    $selectedEmployees = collect(session()->hasOldInput() ? old('employee_ids', []) : $editingRule->employees->modelKeys())->map(fn ($id) => (string) $id)->all();
    $selectedScope = old('scope', $editingRule->scope);
@endphp
<dialog class="penalty-dialog" id="penalty-dialog" aria-labelledby="penalty-dialog-title" open>
    <div class="penalty-dialog-heading"><div><span class="penalty-eyebrow">CẤU HÌNH TÍNH LƯƠNG</span><h2 id="penalty-dialog-title">{{ $editingRule->exists ? 'Chỉnh sửa cấu hình tiền phạt' : 'Thêm cấu hình tiền phạt' }}</h2><p>Thiết lập mức tiền và đối tượng theo nội quy công ty.</p></div><a class="penalty-icon-button" href="{{ route('hr.penalty-rules.index') }}" aria-label="Đóng"><span class="material-symbols-outlined">close</span></a></div>
    <form class="stitch-form penalty-form" method="POST" action="{{ $editingRule->exists ? route('hr.penalty-rules.update', $editingRule) : route('hr.penalty-rules.store') }}">
        @csrf
        @if($editingRule->exists) @method('PATCH') @endif
        @if($errors->any())<div class="notice alert-error" role="alert"><strong>Vui lòng kiểm tra lại thông tin.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <div class="form-group"><label for="rule-name">Tên cấu hình <span class="penalty-required">*</span></label><input class="input" id="rule-name" name="name" maxlength="150" value="{{ old('name', $editingRule->name) }}" placeholder="Ví dụ: Đi muộn theo nội quy công ty" required autofocus></div>
        <div class="form-grid form-grid-2">
            <div class="form-group"><label for="rule-type">Loại vi phạm <span class="penalty-required">*</span></label><select class="select" id="rule-type" name="type" required>@foreach($types as $value => $label)<option value="{{ $value }}" @selected(old('type', $editingRule->type) === $value)>{{ $label }}</option>@endforeach</select></div>
            <div class="form-group"><label for="rule-amount">Mức tiền mỗi lần (VNĐ) <span class="penalty-required">*</span></label><input class="input" id="rule-amount" name="amount" type="number" min="1" max="999999999" step="1" inputmode="numeric" value="{{ old('amount', $editingRule->amount) }}" placeholder="Ví dụ: 50000" required></div>
        </div>
        <fieldset class="penalty-scope-options"><legend>Phạm vi áp dụng <span class="penalty-required">*</span></legend>@foreach($scopes as $value => $label)<label><input type="radio" name="scope" value="{{ $value }}" @checked($selectedScope === $value) required><span>{{ $label }}</span></label>@endforeach</fieldset>
        <fieldset class="penalty-targets" data-scope-target="departments" @if($selectedScope !== 'departments') hidden disabled @endif><legend>Chọn phòng ban</legend><p class="penalty-muted">Áp dụng cho nhân viên thuộc các phòng ban được chọn.</p><div class="penalty-target-list">@forelse($departments as $department)<label class="penalty-target"><input type="checkbox" name="department_ids[]" value="{{ $department->id }}" @checked(in_array((string) $department->id, $selectedDepartments, true))><span>{{ $department->name }}@if(!$department->is_active)<small>Đang khóa</small>@endif</span></label>@empty<p class="penalty-muted">Chưa có phòng ban. Vui lòng tạo phòng ban trước.</p>@endforelse</div></fieldset>
        <fieldset class="penalty-targets" data-scope-target="employees" @if($selectedScope !== 'employees') hidden disabled @endif><legend>Chọn nhân viên</legend><label class="penalty-sr-only" for="employee-search">Tìm nhân viên theo tên, mã hoặc phòng ban</label><input class="input" id="employee-search" type="search" placeholder="Tìm tên, mã nhân viên hoặc phòng ban..." autocomplete="off"><div class="penalty-target-list">@forelse($employees as $person)<label class="penalty-target" data-employee-option><input type="checkbox" name="employee_ids[]" value="{{ $person->id }}" @checked(in_array((string) $person->id, $selectedEmployees, true))><span>{{ $person->full_name }}<small>{{ $person->employee_code }} · {{ $person->department?->name }}{{ $person->employment_status->value === 'terminated' ? ' · Đã nghỉ việc' : '' }}</small></span></label>@empty<p class="penalty-muted">Chưa có nhân viên. Vui lòng thêm hồ sơ nhân viên trước.</p>@endforelse<p class="penalty-muted" id="employee-no-results" hidden>Không tìm thấy nhân viên phù hợp.</p></div></fieldset>
        <div class="form-group"><label for="rule-description">Điều kiện áp dụng / Ghi chú</label><textarea class="input textarea" id="rule-description" name="description" maxlength="2000" rows="3" placeholder="Mô tả trường hợp áp dụng, ngoại lệ hoặc tham chiếu nội quy...">{{ old('description', $editingRule->description) }}</textarea></div>
        <div class="penalty-active-field"><div><strong>Trạng thái cấu hình</strong><p class="penalty-muted">Có thể tắt để giữ lại cấu hình và chỉnh sửa sau.</p></div><input type="hidden" name="is_active" value="0"><label class="check-field"><input type="checkbox" name="is_active" value="1" @checked((bool) old('is_active', $editingRule->is_active))><span>Đang bật</span></label></div>
        <div class="form-actions"><a class="button button-light" href="{{ route('hr.penalty-rules.index') }}">Hủy</a><button class="button" type="submit"><span class="material-symbols-outlined">save</span>{{ $editingRule->exists ? 'Lưu thay đổi' : 'Thêm cấu hình' }}</button></div>
    </form>
</dialog>
<script>
    (() => {
        const dialog = document.getElementById('penalty-dialog');
        const closeUrl = @json(route('hr.penalty-rules.index'));
        dialog.removeAttribute('open');
        dialog.showModal();
        dialog.addEventListener('cancel', event => {
            event.preventDefault();
            window.location.assign(closeUrl);
        });
        const updateScope = () => {
            const scope = dialog.querySelector('[name="scope"]:checked')?.value;
            dialog.querySelectorAll('[data-scope-target]').forEach(fieldset => {
                fieldset.hidden = fieldset.dataset.scopeTarget !== scope;
                fieldset.disabled = fieldset.hidden;
            });
        };
        dialog.querySelectorAll('[name="scope"]').forEach(input => input.addEventListener('change', updateScope));
        updateScope();
        const normalize = value => value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/đ/g, 'd').replace(/Đ/g, 'D').toLocaleLowerCase();
        dialog.querySelector('#employee-search').addEventListener('input', event => {
            const search = normalize(event.target.value.trim());
            let visibleCount = 0;
            dialog.querySelectorAll('[data-employee-option]').forEach(option => {
                option.hidden = !normalize(option.textContent).includes(search);
                if (!option.hidden) { visibleCount++; }
            });
            dialog.querySelector('#employee-no-results').hidden = visibleCount > 0;
        });
        dialog.querySelector('form').addEventListener('submit', event => {
            if (event.target.checkValidity()) {
                const button = event.target.querySelector('button[type="submit"]');
                button.disabled = true;
                button.textContent = 'Đang lưu...';
            }
        });
    })();
</script>
