@extends('layouts.app')

@section('title', 'Ngân sách còn lại')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Ngân sách còn lại</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-wallet me-2 text-info"></i>
                Ngân sách còn lại
            </h1>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Quay lại Dashboard
            </a>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card border-left-info shadow">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Tổng ngân sách
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($budgetSummary->total_budget ?? 0, 0, ',', '.') }} VNĐ</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-wallet fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card border-left-danger shadow">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            Đã sử dụng
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($budgetSummary->total_spent ?? 0, 0, ',', '.') }} VNĐ</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card border-left-success shadow">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Còn lại
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($budgetSummary->total_remaining ?? 0, 0, ',', '.') }} VNĐ</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-piggy-bank fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Progress Bar -->
@if($budgetSummary && $budgetSummary->total_budget > 0)
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-body">
                <h6 class="card-title">Tỷ lệ sử dụng ngân sách</h6>
                @php
                    $usedPercentage = $budgetSummary->total_budget > 0 ? round(($budgetSummary->total_spent / $budgetSummary->total_budget) * 100) : 0;
                    $remainingPercentage = 100 - $usedPercentage;
                @endphp
                <div class="progress mb-2" style="height: 25px;">
                    <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $usedPercentage }}%" aria-valuenow="{{ $usedPercentage }}" aria-valuemin="0" aria-valuemax="100">
                        {{ $usedPercentage }}% đã sử dụng
                    </div>
                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $remainingPercentage }}%" aria-valuenow="{{ $remainingPercentage }}" aria-valuemin="0" aria-valuemax="100">
                        {{ $remainingPercentage }}% còn lại
                    </div>
                </div>
                <div class="d-flex justify-content-between">
                    <small class="text-muted">Đã sử dụng: {{ number_format($budgetSummary->total_spent ?? 0, 0, ',', '.') }} VNĐ</small>
                    <small class="text-muted">Còn lại: {{ number_format($budgetSummary->total_remaining ?? 0, 0, ',', '.') }} VNĐ</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-info">
                    <i class="fas fa-list me-2"></i>
                    Chi tiết các ngân sách ({{ $budgets->count() }} ngân sách)
                </h6>
            </div>
            <div class="card-body">
                @if($budgets->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tên ngân sách</th>
                                    <th>Tổng ngân sách</th>
                                    <th>Đã sử dụng</th>
                                    <th>Còn lại</th>
                                    <th>Tỷ lệ sử dụng</th>
                                    <th>Ngày tạo</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($budgets as $budget)
                                @php
                                    $budgetUsedPercentage = $budget->total_budget > 0 ? round(($budget->spent_amount / $budget->total_budget) * 100) : 0;
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $budget->name }}</strong>
                                        @if($budget->description)
                                            <br><small class="text-muted">{{ Str::limit($budget->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <strong class="text-primary">{{ number_format($budget->total_budget, 0, ',', '.') }} VNĐ</strong>
                                    </td>
                                    <td>
                                        <strong class="text-danger">{{ number_format($budget->spent_amount, 0, ',', '.') }} VNĐ</strong>
                                    </td>
                                    <td>
                                        <strong class="text-success">{{ number_format($budget->remaining_amount, 0, ',', '.') }} VNĐ</strong>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1 me-2" style="height: 20px;">
                                                <div class="progress-bar 
                                                    @if($budgetUsedPercentage >= 90) bg-danger
                                                    @elseif($budgetUsedPercentage >= 70) bg-warning
                                                    @else bg-success
                                                    @endif" 
                                                    role="progressbar" 
                                                    style="width: {{ $budgetUsedPercentage }}%" 
                                                    aria-valuenow="{{ $budgetUsedPercentage }}" 
                                                    aria-valuemin="0" 
                                                    aria-valuemax="100">
                                                </div>
                                            </div>
                                            <span class="badge 
                                                @if($budgetUsedPercentage >= 90) bg-danger
                                                @elseif($budgetUsedPercentage >= 70) bg-warning
                                                @else bg-success
                                                @endif">
                                                {{ $budgetUsedPercentage }}%
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $budget->created_at->format('d/m/Y') }}</strong>
                                            <br><small class="text-muted">{{ $budget->created_at->format('H:i') }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ route('budgets.show', $budget) }}" class="btn btn-sm btn-outline-info" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-wallet fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Chưa có ngân sách nào</h5>
                        <p class="text-muted">Liên hệ quản lý để được tạo ngân sách.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
