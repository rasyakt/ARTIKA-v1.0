@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">

        {{-- Page Header --}}
        <div class="row mb-4 align-items-center">
            <div class="col-12">
                <h4 class="fw-bold mb-1" style="color: var(--color-primary-dark);">
                    <i class="fa-solid fa-code me-2"></i>Developer Tools
                </h4>
                <p class="text-muted mb-0">High-level system management and technical utilities.</p>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <i class="fa-solid fa-circle-xmark me-2"></i>{{ session('error') }}
            </div>
        @endif

        {{-- System Info Card (Full Width) --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
            <div class="card-header bg-white py-3"
                style="border-bottom: 2px solid var(--brown-100); border-radius: 16px 16px 0 0;">
                <h5 class="mb-0 fw-bold" style="color: var(--color-primary-dark);">
                    <i class="fa-solid fa-server me-2"></i>System Information
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="p-3 h-100 bg-light" style="border-radius: 12px;">
                            <label class="text-muted small d-block mb-1">Laravel Version</label>
                            <span class="fw-bold">{{ $systemInfo['laravel_version'] }}</span>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="p-3 h-100 bg-light" style="border-radius: 12px;">
                            <label class="text-muted small d-block mb-1">PHP Version</label>
                            <span class="fw-bold">{{ $systemInfo['php_version'] }}</span>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="p-3 h-100 bg-light" style="border-radius: 12px;">
                            <label class="text-muted small d-block mb-1">Environment</label>
                            <span class="badge {{ $systemInfo['environment'] === 'production' ? 'bg-danger' : 'bg-success' }}"
                                style="border-radius: 8px;">
                                {{ strtoupper($systemInfo['environment']) }}
                            </span>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="p-3 h-100 bg-light" style="border-radius: 12px;">
                            <label class="text-muted small d-block mb-1">Database Type</label>
                            <span class="fw-bold text-uppercase">{{ $systemInfo['db_connection'] }}</span>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="p-3 h-100 bg-light" style="border-radius: 12px;">
                            <label class="text-muted small d-block mb-1">Database Version</label>
                            <span class="fw-bold small">{{ $systemInfo['db_version'] }}</span>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="p-3 h-100 bg-light" style="border-radius: 12px;">
                            <label class="text-muted small d-block mb-1">Server OS</label>
                            <span class="fw-bold">{{ $systemInfo['server_os'] }}</span>
                        </div>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-top text-center">
                    <small class="text-muted">
                        <i class="fa-solid fa-clock me-1"></i>Server Time: {{ $systemInfo['server_time'] }}
                    </small>
                </div>
            </div>
        </div>

        {{-- Developer Actions Row (3 equal cards) --}}
        <div class="row g-4 mb-4">
            {{-- Clear Cache --}}
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 16px;">
                    <div class="card-body p-4 text-center d-flex flex-column">
                        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3 mx-auto"
                            style="width: 64px; height: 64px; color: var(--color-primary-dark);">
                            <i class="fa-solid fa-broom fa-2x"></i>
                        </div>
                        <h5 class="fw-bold mb-2">System Cache</h5>
                        <p class="text-muted small mb-4 flex-grow-1">Clear all application, config, route, and view caches.</p>
                        <form action="{{ route('superadmin.clear-cache') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary w-100" style="border-radius: 12px;">
                                <i class="fa-solid fa-trash-can me-2"></i>Clear Cache
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Optimize --}}
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 16px;">
                    <div class="card-body p-4 text-center d-flex flex-column">
                        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3 mx-auto"
                            style="width: 64px; height: 64px; color: var(--color-primary-dark);">
                            <i class="fa-solid fa-bolt fa-2x"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Optimize</h5>
                        <p class="text-muted small mb-4 flex-grow-1">Cache configuration and routes for production speed.</p>
                        <form action="{{ route('superadmin.optimize') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-outline-success w-100" style="border-radius: 12px;">
                                <i class="fa-solid fa-rocket me-2"></i>Run Optimize
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- System Logs --}}
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 16px;">
                    <div class="card-body p-4 text-center d-flex flex-column">
                        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3 mx-auto"
                            style="width: 64px; height: 64px; color: var(--color-primary-dark);">
                            <i class="fa-solid fa-terminal fa-2x"></i>
                        </div>
                        <h5 class="fw-bold mb-2">System Logs</h5>
                        <p class="text-muted small mb-4 flex-grow-1">Monitor errors and developer-level debug messages.</p>
                        <a href="{{ route('superadmin.logs') }}" class="btn btn-outline-dark w-100" style="border-radius: 12px;">
                            <i class="fa-solid fa-magnifying-glass me-2"></i>View Latest Logs
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Database Overview (Full Width) --}}
        <div class="card border-0 shadow-sm" style="border-radius: 16px;">
            <div class="card-header bg-white py-3"
                style="border-bottom: 2px solid var(--brown-100); border-radius: 16px 16px 0 0;">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fw-bold" style="color: var(--color-primary-dark);">
                        <i class="fa-solid fa-database me-2"></i>Database Overview
                    </h5>
                    <span class="badge bg-light text-muted fw-normal px-3 py-2" style="border-radius: 10px; font-size: 0.8rem;">
                        {{ count($dbStats) }} tables
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light sticky-top">
                            <tr>
                                <th class="ps-4 py-3 text-muted small text-uppercase fw-bold">#</th>
                                <th class="py-3 text-muted small text-uppercase fw-bold">Table Name</th>
                                <th class="py-3 text-muted small text-uppercase fw-bold text-end pe-4">Total Records</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dbStats as $index => $stat)
                                <tr>
                                    <td class="ps-4 text-muted small">{{ $index + 1 }}</td>
                                    <td>
                                        <span class="fw-medium">{{ $stat['name'] }}</span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <span class="badge bg-light text-dark px-3 py-2"
                                            style="border-radius: 8px; font-size: 0.9rem;">
                                            {{ number_format($stat['count']) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
@endsection