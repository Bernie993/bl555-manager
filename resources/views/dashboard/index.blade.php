@extends('layouts.app')

@section('title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('styles')
<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.text-gray-300 {
    color: #dddfeb !important;
}

.text-gray-800 {
    color: #5a5c69 !important;
}

.card {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
}

.btn-lg {
    padding: 1rem 1.5rem;
    font-size: 1.1rem;
}

.quick-action-card {
    transition: transform 0.2s;
}

.quick-action-card:hover {
    transform: translateY(-5px);
}

.stat-card {
    transition: transform 0.2s;
}

.stat-card:hover {
    transform: translateY(-2px);
}

.stat-card a:hover {
    text-decoration: none;
}

.stat-card a:hover .card {
    box-shadow: 0 0.25rem 2rem 0 rgba(58, 59, 69, 0.25) !important;
    transform: translateY(-2px);
    transition: all 0.2s ease-in-out;
}

/* Clickable card styles */
.clickable-card {
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.clickable-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 2rem 0 rgba(58, 59, 69, 0.3) !important;
}

.clickable-card:active {
    transform: translateY(-2px);
}

.clickable-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.clickable-card:hover::before {
    left: 100%;
}

/* Modal styles */
.modal-detail .modal-content {
    border-radius: 15px;
    border: none;
    box-shadow: 0 1rem 3rem rgba(0,0,0,0.175);
}

.modal-detail .modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px 15px 0 0;
    border: none;
}

.modal-detail .modal-body {
    padding: 2rem;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #e9ecef;
}

.detail-item:last-child {
    border-bottom: none;
}

.detail-label {
    font-weight: 600;
    color: #495057;
}

.detail-value {
    color: #6c757d;
    text-align: right;
}

.detail-value.badge {
    text-align: center;
    min-width: 80px;
}
</style>
@endsection

@section('content')
<!-- Header -->
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-tachometer-alt me-2"></i>
                Dashboard
            </h1>
            <div class="d-flex gap-2">
                @if(auth()->user()->hasRole('partner'))
                <a href="{{ route('services.index') }}" class="btn btn-primary">
                    <i class="fas fa-cogs me-2"></i>
                    Quản lý Dịch vụ
                </a>
                @endif
            </div>
        </div>
    </div>
</div>

