@extends('layouts.admin')

@section('content')
<div class="stitch-page admin-page">
    <div class="hr-module-heading"><div><h1>Tọa độ chấm công</h1><p>Thiết lập nơi làm việc được phép check-in và check-out.</p></div><span class="material-symbols-outlined">location_on</span></div>
    <div class="attendance-location-grid">
    <aside class="stitch-panel attendance-map-panel" aria-label="Bản đồ tọa độ chấm công" data-default-lat="{{ $defaults['workplace_latitude'] }}" data-default-lng="{{ $defaults['workplace_longitude'] }}" data-default-radius="{{ $defaults['radius_meters'] }}">
        <div class="attendance-map-heading"><span class="attendance-map-icon material-symbols-outlined">map</span><div><h2>Vị trí nơi làm việc</h2><p>Xem trước tọa độ trên bản đồ</p></div></div>
        <div class="attendance-map-frame"><iframe id="location-map" title="Bản đồ vị trí chấm công" src="about:blank" loading="lazy" referrerpolicy="no-referrer"></iframe></div>
        <div class="attendance-map-details">
            <span class="attendance-map-label">TỌA ĐỘ XEM TRƯỚC</span>
            <strong id="map-coordinate-label">{{ $location['latitude'] }}, {{ $location['longitude'] }}</strong>
            <div class="attendance-map-radius"><span class="material-symbols-outlined">radar</span><span>Bán kính cho phép</span><strong id="map-radius-label">{{ $location['radius_meters'] }} m</strong></div>
            <p id="map-preview-status" role="status">Bản đồ cập nhật theo biểu mẫu. Bấm Lưu cấu hình để áp dụng.</p>
            <a id="map-open-link" class="attendance-map-link" href="https://www.openstreetmap.org/?mlat={{ $location['latitude'] }}&mlon={{ $location['longitude'] }}#map=16/{{ $location['latitude'] }}/{{ $location['longitude'] }}" target="_blank" rel="noopener noreferrer">Mở bản đồ lớn <span class="material-symbols-outlined">open_in_new</span></a>
        </div>
    </aside>
    <section class="stitch-panel attendance-location-form">
        <form class="admin-profile-form" method="POST" action="{{ route('admin.attendance-location.update') }}">
            @csrf @method('PUT')
            <div class="form-group">
                <label for="location-mode">Nguồn tọa độ</label>
                <select class="select" id="location-mode" name="mode">
                    <option value="server" @selected(old('mode', $useServerDefaults ? 'server' : 'custom') === 'server')>Dùng mặc định của server</option>
                    <option value="custom" @selected(old('mode', $useServerDefaults ? 'server' : 'custom') === 'custom')>Nhập tọa độ nơi làm việc</option>
                </select>
                <p class="muted">Mặc định: {{ $defaults['workplace_latitude'] }}, {{ $defaults['workplace_longitude'] }} · Bán kính {{ $defaults['radius_meters'] }} m. Đây là vị trí đã cấu hình trên server, không phải vị trí tự phát hiện.</p>
            </div>
            <div class="form-group">
                <button class="button light" id="detect-location" type="button"><span class="material-symbols-outlined">my_location</span>Lấy vị trí hiện tại của tôi</button>
                <p id="location-status" class="muted" role="status" aria-live="polite">Lấy vị trí của thiết bị đang mở trang này. Bạn cần cho phép trình duyệt truy cập vị trí và bấm Lưu cấu hình để áp dụng.</p>
            </div>
            <fieldset id="custom-location" style="border:0;padding:0;margin:0">
                <div class="form-group"><label for="coordinates">Dán tọa độ (vĩ độ, kinh độ)</label><input class="input" id="coordinates" name="coordinates" maxlength="100" placeholder="10.762622, 106.660172" value="{{ old('coordinates', $location['latitude'].', '.$location['longitude']) }}"><p class="muted">Trên Google Maps, nhấp chuột phải vào nơi làm việc rồi sao chép cặp tọa độ đầu tiên.</p></div>
                <div class="admin-profile-fields">
                    <div><label for="radius">Bán kính cho phép (mét)</label><input class="input" id="radius" name="radius_meters" type="number" min="1" max="100000" value="{{ old('radius_meters', $location['radius_meters']) }}"></div>
                    <div><label for="accuracy">Sai số GPS tối đa (mét)</label><input class="input" id="accuracy" name="max_accuracy_meters" type="number" min="1" max="10000" value="{{ old('max_accuracy_meters', $location['max_accuracy_meters']) }}"></div>
                </div>
            </fieldset>
            <p>Vị trí đang áp dụng: <a href="https://www.google.com/maps?q={{ $location['latitude'] }},{{ $location['longitude'] }}" target="_blank" rel="noopener noreferrer">{{ $location['latitude'] }}, {{ $location['longitude'] }} ↗</a></p>
            <button class="button" type="submit"><span class="material-symbols-outlined">save</span>Lưu cấu hình</button>
        </form>
    </section>
    </div>
