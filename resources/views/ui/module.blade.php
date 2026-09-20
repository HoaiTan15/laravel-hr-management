@extends('layouts.app')

@section('title', $title . ' · HUIT HRMs')

@section('content')
    <div class="page-header">
        <div>
            <div class="breadcrumb">{{ $role }} / {{ $title }}</div>
            <h1>{{ $title }}</h1>
            <p style="margin:8px 0 0; color:#64748b; font-size:14px;">{{ $description }}</p>
        </div>
        <span style="display:inline-flex; align-items:center; gap:6px; padding:6px 10px; border-radius:999px; background:#fff7ed; color:#c2410c; font-size:12px; font-weight:600;">UI preview</span>
    </div>

    <section class="card" style="margin-bottom:24px;">
        <div class="card-body">
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:16px;">
                <div style="padding:16px; background:#eff4ff; border-radius:12px;"><div style="color:#64748b; font-size:12px;">Trạng thái màn hình</div><strong style="display:block; margin-top:6px; color:#004385; font-size:18px;">Đã sẵn sàng</strong></div>
                <div style="padding:16px; background:#f8fafc; border-radius:12px;"><div style="color:#64748b; font-size:12px;">Backend module</div><strong style="display:block; margin-top:6px; color:#334155; font-size:18px;">Đang triển khai</strong></div>
                <div style="padding:16px; background:#fff7ed; border-radius:12px;"><div style="color:#64748b; font-size:12px;">Quyền truy cập</div><strong style="display:block; margin-top:6px; color:#c2410c; font-size:18px;">{{ $role }}</strong></div>
            </div>
        </div>
    </section>

    <section class="card">
        <div class="card-body">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom:16px;">
                <div>
                    <h2 style="margin:0; color:#0f172a; font-size:18px;">{{ $sectionTitle ?? 'Nội dung màn hình' }}</h2>
                    <p style="margin:6px 0 0; color:#64748b; font-size:13px;">Giao diện đã được dựng theo Stitch. Chức năng backend sẽ được nối ở phase tương ứng.</p>
                </div>
                <span style="color:#94a3b8; font-size:13px;">{{ $role }} workspace</span>
            </div>
            <div style="padding:32px 20px; text-align:center; border:1px dashed #cbd5e1; border-radius:12px; color:#64748b;">
                Chưa có dữ liệu nghiệp vụ để hiển thị trên màn hình này.
            </div>
        </div>
    </section>
@endsection
