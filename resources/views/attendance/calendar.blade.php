@extends($layout)

@section('content')
<div class="stitch-page attendance-page">
    <div class="stitch-context-row">
        <div>
            <div class="stitch-context">HỆ THỐNG NHÂN SỰ / Chấm công</div>
            <h1>Chấm công của tôi</h1>
            <p class="stitch-subtitle">Một tháng làm việc, nhìn lại trên lịch. Chọn một ngày để xem chi tiết.</p>
        </div>
        <a class="button button-light" href="{{ route($dashboardRoute) }}"><span class="material-symbols-outlined">login</span>Về trang chấm công</a>
    </div>

    <div class="stitch-summary-grid attendance-summary">
        @foreach([
            ['key' => 'on_time', 'label' => 'Đúng giờ', 'icon' => 'task_alt', 'note' => 'Có check-in trước hoặc lúc 08:30'],
            ['key' => 'late', 'label' => 'Đi trễ', 'icon' => 'schedule', 'note' => 'Check-in sau 08:30'],
            ['key' => 'leave', 'label' => 'Nghỉ phép', 'icon' => 'event_available', 'note' => 'Đã được duyệt trong tháng'],
            ['key' => 'absent', 'label' => 'Vắng', 'icon' => 'event_busy', 'note' => 'Ngày làm việc đã qua, chưa có check-in'],
        ] as $stat)
            <article class="stitch-summary-card attendance-stat attendance-stat-{{ $stat['key'] }}">
                <div class="stitch-summary-head"><span>{{ $stat['label'] }}</span><div class="stitch-summary-icon"><span class="material-symbols-outlined">{{ $stat['icon'] }}</span></div></div>
                <div class="stitch-summary-value">{{ $summary[$stat['key']] }} <small>ngày</small></div>
                <span class="stitch-summary-caption">{{ $stat['note'] }}</span>
            </article>
        @endforeach
    </div>

    <div class="attendance-calendar-layout" data-attendance-calendar>
        <section class="stitch-panel attendance-calendar-panel" aria-label="Lịch chấm công">
            <div class="attendance-calendar-head">
                <div>
                    <span class="attendance-eyebrow">LỊCH CHẤM CÔNG</span>
                    <h2>Tháng {{ $month->format('m / Y') }}</h2>
                </div>
                <div class="attendance-month-nav">
                    <a class="attendance-icon-button" href="{{ route($calendarRoute, ['month' => $month->subMonth()->format('Y-m')]) }}" aria-label="Tháng trước"><span class="material-symbols-outlined">chevron_left</span></a>
                    <a class="button button-light" href="{{ route($calendarRoute) }}">Tháng này</a>
                    <a class="attendance-icon-button" href="{{ route($calendarRoute, ['month' => $month->addMonth()->format('Y-m')]) }}" aria-label="Tháng sau"><span class="material-symbols-outlined">chevron_right</span></a>
                </div>
            </div>
            <form class="attendance-month-filter" method="GET" action="{{ route($calendarRoute) }}">
                <label for="attendance-month">Chọn tháng</label>
                <input class="input" id="attendance-month" name="month" type="month" value="{{ $month->format('Y-m') }}" required>
                <button class="button button-light" type="submit">Xem lịch</button>
            </form>
            <div class="attendance-calendar-wrap">
                <table class="attendance-calendar" aria-label="Chấm công tháng {{ $month->format('m/Y') }}">
                    <thead><tr>
                        @foreach(['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'] as $weekday)
                            <th scope="col">{{ $weekday }}</th>
                        @endforeach
                    </tr></thead>
                    <tbody>
                        @foreach($weeks as $week)
                            <tr>
                                @foreach($week as $day)
                                    <td>
                                        @if($day)
                                            <button type="button" class="attendance-day {{ $day['date']->isToday() ? 'is-today' : '' }} {{ $day['date']->isWeekend() ? 'is-weekend' : '' }}" data-calendar-day="{{ $day['date']->toDateString() }}" aria-controls="attendance-detail-{{ $day['date']->toDateString() }}" aria-pressed="{{ $selectedDate === $day['date']->toDateString() ? 'true' : 'false' }}" aria-label="{{ $day['date']->format('d/m/Y') }}: {{ $day['label'] }}{{ $day['attendance'] && $day['leave'] ? ', có nghỉ phép đã duyệt' : '' }}" @if($day['date']->isToday()) aria-current="date" @endif>
                                                <span class="attendance-day-number">{{ $day['date']->day }}</span>
                                                <span class="attendance-day-dots" aria-hidden="true">
                                                    @if(in_array($day['status'], ['on_time', 'late', 'leave', 'absent'], true))
                                                        <span class="attendance-dot attendance-dot-{{ $day['status'] }}"></span>
                                                    @endif
                                                    @if($day['attendance'] && $day['leave'])
                                                        <span class="attendance-dot attendance-dot-leave"></span>
                                                    @endif
                                                </span>
                                                <span class="attendance-day-caption">{{ $day['attendance']?->check_in_at?->format('H:i') ?? (in_array($day['status'], ['leave', 'absent'], true) ? $day['label'] : '') }}</span>
                                            </button>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="attendance-legend" aria-label="Chú thích màu">
                <span><i class="attendance-dot attendance-dot-on_time" aria-hidden="true"></i>Đúng giờ</span>
                <span><i class="attendance-dot attendance-dot-late" aria-hidden="true"></i>Đi trễ</span>
                <span><i class="attendance-dot attendance-dot-leave" aria-hidden="true"></i>Nghỉ phép / Vắng</span>
                <span><i class="attendance-today-marker" aria-hidden="true"></i>Hôm nay</span>
            </div>
            <p class="attendance-calendar-note">Ngày làm việc tính từ Thứ Hai đến Thứ Sáu. Ngày hôm nay chưa check-in, cuối tuần và ngày chưa tới sẽ không có chấm màu. Ngày vừa đi làm vừa nghỉ phép nửa ngày có hai chấm.</p>
        </section>

        <aside class="stitch-panel attendance-detail-panel" aria-label="Chi tiết ngày được chọn">
            <div class="attendance-detail-heading"><span class="material-symbols-outlined">event_note</span><h2>Chi tiết ngày</h2></div>
            <div aria-live="polite" aria-atomic="true">
                @foreach($days as $day)
                    <section id="attendance-detail-{{ $day['date']->toDateString() }}" data-calendar-detail="{{ $day['date']->toDateString() }}" @if($selectedDate !== $day['date']->toDateString()) hidden @endif>
                        <p class="attendance-detail-date">{{ $day['date']->format('d / m / Y') }}</p>
                        <span class="attendance-status attendance-status-{{ $day['status'] }}">{{ $day['label'] }}</span>
                        @if($day['attendance'])
                            <dl class="attendance-times">
                                <div><dt>Giờ vào</dt><dd>{{ $day['attendance']->check_in_at->format('H:i:s') }}</dd></div>
                                <div><dt>Giờ ra</dt><dd>{{ $day['attendance']->check_out_at?->format('H:i:s') ?? 'Chưa check-out' }}</dd></div>
                            </dl>
                            <p class="attendance-detail-note">{{ $day['status'] === 'late' ? 'Bạn đã check-in sau mốc 08:30.' : 'Bạn đã check-in đúng giờ.' }}{{ ! $day['attendance']->check_out_at ? ' Bản ghi này chưa có giờ check-out.' : '' }}</p>
                        @elseif($day['status'] === 'absent')
                            <p class="attendance-detail-note">Không có bản ghi chấm công hoặc đơn nghỉ phép được duyệt cho ngày làm việc này.</p>
                        @elseif($day['status'] === 'pending')
                            <p class="attendance-detail-note">Hôm nay bạn chưa check-in. Về Dashboard để chấm công.</p>
                        @elseif($day['status'] === 'weekend')
                            <p class="attendance-detail-note">Ngày nghỉ cuối tuần, không tính vắng.</p>
                        @elseif($day['status'] === 'inactive')
                            <p class="attendance-detail-note">Ngày này nằm ngoài thời gian làm việc ghi trong hồ sơ, không tính vắng.</p>
                        @elseif($day['status'] === 'future')
                            <p class="attendance-detail-note">Chưa có dữ liệu chấm công cho ngày này.</p>
                        @endif
                        @if($day['leave'])
                            <div class="attendance-leave-detail">
                                <strong>Nghỉ phép đã duyệt</strong>
                                <p>{{ $day['leave_days'] === 0.5 ? 'Nửa ngày · '.($day['leave']->half_day_period === 'morning' ? 'Buổi sáng' : 'Buổi chiều') : 'Cả ngày' }}</p>
                                <p>{{ $day['leave']->reason }}</p>
                                <a href="{{ route($leaveRoute, $day['leave']) }}">Xem đơn nghỉ phép <span aria-hidden="true">→</span></a>
                            </div>
                        @endif
                    </section>
                @endforeach
            </div>
        </aside>
    </div>
</div>
<script>
    (() => {
        const calendar = document.querySelector('[data-attendance-calendar]');
        if (!calendar) {
            return;
        }
        calendar.addEventListener('click', (event) => {
            const button = event.target.closest('[data-calendar-day]');
            if (!button) {
                return;
            }
            calendar.querySelectorAll('[data-calendar-day]').forEach((day) => {
                day.setAttribute('aria-pressed', String(day === button));
            });
            calendar.querySelectorAll('[data-calendar-detail]').forEach((detail) => {
                detail.hidden = detail.dataset.calendarDetail !== button.dataset.calendarDay;
            });
        });
    })();
</script>
@endsection
