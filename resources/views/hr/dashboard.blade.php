@extends('layouts.hr')

@section('content')
<div class="page">
    <div class="crumb"><strong>CỔNG NHÂN SỰ | ĐẠI HỌC CÔNG THƯƠNG TP.HCM</strong>　/　Dashboard</div>
    <h1>Dashboard HR</h1>
    <p class="subtitle">Tổng quan tình hình nhân sự và hoạt động hôm nay.</p>

    <div class="grid grid-4">
        <div class="card stat"><div class="stat-label">Tổng nhân viên</div><div class="stat-value">125 <small>/ 128 cán bộ</small></div><span class="badge green">97.6%</span><div class="stat-icon">◷</div></div>
        <div class="card stat"><div class="stat-label">Đúng giờ</div><div class="stat-value">96 <small>cán bộ</small></div><span class="badge">76.8%</span><div class="stat-icon">✓</div></div>
        <div class="card stat"><div class="stat-label">Đi muộn</div><div class="stat-value" style="color:#b44a12">12 <small>cán bộ</small></div><span class="badge orange">9.6%</span><div class="stat-icon">⚠</div></div>
        <div class="card stat"><div class="stat-label">Vắng / chưa Check-out</div><div class="stat-value" style="color:var(--red)">7 <small>vắng</small></div><span class="badge red">17 trường hợp</span><div class="stat-icon">⌛</div></div>
    </div>

    <div class="card" style="margin-top:20px">
        <div class="section-head"><div><h2>Công việc cần xử lý hôm nay</h2><p>Các nhiệm vụ quan trọng cho toàn trường</p></div><a class="button" href="#">Xem chi tiết</a></div>
        <div class="grid grid-3">
            <div class="card" style="box-shadow:none;background:#f7f9ff"><strong>3 phiếu yêu cầu</strong><p class="muted">đang chờ xử lý</p></div>
            <div class="card" style="box-shadow:none;background:#f7f9ff"><strong>2 hồ sơ tuyển dụng</strong><p class="muted">mới trong tuần</p></div>
            <div class="card" style="box-shadow:none;background:#f7f9ff"><strong>2 yêu cầu chấm công</strong><p class="muted">cần rà soát</p></div>
        </div>
    </div>

    <div class="card" style="margin-top:20px">
        <div class="section-head"><div><h2>Danh sách chấm công hôm nay</h2><p>Cán bộ đang hoạt động trong ca làm việc</p></div><a class="button" href="{{ route('hr.attendance.index') }}">Xem tất cả</a></div>
        <div class="table-wrap"><table class="data-table"><thead><tr><th>Mã nhân viên</th><th>Họ tên</th><th>Chức vụ</th><th>Giờ check-in</th><th>Thời gian</th><th>Trạng thái</th></tr></thead><tbody>
            <tr><td>NV001</td><td>Nguyễn Văn A</td><td>Giảng viên</td><td>08:02</td><td>9h20</td><td><span class="badge green">đúng giờ</span></td></tr>
            <tr><td>NV002</td><td>Lê Thị B</td><td>Chuyên viên</td><td>08:15</td><td>9h35</td><td><span class="badge orange">muộn</span></td></tr>
            <tr><td>NV003</td><td>Trần Văn C</td><td>Nhân viên</td><td>09:12</td><td>10h25</td><td><span class="badge red">muộn</span></td></tr>
        </tbody></table></div>
    </div>
</div>
@endsection