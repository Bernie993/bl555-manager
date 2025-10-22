@extends('layouts.app')

@section('title', 'Ngân sách đã tiêu')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Ngân sách đã tiêu</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-money-bill-wave me-2 text-danger"></i>
                Ngân sách đã tiêu
            </h1>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Quay lại Dashboard
            </a>
        </div>
    </div>
</div>

<!-- Summary Card -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-left-danger shadow">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            Tổng ngân sách đã tiêu
                        </div>
                        <div class="h3 mb-0 font-weight-bold text-gray-800">{{ number_format($totalSpent, 0, ',', '.') }} VNĐ</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-danger">
                    <i class="fas fa-list me-2"></i>
                    Chi tiết các đơn hàng đã thanh toán ({{ $spentOrders->total() }} đơn)
                </h6>
            </div>
            <div class="card-body">
                @if($spentOrders->count() > 0)
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
                                    <th>Trạng thái thanh toán</th>
                                    <th>Ngân sách</th>
                                    <th>Ngày thanh toán</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($spentOrders as $order)
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
                                        @php
                                            $totalWithdrawn = $order->getTotalWithdrawnAmount();
                                            $percentage = $totalWithdrawn > 0 ? round(($totalWithdrawn / $order->amount) * 100) : 0;
                                        @endphp
                                        @if($percentage >= 100)
                                            <span class="badge bg-success">Đã thanh toán hết</span>
                                        @elseif($percentage > 0)
                                            <span class="badge bg-warning">Đã thanh toán {{ $percentage }}%</span>
                                        @else
                                            <span class="badge bg-secondary">Chưa thanh toán</span>
                                        @endif
                                        <br><small class="text-muted">{{ number_format($totalWithdrawn, 0, ',', '.') }} / {{ number_format($order->amount, 0, ',', '.') }} VNĐ</small>
                                    </td>
                                    <td>
                                        @if($order->budget)
                                            <span class="badge bg-primary">{{ $order->budget->name }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($order->payment_confirmed_at)
                                            <div>
                                                <strong>{{ $order->payment_confirmed_at->format('d/m/Y') }}</strong>
                                                <br><small class="text-muted">{{ $order->payment_confirmed_at->format('H:i') }}</small>
                                            </div>
                                        @elseif($order->completed_at)
                                            <div>
                                                <strong>{{ $order->completed_at->format('d/m/Y') }}</strong>
                                                <br><small class="text-muted">{{ $order->completed_at->format('H:i') }}</small>
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
                        {{ $spentOrders->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-money-bill-wave fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Chưa có ngân sách nào đã được tiêu</h5>
                        <p class="text-muted">Các đơn hàng đã thanh toán sẽ hiển thị ở đây.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
