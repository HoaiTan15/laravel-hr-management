@extends('layouts.app')

@section('title', 'Đăng nhập · HUIT HRMs')

@section('content')
    <div style="display:grid; place-items:center; min-height:calc(100vh - 136px);">
        <section class="card" style="width:min(100%, 460px);">
            <div class="card-body">
                <div style="margin-bottom:24px;">
                    <p style="margin:0 0 8px; color:#f37021; font-size:12px; font-weight:700; letter-spacing:.08em; text-transform:uppercase;">HUIT HRMs</p>
                    <h1>Đăng nhập hệ thống</h1>
                    <p style="margin:8px 0 0; color:#64748b; font-size:14px;">Sử dụng tài khoản được cấp để tiếp tục.</p>
                </div>

                <form method="POST" action="{{ route('login.store') }}">
                    @csrf
                    <div class="form-field">
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email">
                    </div>
                    <div class="form-field">
                        <label for="password">Mật khẩu</label>
                        <input id="password" name="password" type="password" required autocomplete="current-password">
                    </div>
                    <label style="display:flex; align-items:center; gap:8px; margin-bottom:20px; color:#64748b; font-size:13px;">
                        <input name="remember" type="checkbox" value="1">
                        Ghi nhớ đăng nhập
                    </label>
                    <button class="button button-primary" type="submit" style="width:100%;">Đăng nhập</button>
                </form>

                <div style="margin-top:24px; padding:16px; background:#eff4ff; border:1px solid #d3e4fe; border-radius:12px;">
                    <h2 style="margin:0 0 10px; color:#002d5d; font-size:15px;">Tài khoản demo</h2>
                    <p style="margin:0 0 12px; color:#64748b; font-size:13px;">Mật khẩu dùng chung: <strong>10giokem7</strong></p>
                    <ul style="display:grid; gap:8px; margin:0; padding:0; list-style:none; color:#334155; font-size:13px;">
                        <li><strong>Admin:</strong> admin@example.test</li>
                        <li><strong>HR:</strong> hr@example.test</li>
                        <li><strong>Employee:</strong> employee@example.test</li>
                        <li><strong>Employee:</strong> employee2@example.test</li>
                    </ul>
                </div>
            </div>
        </section>
    </div>
@endsection
