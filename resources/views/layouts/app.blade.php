<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'HUIT HRMs'))</title>
    <style>
        :root { --primary: #004385; --primary-dark: #002d5d; --accent: #f37021; --canvas: #f8faff; --border: #e2e8f0; --text: #0f172a; --muted: #64748b; }
        * { box-sizing: border-box; }
        body { margin: 0; background: var(--canvas); color: var(--text); font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        a { color: inherit; text-decoration: none; }
        button, input { font: inherit; }
        .app-shell { display: flex; min-height: 100vh; }
        .sidebar { width: 260px; flex: 0 0 260px; background: var(--primary-dark); color: #fff; padding: 24px 16px; }
        .brand { display: flex; align-items: center; gap: 12px; margin: 0 12px 32px; font-weight: 700; letter-spacing: .01em; }
        .brand-mark { display: grid; place-items: center; width: 36px; height: 36px; border-radius: 10px; background: var(--accent); }
        .nav-label { margin: 20px 12px 8px; color: #a8c8ff; font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
        .nav-link { display: block; padding: 11px 12px; border-radius: 8px; color: #d6e3ff; font-size: 14px; }
        .nav-link:hover, .nav-link.active { background: #004385; color: #fff; }
        .main-column { min-width: 0; flex: 1; }
        .topbar { display: flex; align-items: center; justify-content: space-between; min-height: 72px; padding: 0 32px; background: #fff; border-bottom: 1px solid var(--border); }
        .topbar-title { font-size: 14px; color: var(--muted); }
        .user-chip { display: flex; align-items: center; gap: 10px; font-size: 14px; }
        .avatar { display: grid; place-items: center; width: 34px; height: 34px; border-radius: 50%; background: #d6e3ff; color: var(--primary-dark); font-weight: 700; }
        .content { max-width: 1440px; margin: 0 auto; padding: 32px; }
        .breadcrumb { margin-bottom: 8px; color: var(--muted); font-size: 13px; }
        h1 { margin: 0; font-size: 28px; line-height: 1.25; }
        .page-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 24px; }
        .card { background: #fff; border: 1px solid var(--border); border-radius: 16px; box-shadow: 0 1px 3px rgba(15, 23, 42, .04); }
        .card-body { padding: 24px; }
        .button { display: inline-flex; align-items: center; justify-content: center; min-height: 40px; padding: 0 16px; border: 0; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; }
        .button-primary { background: var(--primary); color: #fff; }
        .button-primary:hover { background: #0d5c9e; }
        .button-outline { background: #fff; border: 1px solid #cbd5e1; color: #334155; }
        .alert { margin-bottom: 20px; padding: 12px 16px; border-radius: 8px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #047857; }
        .alert-error { background: #fef2f2; border-color: #fecaca; color: #b91c1c; }
        .form-field { display: grid; gap: 6px; margin-bottom: 16px; }
        .form-field label { color: #334155; font-size: 13px; font-weight: 600; }
        .form-field input { width: 100%; height: 40px; padding: 0 12px; background: #fff; border: 1px solid #cbd5e1; border-radius: 8px; color: var(--text); }
        .form-field input:focus { outline: 3px solid rgba(0, 67, 133, .15); border-color: var(--primary); }
        .error-list { margin: 0; padding-left: 20px; }
        @media (max-width: 767px) { .sidebar { width: 72px; flex-basis: 72px; padding: 20px 8px; } .brand { justify-content: center; margin: 0 0 28px; } .brand-name, .nav-label, .nav-link span { display: none; } .nav-link { text-align: center; } .topbar, .content { padding-left: 16px; padding-right: 16px; } .page-header { display: block; } }
    </style>
    @stack('head')
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar">
            <a class="brand" href="{{ route('home') }}">
                <span class="brand-mark">H</span>
                <span class="brand-name">HUIT HRMs</span>
            </a>
            @auth
                <div class="nav-label">Workspace</div>
                @if (auth()->user()->role === \App\Enums\UserRole::ADMIN)
                    <a class="nav-link {{ request()->routeIs('admin.dashboard', 'admin.home') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><span>Dashboard</span></a>
                    <a class="nav-link {{ request()->routeIs('admin.requests') ? 'active' : '' }}" href="{{ route('admin.requests') }}"><span>Phiếu yêu cầu</span></a>
                    <a class="nav-link {{ request()->routeIs('admin.accounts') ? 'active' : '' }}" href="{{ route('admin.accounts') }}"><span>Tài khoản</span></a>
                    <a class="nav-link {{ request()->routeIs('admin.profile') ? 'active' : '' }}" href="{{ route('admin.profile') }}"><span>Hồ sơ cá nhân</span></a>
                @elseif (auth()->user()->role === \App\Enums\UserRole::HR)
                    <a class="nav-link {{ request()->routeIs('hr.dashboard', 'hr.home') ? 'active' : '' }}" href="{{ route('hr.dashboard') }}"><span>Dashboard</span></a>
                    <a class="nav-link {{ request()->routeIs('hr.employees') ? 'active' : '' }}" href="{{ route('hr.employees') }}"><span>Nhân viên</span></a>
                    <a class="nav-link {{ request()->routeIs('hr.departments') ? 'active' : '' }}" href="{{ route('hr.departments') }}"><span>Phòng ban</span></a>
                    <a class="nav-link {{ request()->routeIs('hr.positions') ? 'active' : '' }}" href="{{ route('hr.positions') }}"><span>Chức vụ</span></a>
                    <a class="nav-link {{ request()->routeIs('hr.attendance', 'hr.attendances.*') ? 'active' : '' }}" href="{{ route('hr.attendance') }}"><span>Chấm công</span></a>
                    <a class="nav-link {{ request()->routeIs('hr.tasks') ? 'active' : '' }}" href="{{ route('hr.tasks') }}"><span>Công việc</span></a>
                    <a class="nav-link {{ request()->routeIs('hr.requests') ? 'active' : '' }}" href="{{ route('hr.requests') }}"><span>Phiếu yêu cầu</span></a>
                    <a class="nav-link {{ request()->routeIs('hr.recruitment') ? 'active' : '' }}" href="{{ route('hr.recruitment') }}"><span>Tuyển dụng</span></a>
                    <a class="nav-link {{ request()->routeIs('hr.termination') ? 'active' : '' }}" href="{{ route('hr.termination') }}"><span>Thôi việc</span></a>
                    <a class="nav-link {{ request()->routeIs('hr.profile') ? 'active' : '' }}" href="{{ route('hr.profile') }}"><span>Hồ sơ cá nhân</span></a>
                @else
                    <a class="nav-link {{ request()->routeIs('employee.*') ? 'active' : '' }}" href="{{ route('employee.home') }}"><span>My workspace</span></a>
                @endif
                @if (auth()->user()->role !== \App\Enums\UserRole::ADMIN)
                    <a class="nav-link {{ request()->routeIs('attendance.*') ? 'active' : '' }}" href="{{ route('attendance.check-in') }}"><span>Attendance</span></a>
                @endif
            @else
                <a class="nav-link" href="{{ route('login') }}"><span>Log in</span></a>
            @endauth
        </aside>
        <div class="main-column">
            <header class="topbar">
                <span class="topbar-title">Human Resources Management</span>
                @auth
                    <div class="user-chip">
                        <span>{{ auth()->user()->name }}</span>
                        <span class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                    </div>
                @endauth
            </header>
            <main class="content">
                @if (session('success'))
                    <div class="alert">{{ session('success') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-error" role="alert">
                        <ul class="error-list">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
