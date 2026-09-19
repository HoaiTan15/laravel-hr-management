@extends('layouts.app')

@section('title', $role . ' · HUIT HRMs')

@section('content')
    <div class="page-header">
        <div>
            <div class="breadcrumb">Workspace / {{ $role }}</div>
            <h1>{{ $role }} dashboard</h1>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(190px, 1fr)); gap:16px; margin-bottom:24px;">
        @foreach ($stats as $stat)
            <section class="card">
                <div class="card-body">
                    <p style="margin:0 0 8px; color:#64748b; font-size:13px;">{{ $stat['label'] }}</p>
                    <strong style="font-size:28px; color:#004385;">{{ number_format($stat['value']) }}</strong>
                </div>
            </section>
        @endforeach
    </div>

    <section class="card">
        <div class="card-body">
            <h2 style="margin:0 0 8px; font-size:18px;">Tổng quan {{ $role }}</h2>
            <p style="margin:0; color:#64748b; font-size:14px;">Số liệu trên dashboard được lấy trực tiếp từ database. Các module nghiệp vụ sẽ được triển khai theo từng phase.</p>
        </div>
    </section>
@endsection
