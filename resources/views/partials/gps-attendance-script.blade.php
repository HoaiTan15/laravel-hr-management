@once
<script>
(() => {
    const config = {
        latitude: {{ Js::from(config('app.attendance_gps.workplace_latitude')) }},
        longitude: {{ Js::from(config('app.attendance_gps.workplace_longitude')) }},
        radius: {{ Js::from(config('app.attendance_gps.radius_meters')) }},
        maxAccuracy: {{ Js::from(config('app.attendance_gps.max_accuracy_meters')) }},
    };

    const distanceMeters = (lat1, lng1, lat2, lng2) => {
        const radians = value => value * Math.PI / 180;
        const dLat = radians(lat2 - lat1);
        const dLng = radians(lng2 - lng1);
        const a = Math.sin(dLat / 2) ** 2 + Math.cos(radians(lat1)) * Math.cos(radians(lat2)) * Math.sin(dLng / 2) ** 2;
        return 6371000 * 2 * Math.asin(Math.sqrt(a));
    };

    document.querySelectorAll('form[data-gps-attendance]').forEach(form => {
        const button = form.querySelector('button[type="submit"]');
        if (!button || !navigator.geolocation) {
            return;
        }

        const status = document.createElement('p');
        status.setAttribute('role', 'status');
        status.style.cssText = 'margin:10px 0;color:#64748b;font-size:14px;';
        form.insertBefore(status, button);

        form.addEventListener('submit', event => {
            if (form.dataset.gpsReady === 'true') return;
            event.preventDefault();
            button.disabled = true;
            status.textContent = 'Đang lấy vị trí GPS... Vui lòng giữ nguyên vị trí.';

            navigator.geolocation.getCurrentPosition(position => {
                const { latitude, longitude, accuracy } = position.coords;
                if (accuracy > config.maxAccuracy) {
                    status.textContent = `Độ chính xác GPS hiện tại (${Math.round(accuracy)}m) chưa đạt yêu cầu (tối đa ${Math.round(config.maxAccuracy)}m).`;
                    button.disabled = false;
                    return;
                }

                const distance = distanceMeters(latitude, longitude, config.latitude, config.longitude);
                if (distance > config.radius) {
                    status.textContent = `Bạn đang cách nơi làm việc khoảng ${Math.round(distance)}m, vượt bán kính cho phép ${Math.round(config.radius)}m.`;
                    button.disabled = false;
                    return;
                }

                [['latitude', latitude], ['longitude', longitude], ['accuracy', accuracy]].forEach(([name, value]) => {
                    let input = form.querySelector(`input[name="${name}"]`);
                    if (!input) {
                        input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = name;
                        form.appendChild(input);
                    }
                    input.value = value;
                });
                form.dataset.gpsReady = 'true';
                status.textContent = 'Đã xác định vị trí. Đang gửi dữ liệu...';
                form.submit();
            }, error => {
                const messages = {
                    1: 'Bạn đã từ chối quyền truy cập vị trí. Hãy cho phép GPS trong trình duyệt rồi thử lại.',
                    2: 'Không lấy được vị trí GPS. Hãy kiểm tra GPS/kết nối mạng rồi thử lại.',
                    3: 'Lấy vị trí GPS quá thời gian. Hãy thử lại ở nơi có tín hiệu tốt hơn.',
                };
                status.textContent = messages[error.code] || 'Không lấy được vị trí GPS. Vui lòng thử lại.';
                button.disabled = false;
            }, { enableHighAccuracy: true, timeout: {{ (int) config('app.attendance_gps.timeout_seconds') * 1000 }}, maximumAge: 0 });
        });
    });
})();
</script>
@endonce