@if(auth()->user()->hasRole('seoer'))
    <!-- Seoer Dashboard -->
    <div class="row">
        <!-- Số đơn đã xác nhận -->
        <div class="col-xl-4 col-md-6 mb-4">
            <a href="{{ route('dashboard.seoer.confirmed-orders') }}" class="text-decoration-none">
                <div class="card border-left-primary stat-card h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Số đơn đã xác nhận
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $seoerStats['confirmed_orders'] ?? 0 }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Số đơn đã hoàn thành -->
        <div class="col-xl-4 col-md-6 mb-4">
            <a href="{{ route('dashboard.seoer.completed-orders') }}" class="text-decoration-none">
                <div class="card border-left-success stat-card h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Số đơn đã hoàn thành
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $seoerStats['completed_orders'] ?? 0 }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-double fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Số đơn đang chờ thanh toán -->
        <div class="col-xl-4 col-md-6 mb-4">
            <a href="{{ route('dashboard.seoer.pending-payment-orders') }}" class="text-decoration-none">
                <div class="card border-left-warning stat-card h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Số đơn đang chờ thanh toán
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $seoerStats['pending_payment_orders'] ?? 0 }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-hourglass-half fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Ngân sách -->
    <div class="row">
        <!-- Ngân sách đã tiêu -->
        <div class="col-xl-6 col-md-6 mb-4">
            <a href="{{ route('dashboard.seoer.budget-spent') }}" class="text-decoration-none">
                <div class="card border-left-danger stat-card h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Ngân sách đã tiêu
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($seoerStats['budget_spent'] ?? 0) }} VNĐ</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Ngân sách còn lại -->
        <div class="col-xl-6 col-md-6 mb-4">
            <a href="{{ route('dashboard.seoer.budget-remaining') }}" class="text-decoration-none">
                <div class="card border-left-info stat-card h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Ngân sách còn lại
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($seoerStats['budget_remaining'] ?? 0) }} VNĐ</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-wallet fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Danh sách website được phân công -->
    @if(isset($assignedWebsites) && $assignedWebsites->count() > 0)
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-globe me-2"></i>
                        Website được phân công
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tên website</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày giao</th>
                                    <th>Ngày hết hạn</th>
                                    <th>Ghi chú</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($assignedWebsites as $website)
                                <tr>
                                    <td>
                                        <strong>{{ $website->name }}</strong>
                                        @if($website->cloudflare_zone_id)
                                            <span class="badge bg-info ms-2">CF</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $website->getStatusBadgeClass() }}">
                                            {{ $website->getStatusDisplayName() }}
                                        </span>
                                    </td>
                                    <td>{{ $website->delivery_date ? $website->delivery_date->format('d/m/Y') : '-' }}</td>
                                    <td>{{ $website->expiry_date ? $website->expiry_date->format('d/m/Y') : '-' }}</td>
                                    <td>{{ Str::limit($website->notes ?? '-', 50) }}</td>
                                    <td>
                                        @if(auth()->user()->canAccess('read', 'websites'))
                                        <a href="{{ route('websites.show', $website) }}" class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if(auth()->user()->canAccess('read', 'websites'))
                    <div class="text-center mt-3">
                        <a href="{{ route('websites.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-eye me-1"></i>
                            Xem tất cả website
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Thao tác nhanh cho Seoer -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt me-2"></i>
                        Thao tác nhanh
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @if(auth()->user()->canAccess('read', 'budgets'))
                        <div class="col-md-4 mb-3">
                            <a href="{{ route('budgets.index') }}" class="btn btn-outline-primary btn-lg w-100 quick-action-card">
                                <i class="fas fa-wallet fa-2x mb-2"></i>
                                <br>Quản lý Ngân sách
                            </a>
                        </div>
                        @endif
                        @if(auth()->user()->canAccess('read', 'service_proposals'))
                        <div class="col-md-4 mb-3">
                            <a href="{{ route('service-proposals.index') }}" class="btn btn-outline-success btn-lg w-100 quick-action-card">
                                <i class="fas fa-clipboard-list fa-2x mb-2"></i>
                                <br>Đề xuất Dịch vụ
                            </a>
                        </div>
                        @endif
                        @if(auth()->user()->canAccess('read', 'services'))
                        <div class="col-md-4 mb-3">
                            <a href="{{ route('services.index') }}" class="btn btn-outline-info btn-lg w-100 quick-action-card">
                                <i class="fas fa-cogs fa-2x mb-2"></i>
                                <br>Dịch vụ có sẵn
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

