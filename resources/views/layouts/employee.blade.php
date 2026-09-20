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
        <aside class="hrms-sidebar stitch-sidebar"><div class="stitch-sidebar-main"><div class="brand stitch-brand"><div class="brand-mark stitch-brand-mark">H</div><div><div class="brand-title stitch-brand-title">HUIT</div><div class="brand-sub stitch-brand-sub">HR Management</div></div></div><nav class="nav stitch-nav"><a class="stitch-nav-link {{ ($active ?? '') === 'dashboard' ? 'active' : '' }}" href="{{ route('employee.home') }}"><span class="material-symbols-outlined">dashboard</span><span>Dashboard cá nhân</span></a><a class="stitch-nav-link {{ ($active ?? '') === 'tasks' ? 'active' : '' }}" href="{{ route('employee.tasks.index') }}"><span class="material-symbols-outlined">task_alt</span><span>Xem danh sách công việc</span></a><a class="stitch-nav-link {{ ($active ?? '') === 'profile' ? 'active' : '' }}" href="{{ route('employee.profile') }}"><span class="material-symbols-outlined">account_box</span><span>Xem hồ sơ cá nhân</span></a><a class="stitch-nav-link {{ ($active ?? '') === 'requests' ? 'active' : '' }}" href="{{ route('employee.requests.index') }}"><span class="material-symbols-outlined">receipt_long</span><span>Phiếu yêu cầu</span></a></nav></div><div class="sidebar-bottom stitch-sidebar-bottom"><div class="stitch-sidebar-divider"></div><form method="POST" action="{{ route('logout') }}">@csrf<button class="logout-link stitch-logout" type="submit"><span class="material-symbols-outlined">logout</span><span>Đăng xuất</span></button></form></div></aside>
        <main class="hrms-main stitch-main"><header class="topbar stitch-topbar"><div></div><div class="stitch-top-actions"><button class="stitch-notification" type="button" aria-label="Notifications"><span class="material-symbols-outlined">notifications</span></button><div class="user-chip stitch-user-chip"><div class="avatar stitch-user-avatar"><span class="material-symbols-outlined">person</span></div><div class="stitch-user-copy"><strong>{{ $employee->full_name ?? auth()->user()->name ?? 'Người dùng' }}</strong><small>EMPLOYEE</small></div></div></div></header>@yield('content')</main>
    </div>
</body>
</html>
