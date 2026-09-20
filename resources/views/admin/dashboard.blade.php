@extends('layouts.app')
@section('title', 'Dashboard Admin · HUIT HRMS')
@section('content')
<div class="page"><div class="crumb"><strong>Hệ thống quản trị</strong> / Dashboard Admin</div><h1>Dashboard Admin</h1><p class="subtitle">Tổng quan tài khoản và phiếu yêu cầu trong hệ thống.</p><div class="grid grid-4"><div class="card stat"><div class="stat-label">Tổng tài khoản</div><div class="stat-value">{{ $stats[0]['value'] ?? 0 }}</div></div><div class="card stat"><div class="stat-label">Đang hoạt động</div><div class="stat-value">{{ $stats[1]['value'] ?? 0 }}</div></div><div class="card stat"><div class="stat-label">PYC chờ xử lý</div><div class="stat-value">{{ $stats[2]['value'] ?? 0 }}</div></div><div class="card stat"><div class="stat-label">PYC đã xử lý</div><div class="stat-value">{{ $stats[3]['value'] ?? 0 }}</div></div></div></div>
@endsection