@elseif(auth()->user()->hasRole('partner'))
    <!-- Partner Dashboard -->
    <div class="row">
        <!-- Tổng Website -->
        <div class="col-xl-3 col-md-6 mb-4">
            <a href="{{ route('services.index') }}" class="text-decoration-none">
                <div class="card border-left-primary stat-card h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Số dịch vụ đã đăng
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $partnerStats['total_services'] ?? 0 }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-cogs fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Website hoạt động -->
        <div class="col-xl-3 col-md-6 mb-4">
            <a href="{{ route('service-proposals.index') }}" class="text-decoration-none">
                <div class="card border-left-success stat-card h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Số đề xuất
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $partnerStats['total_proposals'] ?? 0 }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Tổng Ngân sách -->
        <div class="col-xl-3 col-md-6 mb-4">
            <a href="{{ route('withdrawals.index') }}" class="text-decoration-none">
                <div class="card border-left-info stat-card h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Tổng tiền đã rút
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($partnerStats['total_withdrawn'] ?? 0) }} VNĐ</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Người dùng -->
        <div class="col-xl-3 col-md-6 mb-4">
            <a href="{{ route('withdrawals.index') }}" class="text-decoration-none">
                <div class="card border-left-warning stat-card h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Tổng tiền chưa rút
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($partnerStats['total_pending'] ?? 0) }} VNĐ</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-hourglass-half fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Thao tác nhanh -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt me-2"></i>
                        Thao tác nhanh
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <a href="{{ route('services.index') }}" class="btn btn-outline-primary btn-lg w-100 quick-action-card">
                                <i class="fas fa-cogs fa-2x mb-2"></i>
                                <br>Quản lý Dịch vụ
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="{{ route('service-proposals.index') }}" class="btn btn-outline-success btn-lg w-100 quick-action-card">
                                <i class="fas fa-clipboard-list fa-2x mb-2"></i>
                                <br>Đề xuất Dịch vụ
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="{{ route('withdrawals.index') }}" class="btn btn-outline-info btn-lg w-100 quick-action-card">
                                <i class="fas fa-money-bill-wave fa-2x mb-2"></i>
                                <br>Quản lý Rút tiền
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dịch vụ gần đây -->
    @if(isset($recentServices) && $recentServices->count() > 0)
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history me-2"></i>
                        Dịch vụ gần đây
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tên dịch vụ</th>
                                    <th>Loại</th>
                                    <th>Website</th>
                                    <th>Giá</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày tạo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentServices as $service)
                                <tr>
                                    <td>
                                        <strong>{{ $service->name }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $service->getTypeDisplayName() }}</span>
                                    </td>
                                    <td>
                                        @if($service->website)
                                            <a href="{{ $service->website }}" target="_blank" class="text-primary">
                                                {{ parse_url($service->website, PHP_URL_HOST) }}
                                                <i class="fas fa-external-link-alt ms-1"></i>
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong class="text-success">{{ $service->formatted_price }}</strong>
                                    </td>
                                    <td>
                                        @if($service->is_active)
                                            <span class="badge bg-success">Hoạt động</span>
                                        @else
                                            <span class="badge bg-secondary">Tạm dừng</span>
                                        @endif
                                    </td>
                                    <td>{{ $service->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="{{ route('services.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-eye me-1"></i>
                            Xem tất cả dịch vụ
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

@else
    <!-- Admin/IT/Seoer/Assistant Dashboard -->
    <div class="row">
        <!-- Tổng Website -->
        <div class="col-xl-3 col-md-6 mb-4">
            <a href="{{ route('websites.index') }}" class="text-decoration-none">
                <div class="card border-left-primary stat-card shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Tổng Website
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_websites'] ?? 0 }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-globe fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Website hoạt động -->
        <div class="col-xl-3 col-md-6 mb-4">
            <a href="{{ route('websites.index') }}" class="text-decoration-none">
                <div class="card border-left-success stat-card shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Website hoạt động
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active_websites'] ?? 0 }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Tổng Ngân sách -->
        <div class="col-xl-3 col-md-6 mb-4">
            <a href="{{ route('budgets.index') }}" class="text-decoration-none">
                <div class="card border-left-info stat-card shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Tổng Ngân sách
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_budgets'] ?? 0 }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-wallet fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Người dùng -->
        <div class="col-xl-3 col-md-6 mb-4">
            <a href="{{ route('users.index') }}" class="text-decoration-none">
                <div class="card border-left-warning stat-card shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Người dùng
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_users'] ?? 0 }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Website gần đây -->
    @if(isset($recentWebsites) && $recentWebsites->count() > 0)
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history me-2"></i>
                        Website gần đây
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tên miền</th>
                                    <th>Trạng thái</th>
                                    <th>Seoer</th>
                                    <th>Ngày tạo</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentWebsites as $website)
                                <tr>
                                    <td>
                                        <strong>{{ $website->name }}</strong>
                                        @if($website->cloudflare_zone_id)
                                            <span class="badge bg-info ms-2">CF</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($website->status === 'active')
                                            <span class="badge bg-success">Hoạt động</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($website->status) }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $website->seoer->name ?? '-' }}</td>
                                    <td>{{ $website->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <a href="{{ route('websites.show', $website) }}" class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Thống kê ngân sách -->
    @if(isset($budgetSummary) && $budgetSummary)
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie me-2"></i>
                        Thống kê Ngân sách
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="h4 mb-0 text-primary">{{ number_format($budgetSummary->total_budget ?? 0) }} VNĐ</div>
                                <div class="text-muted">Tổng ngân sách</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="h4 mb-0 text-warning">{{ number_format($budgetSummary->total_spent ?? 0) }} VNĐ</div>
                                <div class="text-muted">Đã sử dụng</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="h4 mb-0 text-success">{{ number_format($budgetSummary->total_remaining ?? 0) }} VNĐ</div>
                                <div class="text-muted">Còn lại</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
@endif

@endsection
