<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'HUIT HRMS' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,400,0,0" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="stitch-body">
    <div class="hrms-shell stitch-shell">
        <aside class="hrms-sidebar stitch-sidebar">
            <div class="stitch-sidebar-main">
                <a class="brand stitch-brand" href="{{ route('hr.home') }}">
                    <div class="brand-mark stitch-brand-mark">H</div>
                    <div><div class="brand-title stitch-brand-title">HUIT</div><div class="brand-sub stitch-brand-sub">HR Management</div></div>
                </a>
                <nav class="nav stitch-nav" aria-label="Điều hướng HR">
                    <a class="stitch-nav-link {{ ($active ?? '') === 'dashboard' ? 'active' : '' }}" href="{{ route('hr.home') }}"><span class="material-symbols-outlined">dashboard</span><span>Dashboard</span></a>
                    <a class="stitch-nav-link {{ ($active ?? '') === 'attendance' ? 'active' : '' }}" href="{{ route('hr.attendance.index') }}"><span class="material-symbols-outlined">schedule</span><span>Quản lý chấm công</span></a>
                    <a class="stitch-nav-link {{ ($active ?? '') === 'employees' ? 'active' : '' }}" href="{{ route('hr.employees') }}"><span class="material-symbols-outlined">groups</span><span>Quản lý nhân viên</span></a>
                    <a class="stitch-nav-link {{ ($active ?? '') === 'departments' ? 'active' : '' }}" href="{{ route('hr.departments') }}"><span class="material-symbols-outlined">corporate_fare</span><span>Phòng ban</span></a>
                    <a class="stitch-nav-link {{ ($active ?? '') === 'positions' ? 'active' : '' }}" href="{{ route('hr.positions') }}"><span class="material-symbols-outlined">badge</span><span>Chức vụ</span></a>
                    <a class="stitch-nav-link {{ ($active ?? '') === 'tasks' ? 'active' : '' }}" href="{{ route('hr.tasks') }}"><span class="material-symbols-outlined">checklist</span><span>Công việc</span></a>
                    <a class="stitch-nav-link {{ ($active ?? '') === 'requests' ? 'active' : '' }}" href="{{ route('hr.requests') }}"><span class="material-symbols-outlined">receipt_long</span><span>Phiếu yêu cầu</span></a>
                    <a class="stitch-nav-link {{ ($active ?? '') === 'recruitment' ? 'active' : '' }}" href="{{ route('hr.recruitment') }}"><span class="material-symbols-outlined">person_add</span><span>Tuyển dụng</span></a>
                    <a class="stitch-nav-link {{ ($active ?? '') === 'termination' ? 'active' : '' }}" href="{{ route('hr.termination') }}"><span class="material-symbols-outlined">person_remove</span><span>Thôi việc</span></a>
                    <a class="stitch-nav-link {{ ($active ?? '') === 'profile' ? 'active' : '' }}" href="{{ route('hr.profile') }}"><span class="material-symbols-outlined">account_box</span><span>Hồ sơ cá nhân</span></a>
                </nav>
            </div>
            <div class="sidebar-bottom stitch-sidebar-bottom">
                <div class="stitch-sidebar-divider"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="logout-link stitch-logout" type="submit"><span class="material-symbols-outlined">logout</span><span>Đăng xuất</span></button>
                </form>
            </div>
        </aside>
        <main class="hrms-main stitch-main">
            <header class="topbar stitch-topbar">
                <div class="stitch-greeting"><div class="stitch-greeting-line"><span class="top-title">{{ $topTitle ?? 'Cổng nhân sự' }}</span><span class="stitch-dot">•</span><span class="stitch-hello">{{ $topSub ?? 'Không gian làm việc HR' }}</span></div></div>
                <div class="stitch-top-actions">
                    <button class="stitch-notification" type="button" aria-label="Thông báo"><span class="material-symbols-outlined">notifications</span><span class="stitch-notification-dot"></span></button>
                    <div class="user-chip stitch-user-chip"><div class="avatar stitch-user-avatar"><span class="material-symbols-outlined">person</span></div><div class="stitch-user-copy"><strong>{{ $employee->full_name ?? auth()->user()->name ?? 'Người dùng' }}</strong><small>HR</small></div></div>
                </div>
            </header>
            @if (session('success'))
                <div class="stitch-page"><div class="notice">{{ session('success') }}</div></div>
            @endif
            @if ($errors->any())
                <div class="stitch-page"><div class="notice alert-error">{{ $errors->first() }}</div></div>
            @endif
            @yield('content')
        </main>
    </div>
</body>
</html>
