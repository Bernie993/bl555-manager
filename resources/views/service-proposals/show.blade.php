@extends('layouts.app')

@section('title', 'Chi tiết Đề xuất Dịch vụ')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('service-proposals.index') }}">Quản lý Đề xuất Dịch vụ</a></li>
    <li class="breadcrumb-item active">Chi tiết Đề xuất</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-clipboard-list me-2"></i>
                    Chi tiết Đề xuất: {{ $serviceProposal->service_name }}
                </h1>
                <div class="btn-group">
                    <a href="{{ route('service-proposals.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Quay lại
                    </a>

                    @if($serviceProposal->created_by === auth()->id() && $serviceProposal->status === 'pending')
                        <a href="{{ route('service-proposals.edit', $serviceProposal) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>
                            Chỉnh sửa
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Service Information -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Thông tin Dịch vụ
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-3">
                            <strong>Mã đơn hàng:</strong>
                        </div>
                        <div class="col-sm-9">
                            <h5 class="text-primary">{{ $serviceProposal->order_code }}</h5>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-3">
                            <strong>Tên dịch vụ:</strong>
                        </div>
                        <div class="col-sm-9">
                            <h5 class="text-primary">{{ $serviceProposal->service_name }}</h5>
                        </div>
                    </div>

                    @if($serviceProposal->target_domain)
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <strong>Domain đích:</strong>
                            </div>
                            <div class="col-sm-9">
                        <span class="badge bg-primary fs-6">
                            <i class="fas fa-globe me-1"></i>
                            {{ $serviceProposal->target_domain }}
                        </span>
                            </div>
                        </div>
                    @endif

                    <div class="row mb-3">
                        <div class="col-sm-3">
                            <strong>Số lượng:</strong>
                        </div>
                        <div class="col-sm-9">
                            <span class="badge bg-info fs-6">{{ $serviceProposal->quantity }}</span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-3">
                            <strong>Số tiền:</strong>
                        </div>
                        <div class="col-sm-9">
                            <h4 class="text-success">{{ $serviceProposal->formatted_amount }}</h4>
                            @if($serviceProposal->quantity > 1)
                                <small class="text-muted">
                                    Đơn giá: {{ number_format($serviceProposal->amount / $serviceProposal->quantity, 0, ',', '.') }} VNĐ
                                </small>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-3">
                            <strong>Trạng thái:</strong>
                        </div>
                        <div class="col-sm-9">
                        <span class="badge {{ $serviceProposal->getStatusBadgeClass() }} fs-6">
                            {{ $serviceProposal->getStatusDisplayName() }}
                        </span>
                        </div>
                    </div>

                    @if($serviceProposal->notes)
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <strong>Ghi chú:</strong>
                            </div>
                            <div class="col-sm-9">
                                <div class="bg-light p-3 rounded">
                                    {{ $serviceProposal->notes }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Supplier Information -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-building me-2"></i>
                        Thông tin Nhà cung cấp
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-3">
                            <strong>Tên NCC:</strong>
                        </div>
                        <div class="col-sm-9">
                            {{ $serviceProposal->supplier_name }}
                        </div>
                    </div>

                    @if($serviceProposal->proposal_link)
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <strong>Link đề xuất:</strong>
                            </div>
                            <div class="col-sm-9">
                                <a href="{{ $serviceProposal->proposal_link }}" target="_blank" class="text-decoration-none text-primary">
                                    <i class="fas fa-external-link-alt me-2"></i>
                                    {{ $serviceProposal->proposal_link }}
                                </a>
                            </div>
                        </div>
                    @endif

                    @if($serviceProposal->supplier_telegram)
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <strong>Telegram:</strong>
                            </div>
                            <div class="col-sm-9">
                                @if(str_starts_with($serviceProposal->supplier_telegram, '@') || str_starts_with($serviceProposal->supplier_telegram, 'http'))
                                    <a href="{{ str_starts_with($serviceProposal->supplier_telegram, 'http') ? $serviceProposal->supplier_telegram : 'https://t.me/' . ltrim($serviceProposal->supplier_telegram, '@') }}"
                                       target="_blank" class="text-decoration-none">
                                        <i class="fab fa-telegram me-2"></i>
                                        {{ $serviceProposal->supplier_telegram }}
                                    </a>
                                @else
                                    <i class="fab fa-telegram me-2"></i>
                                    {{ $serviceProposal->supplier_telegram }}
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Timeline -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>
                        Lịch sử Thay đổi
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Tạo đề xuất</h6>
                                <p class="timeline-text">
                                    Được tạo bởi <strong>{{ $serviceProposal->creator->name }}</strong>
                                </p>
                                <small class="text-muted">{{ $serviceProposal->created_at->format('d/m/Y H:i') }}</small>
                            </div>
                        </div>

                        @if($serviceProposal->approved_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-{{ $serviceProposal->status === 'rejected' ? 'danger' : 'success' }}"></div>
                                <div class="timeline-content">
                                    <h6 class="timeline-title">
                                        {{ $serviceProposal->status === 'rejected' ? 'Bị từ chối' : 'Đã duyệt' }}
                                    </h6>
                                    <p class="timeline-text">
                                        Bởi <strong>{{ $serviceProposal->approver->name ?? 'N/A' }}</strong>
                                    </p>
                                    <small class="text-muted">{{ $serviceProposal->approved_at->format('d/m/Y H:i') }}</small>
                                </div>
                            </div>
                        @endif

                        @if($serviceProposal->partner_confirmed_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-primary"></div>
                                <div class="timeline-content">
                                    <h6 class="timeline-title">Đối tác xác nhận</h6>
                                    <p class="timeline-text">
                                        Bởi <strong>{{ $serviceProposal->partnerConfirmer->name ?? 'N/A' }}</strong>
                                    </p>
                                    <small class="text-muted">{{ $serviceProposal->partner_confirmed_at->format('d/m/Y H:i') }}</small>
                                </div>
                            </div>
                        @endif

                        @if($serviceProposal->partner_completed_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-dark"></div>
                                <div class="timeline-content">
                                    <h6 class="timeline-title">Đối tác xác nhận hoàn thành</h6>
                                    <p class="timeline-text">
                                        Bởi <strong>{{ $serviceProposal->partnerCompleter->name ?? 'N/A' }}</strong>
                                    </p>
                                    <small class="text-muted">{{ $serviceProposal->partner_completed_at->format('d/m/Y H:i') }}</small>
                                </div>
                            </div>
                        @endif

                        @if($serviceProposal->admin_completed_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="timeline-title">Quản lý xác nhận hoàn thành</h6>
                                    <p class="timeline-text">
                                        Bởi <strong>{{ $serviceProposal->adminCompleter->name ?? 'N/A' }}</strong>
                                    </p>
                                    <small class="text-muted">{{ $serviceProposal->admin_completed_at->format('d/m/Y H:i') }}</small>
                                </div>
                            </div>
                        @endif

                        @if($serviceProposal->payment_confirmed_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="timeline-title">Đã thanh toán</h6>
                                    <p class="timeline-text">
                                        Bởi <strong>{{ $serviceProposal->paymentConfirmer->name ?? 'N/A' }}</strong>
                                    </p>
                                    <small class="text-muted">{{ $serviceProposal->payment_confirmed_at->format('d/m/Y H:i') }}</small>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Actions -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-cog me-2"></i>
                        Thao tác
                    </h6>
                </div>
                <div class="card-body">
                    @php
                        $availableActions = $serviceProposal->getAvailableActionsFor(auth()->user());
                    @endphp

                    @if(count($availableActions) > 0)
                        <div class="d-grid gap-2 mb-3">
                            @foreach($availableActions as $action)
                                @php
                                    $routeName = match($action['action']) {
                                        'approve' => 'service-proposals.approve',
                                        'reject' => 'service-proposals.reject',
                                        'partner_confirm' => 'service-proposals.partner-confirm',
                                        'partner_complete' => 'service-proposals.partner-complete',
                                        'admin_complete' => 'service-proposals.admin-complete',
                                        'payment_confirm' => 'service-proposals.payment-confirm',
                                        default => null
                                    };

                                    $icon = match($action['action']) {
                                        'approve' => 'fas fa-check',
                                        'reject' => 'fas fa-times',
                                        'partner_confirm' => 'fas fa-handshake',
                                        'partner_complete' => 'fas fa-check-circle',
                                        'admin_complete' => 'fas fa-check-double',
                                        'payment_confirm' => 'fas fa-money-check-alt',
                                        default => 'fas fa-cog'
                                    };
                                @endphp

                                @if($routeName)
                                    <form action="{{ route($routeName, $serviceProposal) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn {{ $action['class'] }} w-100">
                                            <i class="{{ $icon }} me-2"></i>
                                            {{ $action['label'] }}
                                        </button>
                                    </form>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted">
                            <i class="fas fa-info-circle me-2"></i>
                            Không có thao tác nào khả dụng
                        </div>
                    @endif

                    @if($serviceProposal->created_by === auth()->id() && $serviceProposal->status === 'pending')
                        <div class="d-grid gap-2">
                            <form action="{{ route('service-proposals.destroy', $serviceProposal) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger w-100 btn-delete">
                                    <i class="fas fa-trash me-2"></i>
                                    Xóa đề xuất
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Budget Information -->
            @if($serviceProposal->budget)
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-wallet me-2"></i>
                            Ngân sách liên kết
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="small">
                            <div class="row">
                                <div class="col-6">
                                    <strong>Seoer:</strong>
                                </div>
                                <div class="col-6">
                                    {{ $serviceProposal->budget->seoer }}
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <strong>Tổng ngân sách:</strong>
                                </div>
                                <div class="col-6">
                                    {{ $serviceProposal->budget->formatted_total_budget }}
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <strong>Còn lại:</strong>
                                </div>
                                <div class="col-6">
                                    {{ $serviceProposal->budget->formatted_remaining_amount }}
                                </div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <a href="{{ route('budgets.show', $serviceProposal->budget) }}" class="btn btn-sm btn-outline-primary w-100">
                                <i class="fas fa-eye me-2"></i>
                                Xem chi tiết ngân sách
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Workflow Guide -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-route me-2"></i>
                        Quy trình
                    </h6>
                </div>
                <div class="card-body">
                    <div class="small">
                        <div class="workflow-step {{ $serviceProposal->status === 'pending' ? 'active' : ($serviceProposal->status !== 'rejected' ? 'completed' : 'skipped') }}">
                            <i class="fas fa-clock me-2"></i>
                            Chờ duyệt
                        </div>
                        <div class="workflow-step {{ $serviceProposal->status === 'approved' ? 'active' : (in_array($serviceProposal->status, ['confirmed', 'completed']) ? 'completed' : 'pending') }}">
                            <i class="fas fa-check me-2"></i>
                            Đã duyệt
                        </div>
                        <div class="workflow-step {{ $serviceProposal->status === 'confirmed' ? 'active' : ($serviceProposal->status === 'completed' ? 'completed' : 'pending') }}">
                            <i class="fas fa-handshake me-2"></i>
                            Xác nhận đơn hàng
                        </div>
                        <div class="workflow-step {{ $serviceProposal->status === 'completed' ? 'active' : 'pending' }}">
                            <i class="fas fa-money-check-alt me-2"></i>
                            Hoàn thành thanh toán
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #dee2e6;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }

        .timeline-marker {
            position: absolute;
            left: -22px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 2px solid #fff;
        }

        .timeline-content {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 3px solid #007bff;
        }

        .timeline-title {
            margin-bottom: 5px;
            font-weight: 600;
        }

        .timeline-text {
            margin-bottom: 5px;
        }

        .workflow-step {
            padding: 8px 12px;
            margin-bottom: 8px;
            border-radius: 5px;
            border-left: 4px solid #dee2e6;
            background: #f8f9fa;
        }

        .workflow-step.active {
            border-left-color: #007bff;
            background: #e3f2fd;
            font-weight: 600;
        }

        .workflow-step.completed {
            border-left-color: #28a745;
            background: #d4edda;
        }

        .workflow-step.skipped {
            border-left-color: #dc3545;
            background: #f8d7da;
            opacity: 0.7;
        }
    </style>
@endsection


