@extends('layouts.app')

@section('title', 'Chi tiết Ngân sách')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('budgets.index') }}">Quản lý Ngân sách</a></li>
    <li class="breadcrumb-item active">Chi tiết Ngân sách</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-eye me-2"></i>
                Chi tiết Ngân sách: {{ $budget->seoer }}
            </h1>
            <div class="btn-group">
                <a href="{{ route('budgets.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>
                    Quay lại
                </a>
                @if(auth()->user()->hasPermission('budgets.update'))
                <a href="{{ route('budgets.edit', $budget) }}" class="btn btn-primary">
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
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Thông tin Ngân sách
                </h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Seoer:</strong>
                    </div>
                    <div class="col-sm-9">
                        {{ $budget->seoer }}
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Tổng ngân sách:</strong>
                    </div>
                    <div class="col-sm-9">
                        <span class="h5 text-primary">{{ $budget->formatted_total_budget }}</span>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Đã chi tiêu:</strong>
                    </div>
                    <div class="col-sm-9">
                        <span class="h5 text-danger">{{ $budget->formatted_spent_amount }}</span>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Còn lại:</strong>
                    </div>
                    <div class="col-sm-9">
                        <span class="h5 text-success">{{ $budget->formatted_remaining_amount }}</span>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Tiến độ chi tiêu:</strong>
                    </div>
                    <div class="col-sm-9">
                        @php
                            $percentage = $budget->spending_percentage;
                            $progressClass = $percentage > 80 ? 'bg-danger' : ($percentage > 60 ? 'bg-warning' : 'bg-success');
                        @endphp
                        <div class="progress mb-2" style="height: 25px;">
                            <div class="progress-bar {{ $progressClass }}" 
                                 role="progressbar" 
                                 style="width: {{ $percentage }}%">
                                {{ number_format($percentage, 1) }}%
                            </div>
                        </div>
                        <small class="text-muted">
                            Đã sử dụng {{ number_format($percentage, 1) }}% ngân sách
                        </small>
                    </div>
                </div>
                
                @if($budget->period_start || $budget->period_end)
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Kỳ hạn:</strong>
                    </div>
                    <div class="col-sm-9">
                        @if($budget->period_start && $budget->period_end)
                            {{ $budget->period_start->format('d/m/Y') }} - {{ $budget->period_end->format('d/m/Y') }}
                            <br>
                            <small class="text-muted">
                                ({{ $budget->period_start->diffInDays($budget->period_end) }} ngày)
                            </small>
                        @elseif($budget->period_start)
                            Từ {{ $budget->period_start->format('d/m/Y') }}
                        @elseif($budget->period_end)
                            Đến {{ $budget->period_end->format('d/m/Y') }}
                        @endif
                    </div>
                </div>
                @endif
                
                @if($budget->description)
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Mô tả:</strong>
                    </div>
                    <div class="col-sm-9">
                        <div class="bg-light p-3 rounded">
                            {!! nl2br(e($budget->description)) !!}
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Chi tiết Dịch vụ đã sử dụng ngân sách -->
        <div class="card mt-4 service-details-card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>
                    Chi tiết Dịch vụ đã sử dụng ngân sách
                </h5>
            </div>
            <div class="card-body">
                @if($budget->serviceProposals->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Dịch vụ</th>
                                    <th>Domain đích</th>
                                    <th>Số lượng</th>
                                    <th>Nhà cung cấp</th>
                                    <th>Số tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày tạo</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($budget->serviceProposals as $proposal)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $proposal->service_name }}</div>
                                        @if($proposal->service)
                                            <small class="text-muted">{{ $proposal->service->type ?? 'N/A' }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-break">{{ $proposal->target_domain }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $proposal->quantity }}</span>
                                    </td>
                                    <td>
                                        <div>{{ $proposal->supplier_name }}</div>
                                        @if($proposal->supplier_telegram)
                                            <small class="text-muted">
                                                <i class="fab fa-telegram me-1"></i>
                                                {{ $proposal->supplier_telegram }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="fw-bold text-primary">{{ $proposal->formatted_amount }}</span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $proposal->getStatusBadgeClass() }}">
                                            {{ $proposal->getStatusDisplayName() }}
                                        </span>
                                    </td>
                                    <td>
                                        <small>{{ $proposal->created_at->format('d/m/Y H:i') }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('service-proposals.show', $proposal) }}" 
                                               class="btn btn-outline-primary btn-sm" 
                                               title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($proposal->proposal_link)
                                                <a href="{{ $proposal->proposal_link }}" 
                                                   target="_blank" 
                                                   class="btn btn-outline-info btn-sm" 
                                                   title="Xem đề xuất">
                                                    <i class="fas fa-external-link-alt"></i>
                                                </a>
                                            @endif
                                            @if($proposal->result_link)
                                                <a href="{{ $proposal->result_link }}" 
                                                   target="_blank" 
                                                   class="btn btn-outline-success btn-sm" 
                                                   title="Xem kết quả">
                                                    <i class="fas fa-link"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="4" class="text-end">Tổng cộng:</th>
                                    <th class="text-primary">
                                        {{ number_format($budget->serviceProposals->sum('amount'), 0, ',', '.') }} VNĐ
                                    </th>
                                    <th colspan="3"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <!-- Thống kê nhanh -->
                    <div class="row mt-3">
                        <div class="col-md-3">
                            <div class="card bg-light stats-card">
                                <div class="card-body text-center">
                                    <h6 class="card-title text-muted">Tổng đề xuất</h6>
                                    <h4 class="text-primary">{{ $budget->serviceProposals->count() }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light stats-card">
                                <div class="card-body text-center">
                                    <h6 class="card-title text-muted">Đã hoàn thành</h6>
                                    <h4 class="text-success">
                                        {{ $budget->serviceProposals->where('status', 'completed')->count() }}
                                    </h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light stats-card">
                                <div class="card-body text-center">
                                    <h6 class="card-title text-muted">Đang xử lý</h6>
                                    <h4 class="text-warning">
                                        {{ $budget->serviceProposals->whereIn('status', ['pending', 'approved', 'partner_confirmed', 'partner_completed', 'seoer_confirmed', 'admin_completed'])->count() }}
                                    </h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light stats-card">
                                <div class="card-body text-center">
                                    <h6 class="card-title text-muted">Từ chối</h6>
                                    <h4 class="text-danger">
                                        {{ $budget->serviceProposals->where('status', 'rejected')->count() }}
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-5 empty-state">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Chưa có dịch vụ nào sử dụng ngân sách này</h5>
                        <p class="text-muted">Các đề xuất dịch vụ liên kết với ngân sách này sẽ hiển thị ở đây.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-chart-pie me-2"></i>
                    Thống kê
                </h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="progress-circle" data-percentage="{{ $percentage }}">
                        <span class="progress-value">{{ number_format($percentage, 1) }}%</span>
                    </div>
                </div>
                
                <div class="row text-center">
                    <div class="col-12 mb-3">
                        <div class="border-bottom pb-2">
                            <div class="h6 mb-1 text-primary">Tổng ngân sách</div>
                            <div class="h5 mb-0">{{ $budget->formatted_total_budget }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="h6 mb-1 text-danger">Đã chi</div>
                        <div class="h6 mb-0">{{ $budget->formatted_spent_amount }}</div>
                    </div>
                    <div class="col-6">
                        <div class="h6 mb-1 text-success">Còn lại</div>
                        <div class="h6 mb-0">{{ $budget->formatted_remaining_amount }}</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-clock me-2"></i>
                    Thông tin thời gian
                </h6>
            </div>
            <div class="card-body">
                <div class="small">
                    <div class="row mb-2">
                        <div class="col-6">
                            <strong>Tạo lúc:</strong>
                        </div>
                        <div class="col-6">
                            {{ $budget->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <strong>Cập nhật:</strong>
                        </div>
                        <div class="col-6">
                            {{ $budget->updated_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        @if(auth()->user()->hasPermission('budgets.update') || auth()->user()->hasPermission('budgets.delete'))
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-cogs me-2"></i>
                    Thao tác
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if(auth()->user()->hasPermission('budgets.update'))
                    <a href="{{ route('budgets.edit', $budget) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-2"></i>
                        Chỉnh sửa
                    </a>
                    @endif
                    
                    @if(auth()->user()->hasPermission('budgets.delete'))
                    <form action="{{ route('budgets.destroy', $budget) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100 btn-delete">
                            <i class="fas fa-trash me-2"></i>
                            Xóa Ngân sách
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<style>
.progress-circle {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: conic-gradient(#007bff 0deg, #007bff {{ $percentage * 3.6 }}deg, #e9ecef {{ $percentage * 3.6 }}deg, #e9ecef 360deg);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    position: relative;
}

.progress-circle::before {
    content: '';
    width: 90px;
    height: 90px;
    border-radius: 50%;
    background: white;
    position: absolute;
}

.progress-value {
    position: relative;
    z-index: 1;
    font-weight: bold;
    font-size: 16px;
    color: #007bff;
}

/* Styling cho section dịch vụ mới */
.service-details-card {
    border-left: 4px solid #007bff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.service-details-card .card-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-bottom: 1px solid #dee2e6;
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

.badge {
    font-size: 0.75em;
    padding: 0.375rem 0.5rem;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.4rem;
    font-size: 0.75rem;
}

.text-break {
    word-break: break-all;
    max-width: 200px;
}

/* Responsive table */
@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .btn-group-sm .btn {
        padding: 0.2rem 0.3rem;
        font-size: 0.7rem;
    }
    
    .text-break {
        max-width: 150px;
    }
}

/* Statistics cards */
.stats-card {
    transition: transform 0.2s ease-in-out;
}

.stats-card:hover {
    transform: translateY(-2px);
}

/* Empty state */
.empty-state {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 10px;
}
</style>
@endsection