</div>
<script>
(() => {
    const mode = document.getElementById('location-mode');
    const fields = document.getElementById('custom-location');
    const mapPanel = document.querySelector('.attendance-map-panel');
    const coordinateInput = document.getElementById('coordinates');
    const radiusInput = document.getElementById('radius');
    let mapTimer;
    const updateMap = () => {
        const server = mode.value === 'server';
        const number = '[+-]?(?:\\d+(?:\\.\\d+)?|\\.\\d+)';
        const match = coordinateInput.value.trim().match(new RegExp(`^(${number})(?:\\s*,\\s*|\\s+)(${number})$`));
        const lat = server ? Number(mapPanel.dataset.defaultLat) : (match ? Number(match[1]) : NaN);
        const lng = server ? Number(mapPanel.dataset.defaultLng) : (match ? Number(match[2]) : NaN);
        const radius = server ? Number(mapPanel.dataset.defaultRadius) : Number(radiusInput.value);
        const frame = document.getElementById('location-map');
        const valid = Number.isFinite(lat) && Number.isFinite(lng) && Math.abs(lat) <= 90 && Math.abs(lng) <= 180;
        frame.hidden = !valid;
        document.getElementById('map-preview-status').textContent = valid
            ? 'Bản đồ cập nhật theo biểu mẫu. Bấm Lưu cấu hình để áp dụng.'
            : 'Nhập đúng vĩ độ, kinh độ để xem trước bản đồ.';
        document.getElementById('map-coordinate-label').textContent = valid ? `${lat.toFixed(6)}, ${lng.toFixed(6)}` : 'Tọa độ chưa hợp lệ';
        document.getElementById('map-radius-label').textContent = radius > 0 ? `${radius.toLocaleString('vi-VN')} m` : '—';
        const link = document.getElementById('map-open-link');
        link.hidden = !valid;
        if (!valid) return;
        const span = Math.max(500, Math.min(100000, radius || 500) * 2) / 111320;
        const lngSpan = Math.min(180, span / Math.max(0.01, Math.cos(lat * Math.PI / 180)));
        const bbox = [Math.max(-180, lng - lngSpan), Math.max(-90, lat - span), Math.min(180, lng + lngSpan), Math.min(90, lat + span)].join(',');
        const url = `https://www.openstreetmap.org/export/embed.html?bbox=${encodeURIComponent(bbox)}&layer=mapnik&marker=${lat},${lng}`;
        if (frame.getAttribute('src') !== url) frame.src = url;
        link.href = `https://www.openstreetmap.org/?mlat=${lat}&mlon=${lng}#map=16/${lat}/${lng}`;
    };
    const queueMap = () => { clearTimeout(mapTimer); mapTimer = setTimeout(updateMap, 500); };
    coordinateInput.addEventListener('input', queueMap);
    radiusInput.addEventListener('input', queueMap);
    const update = () => { fields.disabled = mode.value === 'server'; fields.hidden = fields.disabled; updateMap(); };
    mode.addEventListener('change', update);
    update();

    const detect = document.getElementById('detect-location');
    const status = document.getElementById('location-status');
    detect.addEventListener('click', () => {
        if (!window.isSecureContext) {
            status.textContent = 'Hãy mở trang bằng HTTPS hoặc localhost để lấy vị trí.';
            return;
        }
        if (!navigator.geolocation) {
            status.textContent = 'Trình duyệt không hỗ trợ lấy vị trí. Bạn có thể dán tọa độ từ Google Maps.';
            return;
        }
        detect.disabled = true;
        detect.setAttribute('aria-busy', 'true');
        status.textContent = 'Đang lấy vị trí nhanh. Hãy cho phép trình duyệt truy cập vị trí khi được hỏi.';
        let completed = false;
        const finish = () => {
            completed = true;
            clearTimeout(deadline);
            detect.disabled = false;
            detect.removeAttribute('aria-busy');
        };
        const deadline = setTimeout(() => {
            status.textContent = 'Chưa lấy được vị trí. Trên Windows, kiểm tra Cài đặt → Quyền riêng tư & bảo mật → Vị trí và bật dịch vụ vị trí. Bạn cũng có thể dán tọa độ từ Google Maps.';
            finish();
        }, 18000);
        const onSuccess = position => {
            if (completed) return;
            const {latitude, longitude, accuracy} = position.coords;
            mode.value = 'custom';
            update();
            document.getElementById('coordinates').value = `${latitude.toFixed(7)}, ${longitude.toFixed(7)}`;
            updateMap();
            status.textContent = `Đã điền vị trí hiện tại, sai số ước tính ${Math.round(accuracy)} m. Kiểm tra tọa độ rồi bấm Lưu cấu hình để áp dụng.`;
            finish();
        };
        const onError = error => {
            if (completed) return;
            const messages = {
                1: 'Bạn chưa cho phép truy cập vị trí. Hãy bật quyền vị trí cho trang trong trình duyệt rồi thử lại.',
                2: 'Không xác định được vị trí. Hãy bật dịch vụ vị trí trên thiết bị hoặc dán tọa độ từ Google Maps.',
                3: 'Chưa lấy được vị trí dù đã thử lại. Trên Windows, kiểm tra Cài đặt → Quyền riêng tư & bảo mật → Vị trí và bật dịch vụ vị trí, hoặc dán tọa độ từ Google Maps.',
            };
            status.textContent = messages[error.code] || 'Không lấy được vị trí. Hãy thử lại.';
            finish();
        };
        navigator.geolocation.getCurrentPosition(onSuccess, error => {
            if (completed) return;
            if (error.code === 1) {
                onError(error);
                return;
            }
            status.textContent = 'Lần đầu chưa lấy được vị trí. Đang thử chế độ chính xác cao, vui lòng đợi thêm tối đa 8 giây…';
            navigator.geolocation.getCurrentPosition(onSuccess, onError, {
                enableHighAccuracy: true, timeout: 8000, maximumAge: 0,
            });
        }, {enableHighAccuracy: false, timeout: 5000, maximumAge: 30000});
    });
})();
</script>
@endsection
