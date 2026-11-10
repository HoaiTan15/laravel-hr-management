@extends('layouts.hr')

@section('content')
@php($vehicleLabels = ['motorbike' => 'Xe máy', 'car' => 'Ô tô'])
<div class="stitch-page parking-page">
    <div class="hr-module-heading"><div><div class="stitch-context">HR / PHÚC LỢI NHÂN VIÊN</div><h1>Quản lý gửi xe</h1><p>Duyệt đăng ký, quản lý phương tiện và chi phí công ty hỗ trợ.</p></div><span class="parking-heading-icon material-symbols-outlined">local_parking</span></div>
    <div class="parking-kpis">
        @foreach([['Xe có hiệu lực', $activeCount, 'local_parking'], ['Yêu cầu chờ duyệt', $pendingCount, 'pending_actions'], ['Vé tính phí tháng '.$month->format('m/Y'), $ticketCount, 'confirmation_number'], ['Công ty hỗ trợ tháng '.$month->format('m/Y'), number_format($totalCost, 0, ',', '.').' đ', 'payments']] as [$label, $value, $icon])<article class="stitch-summary-card"><div class="stitch-summary-head"><span>{{ $label }}</span><span class="material-symbols-outlined">{{ $icon }}</span></div><div class="stitch-summary-value">{{ $value }}</div></article>@endforeach
    </div>
    <div class="parking-benefit"><span class="material-symbols-outlined">business</span><div><strong>Phúc lợi do công ty chi trả</strong><p>Phí được ghi nhận trọn tháng, một khoản cho mỗi nhân viên. Đổi xe trong tháng không tạo phí lần hai. Không khấu trừ vào lương.</p></div></div>
    <form class="parking-filters" method="GET" action="{{ route('hr.parking') }}"><input class="input" name="q" value="{{ request('q') }}" placeholder="Biển số, mã hoặc tên nhân viên" aria-label="Tìm xe hoặc nhân viên"><select class="select" name="status" aria-label="Trạng thái yêu cầu"><option value="">Tất cả yêu cầu</option>@foreach(['pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'rejected' => 'Từ chối', 'cancelled' => 'Đã hủy'] as $key => $label)<option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>@endforeach</select><input class="input" type="month" name="month" min="2000-01" max="{{ now()->format('Y-m') }}" value="{{ $month->format('Y-m') }}" aria-label="Tháng báo cáo chi phí"><button class="button" type="submit">Áp dụng</button></form>
    <section class="stitch-panel parking-history"><div class="parking-card-heading"><span class="material-symbols-outlined">fact_check</span><h2>Yêu cầu và lịch sử xử lý</h2></div>
        <div class="parking-request-list">@forelse($requests as $record)<article class="parking-request">
            <div class="parking-request-top"><div><strong>{{ $record->employee->full_name }}</strong><small>{{ $record->employee->employee_code }} · {{ $record->employee->department?->name }}</small></div><span class="stitch-status-pill {{ $record->status === 'approved' ? 'completed' : ($record->status === 'pending' ? 'pending' : 'rejected') }}">{{ $record->statusLabel() }}</span></div>
            <p><strong>{{ $record->typeLabel() }} · {{ $record->plate }}</strong> · {{ $vehicleLabels[$record->vehicle_type] ?? '' }} · {{ $record->vehicle_description }}</p>
            @if($record->type === 'change')<p class="muted">Đổi từ xe {{ $record->permit?->plate }}. Xe cũ giữ hiệu lực đến khi duyệt.</p>@endif
            <p class="parking-request-reason">{{ $record->reason }}</p><p class="muted">Gửi lúc {{ $record->created_at->format('d/m/Y H:i') }}</p>
            @if($record->status === 'pending')
            @php($isFailedRequest = (string) old('_parking_request_id') === (string) $record->id)
            @php($defaultMonthlyCost = $record->permit?->monthly_cost ?? config('parking.monthly_cost'))
            <form method="POST" action="{{ route('hr.parking.review', $record) }}" data-parking-review>@csrf @method('PATCH')
                @if($isFailedRequest && $errors->any())<p class="notice alert-error" role="alert">{{ $errors->first() }}</p>@endif
                <div class="form-grid form-grid-2">@if($record->type !== 'stop')<div class="form-group"><label for="cost-{{ $record->id }}">Chi phí công ty / tháng (đồng) *</label><input class="input" id="cost-{{ $record->id }}" name="monthly_cost" type="number" min="0" max="100000000" step="1" value="{{ $isFailedRequest ? (old('monthly_cost') ?? $defaultMonthlyCost) : $defaultMonthlyCost }}" placeholder="Nhập phí theo vé của bãi xe" aria-describedby="cost-help-{{ $record->id }}"><small id="cost-help-{{ $record->id }}" class="muted">Đã điền sẵn mức phí {{ $record->type === 'change' ? 'hiện tại' : 'mặc định của bãi xe' }}: {{ number_format((float) $defaultMonthlyCost, 0, ',', '.') }} đ/tháng. HR có thể chỉnh trước khi duyệt; công ty chi trả toàn bộ.</small></div>@endif<div class="form-group"><label for="note-{{ $record->id }}">Ghi chú xử lý (bắt buộc khi từ chối)</label><input class="input" id="note-{{ $record->id }}" name="review_note" maxlength="1000" value="{{ $isFailedRequest ? old('review_note') : '' }}"></div></div>
                <p class="notice alert-error" data-review-error role="alert" hidden></p>
                <div class="parking-actions"><button class="button" name="status" value="approved" type="submit"><span class="material-symbols-outlined">check</span>Duyệt yêu cầu</button><button class="button light" name="status" value="rejected" type="submit">Từ chối</button></div>
            </form>
            @else<p class="parking-note">{{ $record->reviewer?->name ?? 'Nhân viên' }} · {{ $record->reviewed_at?->format('d/m/Y H:i') ?? $record->updated_at->format('d/m/Y H:i') }}<br>{{ $record->review_note }}</p>@endif
        </article>@empty<div class="stitch-empty">Không có yêu cầu phù hợp.</div>@endforelse</div>{{ $requests->links() }}
    </section>
    <section class="stitch-panel parking-history"><div class="parking-card-heading"><span class="material-symbols-outlined">two_wheeler</span><h2>Danh sách xe có hiệu lực</h2></div><div class="stitch-table-wrap"><table class="stitch-table"><thead><tr><th>Nhân viên</th><th>Biển số / phương tiện</th><th>Phí công ty / tháng</th><th>Bắt đầu</th><th>Kết thúc quyền gửi xe</th></tr></thead><tbody>@forelse($permits as $permit)<tr><td><strong>{{ $permit->employee->full_name }}</strong><br><small>{{ $permit->employee->employee_code }} · {{ $permit->employee->department?->name }}</small></td><td><strong>{{ $permit->plate }}</strong><br><small>{{ $permit->vehicle_description }}</small></td><td>{{ number_format((float) $permit->monthly_cost, 0, ',', '.') }} đ</td><td>{{ $permit->starts_at->format('d/m/Y') }}</td><td><form method="POST" action="{{ route('hr.parking.end', $permit) }}" class="parking-end-form">@csrf<input class="input" name="reason" maxlength="1000" required placeholder="Lý do kết thúc" aria-label="Lý do kết thúc xe {{ $permit->plate }}"><button class="button light" type="submit">Kết thúc</button></form></td></tr>@empty<tr><td colspan="5" class="stitch-empty">Chưa có xe đang hoạt động.</td></tr>@endforelse</tbody></table></div>{{ $permits->links() }}</section>
    <section class="stitch-panel parking-history"><div class="parking-card-heading"><span class="material-symbols-outlined">receipt_long</span><h2>Chi phí công ty hỗ trợ · {{ $month->format('m/Y') }}</h2></div><p class="muted parking-report-note">Theo dõi chi phí vé tháng, không phải xác nhận đã thanh toán. Mức phí đã ghi nhận trong tháng được giữ nguyên khi đổi xe hoặc ngừng gửi.</p><div class="stitch-table-wrap"><table class="stitch-table"><thead><tr><th>Nhân viên</th><th>Phòng ban</th><th>Xe ghi nhận đầu kỳ</th><th>Công ty chi trả</th><th>Nhân viên chi trả</th></tr></thead><tbody>@forelse($costs as $cost)<tr><td>{{ $cost->employee->full_name }}<br><small>{{ $cost->employee->employee_code }}</small></td><td>{{ $cost->employee->department?->name ?? '—' }}</td><td>{{ $cost->permit->plate }}</td><td><strong>{{ number_format((float) $cost->amount, 0, ',', '.') }} đ</strong></td><td>0 đ</td></tr>@empty<tr><td colspan="5" class="stitch-empty">Chưa phát sinh chi phí trong tháng này.</td></tr>@endforelse</tbody></table></div>{{ $costs->links() }}</section>
</div>
<script>
document.querySelectorAll('form[data-parking-review]').forEach(form => {
    form.addEventListener('submit', event => {
        const approving = event.submitter?.value === 'approved';
        const cost = form.querySelector('[name="monthly_cost"]');
        const note = form.querySelector('[name="review_note"]');
        const error = form.querySelector('[data-review-error]');
        const missing = approving && cost && !cost.value.trim() ? cost : (!approving && !note.value.trim() ? note : null);
        if (missing) {
            event.preventDefault();
            error.hidden = false;
            error.textContent = missing === cost
                ? 'Nhập chi phí công ty trả cho bãi xe mỗi tháng trước khi duyệt. Nhập 0 nếu bãi xe không thu phí.'
                : 'Nhập lý do trước khi từ chối yêu cầu.';
            missing.focus();
        } else {
            error.hidden = true;
        }
    });
});
</script>
@endsection
