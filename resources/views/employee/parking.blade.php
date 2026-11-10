@extends('layouts.employee')

@section('content')
@php($vehicleLabels = ['motorbike' => 'Xe máy', 'car' => 'Ô tô'])
<div class="stitch-page parking-page">
    <div class="hr-module-heading"><div><div class="stitch-context">NHÂN VIÊN / PHÚC LỢI</div><h1>Xe của tôi</h1><p>Đăng ký phương tiện và theo dõi quyền gửi xe tại công ty.</p></div><span class="parking-heading-icon material-symbols-outlined">two_wheeler</span></div>
    <div class="parking-benefit"><span class="material-symbols-outlined">verified_user</span><div><strong>Công ty hỗ trợ 100% phí gửi xe</strong><p>Bạn không phải thanh toán hoặc bị khấu trừ vào lương. Mỗi nhân viên được một xe có hiệu lực.</p></div></div>
    <div class="parking-columns">
        <section class="stitch-panel parking-card">
            <div class="parking-card-heading"><span class="material-symbols-outlined">local_parking</span><h2>Xe đang có hiệu lực</h2></div>
            @if($permit)
                <div class="parking-plate">{{ $permit->plate }}</div>
                <div class="parking-vehicle-meta"><span class="stitch-status-pill completed">Được phép gửi xe</span><span>{{ $vehicleLabels[$permit->vehicle_type] }}</span></div>
                <p>{{ $permit->vehicle_description }}</p><p class="muted">Có hiệu lực từ {{ $permit->starts_at->format('d/m/Y H:i') }}</p>
                <div class="parking-note">Khi đổi xe, xe hiện tại vẫn có hiệu lực cho đến khi HR duyệt yêu cầu mới.</div>
            @else
                <div class="parking-empty"><span class="material-symbols-outlined">directions_bike</span><h3>Chưa có xe được duyệt</h3><p>Gửi thông tin xe để HR đăng ký quyền gửi xe cho bạn.</p></div>
            @endif
        </section>
        <section class="stitch-panel parking-card">
            <div class="parking-card-heading"><span class="material-symbols-outlined">edit_note</span><h2>{{ $permit ? 'Thay đổi đăng ký' : 'Đăng ký gửi xe' }}</h2></div>
            @if($pending)
                <div class="parking-note">Bạn đang có yêu cầu chờ HR xử lý. Có thể hủy yêu cầu trong lịch sử bên dưới nếu cần gửi lại.</div>
            @else
                <form method="POST" action="{{ route('employee.parking.store') }}">
                    @csrf
                    <div class="form-group"><label for="parking-type">Loại yêu cầu</label><select class="select" name="type" id="parking-type">@if($permit)<option value="change" @selected(old('type') === 'change')>Đổi xe</option><option value="stop" @selected(old('type') === 'stop')>Ngừng gửi xe</option>@else<option value="register">Đăng ký xe</option>@endif</select></div>
                    <fieldset id="parking-vehicle-fields" style="border:0;padding:0;margin:0">
                        <div class="form-grid form-grid-2"><div class="form-group"><label for="parking-plate">Biển số xe *</label><input class="input" name="plate" id="parking-plate" maxlength="25" placeholder="59A1-123.45" value="{{ old('plate') }}" required></div><div class="form-group"><label for="parking-vehicle-type">Loại xe *</label><select class="select" name="vehicle_type" id="parking-vehicle-type">@foreach($vehicleLabels as $value => $label)<option value="{{ $value }}" @selected(old('vehicle_type') === $value)>{{ $label }}</option>@endforeach</select></div></div>
                        <div class="form-group"><label for="parking-description">Hãng xe / màu xe *</label><input class="input" name="vehicle_description" id="parking-description" maxlength="150" placeholder="Honda Vision, màu trắng" value="{{ old('vehicle_description') }}" required></div>
                    </fieldset>
                    <div class="form-group"><label for="parking-reason">Ghi chú / lý do *</label><textarea class="input textarea" name="reason" id="parking-reason" maxlength="1000" required placeholder="Thông tin để HR kiểm tra đăng ký">{{ old('reason') }}</textarea></div>
                    <p class="muted">Yêu cầu có hiệu lực ngay khi được HR duyệt.</p>
                    <button class="button" type="submit"><span class="material-symbols-outlined">send</span>Gửi yêu cầu</button>
                </form>
            @endif
        </section>
    </div>
    <section class="stitch-panel parking-history"><div class="parking-card-heading"><span class="material-symbols-outlined">history</span><h2>Lịch sử yêu cầu</h2></div>
        <div class="stitch-table-wrap"><table class="stitch-table"><thead><tr><th>Yêu cầu</th><th>Phương tiện</th><th>Ngày gửi</th><th>Trạng thái</th><th>Xử lý của HR</th><th></th></tr></thead><tbody>
        @forelse($requests as $record)<tr><td><strong>{{ $record->typeLabel() }}</strong><br><small>{{ $record->reason }}</small></td><td>{{ $record->plate }}<br><small>{{ $record->vehicle_description }}</small></td><td>{{ $record->created_at->format('d/m/Y H:i') }}</td><td><span class="stitch-status-pill {{ $record->status === 'approved' ? 'completed' : ($record->status === 'pending' ? 'pending' : 'rejected') }}">{{ $record->statusLabel() }}</span></td><td>{{ $record->reviewer?->name ?? '—' }}<br><small>{{ $record->review_note }} {{ $record->reviewed_at?->format('d/m/Y H:i') }}</small></td><td>@if($record->status === 'pending')<form method="POST" action="{{ route('employee.parking.cancel', $record) }}">@csrf @method('PATCH')<button class="button light" type="submit">Hủy yêu cầu</button></form>@endif</td></tr>@empty<tr><td colspan="6" class="stitch-empty">Chưa có yêu cầu gửi xe.</td></tr>@endforelse
        </tbody></table></div>{{ $requests->links() }}
    </section>
    <section class="stitch-panel parking-history"><div class="parking-card-heading"><span class="material-symbols-outlined">directions_car</span><h2>Lịch sử xe đã đăng ký</h2></div><div class="stitch-table-wrap"><table class="stitch-table"><thead><tr><th>Biển số</th><th>Phương tiện</th><th>Bắt đầu</th><th>Kết thúc</th><th>Lý do kết thúc</th></tr></thead><tbody>@forelse($permits as $oldPermit)<tr><td><strong>{{ $oldPermit->plate }}</strong></td><td>{{ $oldPermit->vehicle_description }}</td><td>{{ $oldPermit->starts_at->format('d/m/Y H:i') }}</td><td>{{ $oldPermit->ends_at?->format('d/m/Y H:i') ?? 'Đang có hiệu lực' }}</td><td>{{ $oldPermit->end_reason ?? '—' }}</td></tr>@empty<tr><td colspan="5" class="stitch-empty">Chưa có lịch sử xe.</td></tr>@endforelse</tbody></table></div></section>
</div>
<script>
(() => {
    const type = document.getElementById('parking-type');
    const fields = document.getElementById('parking-vehicle-fields');
    if (!type || !fields) return;
    const update = () => { fields.disabled = type.value === 'stop'; fields.hidden = fields.disabled; };
    type.addEventListener('change', update);
    update();
})();
</script>
@endsection
