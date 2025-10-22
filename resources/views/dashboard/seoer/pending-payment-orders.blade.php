@extends('layouts.app')

@section('title', 'Đơn hàng chờ thanh toán')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Đơn hàng chờ thanh toán</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-hourglass-half me-2 text-warning"></i>
                Đơn hàng chờ thanh toán
            </h1>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Quay lại Dashboard
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-warning">
                    <i class="fas fa-list me-2"></i>
                    Danh sách đơn hàng chờ thanh toán ({{ $pendingPaymentOrders->total() }} đơn)
                </h6>
            </div>
            <div class="card-body">
                @if($pendingPaymentOrders->count() > 0)
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Thông tin:</strong> Các đơn hàng này đã được quản lý xác nhận hoàn thành và đang chờ trợ lý xác nhận thanh toán.
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tên dịch vụ</th>
                                    <th>Domain đích</th>
                                    <th>Nhà cung cấp</th>
                                    <th>Số lượng</th>
                                    <th>Số tiền</th>
                                    <th>Ngân sách</th>
                                    <th>Ngày hoàn thành</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingPaymentOrders as $order)
                                <tr>
                                    <td>
                                        <span class="badge bg-secondary">#{{ $order->id }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $order->service_name }}</strong>
                                        @if($order->service)
                                            <br><small class="text-muted">{{ $order->service->partner->name ?? 'N/A' }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($order->target_domain)
                                            <a href="http://{{ $order->target_domain }}" target="_blank" class="text-primary">
                                                {{ $order->target_domain }}
                                                <i class="fas fa-external-link-alt ms-1"></i>
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $order->supplier_name }}</strong>
                                            @if($order->supplier_telegram)
                                                <br><small class="text-muted">@{{ $order->supplier_telegram }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $order->quantity }}</span>
                                    </td>
                                    <td>
                                        <strong class="text-success">{{ number_format($order->amount, 0, ',', '.') }} VNĐ</strong>
                                        <br><small class="text-muted">{{ number_format($order->unit_price, 0, ',', '.') }} VNĐ/đơn vị</small>
                                    </td>
                                    <td>
                                        @if($order->budget)
                                            <span class="badge bg-primary">{{ $order->budget->name }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($order->admin_completed_at)
                                            <div>
                                                <strong>{{ $order->admin_completed_at->format('d/m/Y') }}</strong>
                                                <br><small class="text-muted">{{ $order->admin_completed_at->format('H:i') }}</small>
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('service-proposals.show', $order) }}" class="btn btn-sm btn-outline-info" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $pendingPaymentOrders->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-hourglass-half fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Không có đơn hàng nào đang chờ thanh toán</h5>
                        <p class="text-muted">Tất cả đơn hàng đã được thanh toán hoặc chưa đến bước chờ thanh toán.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
