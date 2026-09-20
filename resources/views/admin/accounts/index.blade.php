@extends('layouts.admin')

@section('content')
    <div class="stitch-page admin-page">
        <div class="stitch-context-row"><div class="stitch-context"><span class="stitch-kicker">Admin</span><span class="stitch-context-slash">/</span><span>Quản lý tài khoản</span></div><span class="stitch-date"><span></span> Hệ thống đang hoạt động</span></div>
        <div class="hr-module-heading"><div><h1>Quản lý tài khoản</h1><p>Quản lý người dùng, vai trò và quyền truy cập vào hệ thống HRMS.</p></div><div class="stitch-summary-icon"><span class="material-symbols-outlined">manage_accounts</span></div></div>

        <div class="stitch-summary-grid hr-module-summary">
            <div class="stitch-summary-card"><div class="stitch-summary-head"><span>Tổng tài khoản</span><span class="stitch-summary-icon"><span class="material-symbols-outlined">group</span></span></div><div class="stitch-summary-value">{{ $totalUsers }} <small>tài khoản</small></div><div class="stitch-summary-caption">Trong hệ thống</div></div>
            <div class="stitch-summary-card"><div class="stitch-summary-head"><span>Đang hoạt động</span><span class="stitch-summary-icon"><span class="material-symbols-outlined">how_to_reg</span></span></div><div class="stitch-summary-value">{{ $activeUsers }} <small>tài khoản</small></div><div class="stitch-summary-caption"><span class="material-symbols-outlined">check_circle</span>Đang sử dụng</div></div>
            <div class="stitch-summary-card"><div class="stitch-summary-head"><span>Quản trị viên</span><span class="stitch-summary-icon secondary"><span class="material-symbols-outlined">admin_panel_settings</span></span></div><div class="stitch-summary-value">{{ $adminUsers }} <small>tài khoản</small></div><div class="stitch-summary-caption">Admin và HR</div></div>
            <div class="stitch-summary-card"><div class="stitch-summary-head"><span>Đã khóa</span><span class="stitch-summary-icon secondary"><span class="material-symbols-outlined">person_off</span></span></div><div class="stitch-summary-value">{{ $inactiveUsers }} <small>tài khoản</small></div><div class="stitch-summary-caption">Không được truy cập</div></div>
        </div>

        <div class="stitch-content-grid admin-accounts-grid">
            <div class="stitch-main-column">
                <div class="stitch-panel hr-demo-panel">
                    <div class="stitch-panel-head"><div class="stitch-panel-title"><span class="stitch-panel-icon primary"><span class="material-symbols-outlined">manage_accounts</span></span><div><h2>Danh sách tài khoản</h2><p>Dữ liệu trực tiếp từ database</p></div></div><span class="stitch-count-pill">{{ $users->total() }} tài khoản</span></div>
                    <form class="hr-demo-toolbar" method="GET" action="{{ route('admin.accounts') }}">
                        <input class="input" name="search" value="{{ request('search') }}" type="search" placeholder="Tìm theo tên hoặc email">
                        <select class="select" name="role"><option value="">Tất cả vai trò</option>@foreach($roles as $roleOption)<option value="{{ $roleOption->value }}" @selected(request('role') === $roleOption->value)>{{ strtoupper($roleOption->value) }}</option>@endforeach</select>
                        <select class="select" name="status"><option value="">Tất cả trạng thái</option><option value="active" @selected(request('status') === 'active')>Hoạt động</option><option value="inactive" @selected(request('status') === 'inactive')>Đã khóa</option></select>
                        <button class="button button-light" type="submit"><span class="material-symbols-outlined">filter_alt</span>Lọc</button>
                    </form>
                    <div class="stitch-table-wrap"><table class="stitch-table admin-account-table"><thead><tr><th>Tài khoản</th><th>Vai trò</th><th>Trạng thái</th><th>Mật khẩu mới</th><th></th></tr></thead><tbody>
                        @forelse($users as $account)
                            @php($formId = 'account-update-'.$account->id)
                            <tr>
                                <td><form id="{{ $formId }}" method="POST" action="{{ route('admin.accounts.update', $account) }}">@csrf @method('PATCH')</form><div class="admin-account-edit-fields"><input class="input" form="{{ $formId }}" name="name" value="{{ $account->name }}" aria-label="Tên tài khoản"><input class="input" form="{{ $formId }}" name="email" type="email" value="{{ $account->email }}" aria-label="Email tài khoản"></div></td>
                                <td><select class="select admin-table-select" form="{{ $formId }}" name="role" aria-label="Vai trò">@foreach($roles as $roleOption)<option value="{{ $roleOption->value }}" @selected($account->role === $roleOption)>{{ strtoupper($roleOption->value) }}</option>@endforeach</select></td>
                                <td><select class="select admin-table-select" form="{{ $formId }}" name="is_active" aria-label="Trạng thái"><option value="1" @selected($account->is_active)>Hoạt động</option><option value="0" @selected(! $account->is_active)>Đã khóa</option></select></td>
                                <td><input class="input admin-table-input" form="{{ $formId }}" name="password" type="password" placeholder="Giữ nguyên" autocomplete="new-password"><input form="{{ $formId }}" name="password_confirmation" type="hidden" value=""></td>
                                <td><button class="admin-icon-button admin-save-button" form="{{ $formId }}" type="submit" title="Lưu thay đổi"><span class="material-symbols-outlined">save</span></button></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="stitch-empty">Không tìm thấy tài khoản phù hợp.</td></tr>
                        @endforelse
                    </tbody></table></div>
                    @if($users->hasPages())<div class="admin-pagination">{{ $users->links() }}</div>@endif
                </div>
            </div>

            <div class="stitch-side-column">
                <div class="stitch-panel">
                    <div class="stitch-side-head"><span class="material-symbols-outlined">person_add</span><div><h3>Thêm tài khoản</h3><span>Tạo người dùng mới</span></div></div>
                    <form method="POST" action="{{ route('admin.accounts.store') }}" class="admin-create-form">
                        @csrf
                        <div class="form-group"><label for="new-name">Họ và tên</label><input id="new-name" class="input admin-form-control" name="name" value="{{ old('name') }}" required></div>
                        <div class="form-group"><label for="new-email">Email</label><input id="new-email" class="input admin-form-control" name="email" type="email" value="{{ old('email') }}" required></div>
                        <div class="form-group"><label for="new-role">Vai trò</label><select id="new-role" class="select admin-form-control" name="role" required>@foreach($roles as $roleOption)<option value="{{ $roleOption->value }}" @selected(old('role', 'employee') === $roleOption->value)>{{ strtoupper($roleOption->value) }}</option>@endforeach</select></div>
                        <div class="form-group"><label for="new-password">Mật khẩu</label><input id="new-password" class="input admin-form-control" name="password" type="password" minlength="8" required></div>
                        <div class="form-group"><label for="new-password-confirmation">Xác nhận mật khẩu</label><input id="new-password-confirmation" class="input admin-form-control" name="password_confirmation" type="password" minlength="8" required></div>
                        <button class="button full" type="submit"><span class="material-symbols-outlined">add</span>Tạo tài khoản</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
