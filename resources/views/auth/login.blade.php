@extends('layouts.auth')

@section('title', 'Đăng nhập · HUIT HRMS')
@section('content')
    <main class="login-page">
        <div class="login-shell">
            <section class="login-brand">
                <div class="login-logo"><div class="login-logo-mark">H</div><div><strong>HUIT <span style="color:#ff8b35">•</span> HRMS</strong><small>HR MANAGEMENT SYSTEM</small></div></div>
                <p class="login-description">Hệ thống Quản lý Nhân sự & Chấm công trực tuyến nội bộ Trường Đại học Công Thương TP. Hồ Chí Minh</p>
            </section>
            <section class="login-form">
                <span class="badge login-kicker">CỔNG XÁC THỰC TẬP TRUNG</span>
                <h1>Đăng nhập hệ thống</h1>
                <p class="lead">Nhập tài khoản được cấp bởi Quản trị viên để truy cập không gian làm việc.</p>
                @if ($errors->any())
                    <div class="login-alert" role="alert"><strong>Đăng nhập không thành công:</strong> {{ $errors->first() }}</div>
                @endif
                <form method="POST" action="{{ route('login.store') }}">
                    @csrf
                    <label class="login-label" for="email">Tên đăng nhập <span style="color:#e11d48">*</span></label>
                    <input class="login-control" id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email">
                    <label class="login-label" for="password">Mật khẩu <span style="color:#e11d48">*</span></label>
                    <input class="login-control" id="password" name="password" type="password" required autocomplete="current-password">
                    <label class="login-check" for="remember"><input id="remember" name="remember" type="checkbox" value="1"> Ghi nhớ đăng nhập</label>
                    <button class="login-button" type="submit">Đăng nhập</button>
                </form>

                <div class="login-demo-accounts" role="note">
                    <div class="login-demo-heading">Tài khoản demo</div>
                    <div class="login-demo-password">Mật khẩu dùng chung: <strong>10giokem7</strong></div>
                    <ul>
                        <li><span>Admin</span><strong>admin@example.test</strong></li>
                        <li><span>HR</span><strong>hr@example.test</strong></li>
                        <li><span>Employee</span><strong>employee@example.test</strong></li>
                    </ul>
                </div>
            </section>
        </div>
    </main>
@endsection
