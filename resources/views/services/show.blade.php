@extends('layouts.app')

@section('title', 'Chi tiết dịch vụ')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-eye mr-2"></i>
                        Chi tiết dịch vụ: {{ $service->name }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('services.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left mr-1"></i>Quay lại
                        </a>
                        @if($service->canBeManageBy(auth()->user()))
                            <a href="{{ route('services.edit', $service) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit mr-1"></i>Chỉnh sửa
                            </a>
                        @endif
                        
                        @if($service->canBeApprovedBy(auth()->user()))
                            <form method="POST" action="{{ route('services.approve', $service) }}" class="d-inline" 
                                  onsubmit="return confirm('Bạn có chắc chắn muốn duyệt dịch vụ này?')">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="fas fa-check mr-1"></i>Duyệt nhanh
                                </button>
                            </form>
                            
                            <button type="button" class="btn btn-danger btn-sm" 
                                    onclick="showRejectModal({{ $service->id }})">
                                <i class="fas fa-times mr-1"></i>Từ chối
                            </button>
                        @endif
                        @if(auth()->user()->role && auth()->user()->role->name === 'seoer' && $service->is_active)
                            <a href="{{ route('services.create-proposal', $service) }}" class="btn btn-success btn-sm">
                                <i class="fas fa-plus mr-1"></i>Tạo đề xuất
                            </a>
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-info-circle mr-2"></i>Thông tin cơ bản
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>ID:</strong></td>
                                            <td>{{ $service->id }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tên dịch vụ:</strong></td>
                                            <td>{{ $service->name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Loại dịch vụ:</strong></td>
                                            <td>
                                                <span class="badge badge-info">{{ $service->getTypeDisplayName() }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Website:</strong></td>
                                            <td>
                                                <a href="{{ $service->website }}" target="_blank" class="text-decoration-none">
                                                    {{ $service->website }}
                                                    <i class="fas fa-external-link-alt fa-xs"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Đối tác:</strong></td>
                                            <td>{{ $service->partner->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Giá dịch vụ:</strong></td>
                                            <td><strong class="text-success">{{ $service->formatted_price }}</strong></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Lĩnh vực:</strong></td>
                                            <td>{{ $service->category ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Trạng thái:</strong></td>
                                            <td>
                                                @if($service->is_active)
                                                    <span class="badge badge-success">Hoạt động</span>
                                                @else
                                                    <span class="badge badge-secondary">Tạm dừng</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>File báo giá:</strong></td>
                                            <td>
                                                @if($service->quote_file)
                                                    <a href="{{ $service->quote_file }}" target="_blank" class="text-decoration-none">
                                                        <i class="fas fa-file-pdf text-danger"></i> Xem file báo giá
                                                    </a>
                                                @else
                                                    <span class="text-muted">Chưa có</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>File demo:</strong></td>
                                            <td>
                                                @if($service->demo_file)
                                                    <a href="{{ $service->demo_file }}" target="_blank" class="text-decoration-none">
                                                        <i class="fas fa-file-image text-info"></i> Xem file demo
                                                    </a>
                                                @else
                                                    <span class="text-muted">Chưa có</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Ref domain:</strong></td>
                                            <td>
                                                @if($service->ref_domain)
                                                    <a href="{{ $service->ref_domain }}" target="_blank" class="text-decoration-none">
                                                        {{ $service->ref_domain }}
                                                        <i class="fas fa-external-link-alt fa-xs"></i>
                                                    </a>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Traffic:</strong></td>
                                            <td>
                                                @if($service->traffic)
                                                    <span class="badge badge-success">{{ $service->traffic }}</span>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Trạng thái duyệt:</strong></td>
                                            <td>
                                                @if($service->approval_status === 'pending')
                                                    <span class="badge badge-warning">
                                                        <i class="fas fa-clock"></i> Chờ duyệt
                                                    </span>
                                                @elseif($service->approval_status === 'approved')
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check"></i> Đã duyệt
                                                    </span>
                                                    @if($service->approvedBy)
                                                        <br><small class="text-success">Duyệt bởi: <strong>{{ $service->approvedBy->name }}</strong></small>
                                                    @endif
                                                @elseif($service->approval_status === 'rejected')
                                                    <span class="badge badge-danger">
                                                        <i class="fas fa-times"></i> Đã từ chối
                                                    </span>
                                                    @if($service->rejection_reason)
                                                        <br><small class="text-danger">{{ $service->rejection_reason }}</small>
                                                    @endif
                                                @else
                                                    <span class="text-muted">Chưa có</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- SEO Metrics -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-chart-line mr-2"></i>Chỉ số SEO
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @if($service->dr)
                                            <div class="col-6 mb-3">
                                                <div class="info-box">
                                                    <span class="info-box-icon bg-info">DR</span>
                                                    <div class="info-box-content">
                                                        <span class="info-box-text">Domain Rating</span>
                                                        <span class="info-box-number">{{ $service->dr }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        @if($service->da)
                                            <div class="col-6 mb-3">
                                                <div class="info-box">
                                                    <span class="info-box-icon bg-success">DA</span>
                                                    <div class="info-box-content">
                                                        <span class="info-box-text">Domain Authority</span>
                                                        <span class="info-box-number">{{ $service->da }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        @if($service->pa)
                                            <div class="col-6 mb-3">
                                                <div class="info-box">
                                                    <span class="info-box-icon bg-warning">PA</span>
                                                    <div class="info-box-content">
                                                        <span class="info-box-text">Page Authority</span>
                                                        <span class="info-box-number">{{ $service->pa }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        @if($service->tf)
                                            <div class="col-6 mb-3">
                                                <div class="info-box">
                                                    <span class="info-box-icon bg-danger">TF</span>
                                                    <div class="info-box-content">
                                                        <span class="info-box-text">Trust Flow</span>
                                                        <span class="info-box-number">{{ $service->tf }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    @if($service->ip)
                                        <div class="mt-3">
                                            <strong>IP Address:</strong> 
                                            <code>{{ $service->ip }}</code>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Keywords -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-tags mr-2"></i>Keywords
                                    </h5>
                                </div>
                                <div class="card-body">
                    @if($service->keywords_string)
                        @php
                            $keywords = is_array($service->keywords) ? $service->keywords : explode(', ', $service->keywords_string);
                        @endphp
                        @foreach($keywords as $keyword)
                            <span class="badge badge-light mr-1 mb-1">{{ trim($keyword) }}</span>
                        @endforeach
                    @else
                        <span class="text-muted">Chưa có keywords</span>
                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    @if($service->description)
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-file-alt mr-2"></i>Mô tả dịch vụ
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-0">{!! nl2br(e($service->description)) !!}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Service Proposals -->
                    @if($service->serviceProposals->count() > 0)
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-clipboard-list mr-2"></i>
                                            Đề xuất từ dịch vụ này ({{ $service->serviceProposals->count() }})
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Seoer</th>
                                                        <th>Số lượng</th>
                                                        <th>Tổng tiền</th>
                                                        <th>Trạng thái</th>
                                                        <th>Ngày tạo</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($service->serviceProposals->take(5) as $proposal)
                                                        <tr>
                                                            <td>
                                                                <a href="{{ route('service-proposals.show', $proposal) }}">
                                                                    #{{ $proposal->id }}
                                                                </a>
                                                            </td>
                                                            <td>{{ $proposal->user->name ?? 'N/A' }}</td>
                                                            <td>{{ $proposal->quantity }}</td>
                                                            <td>{{ number_format($proposal->amount, 0, ',', '.') }} VNĐ</td>
                                                            <td>
                                                                <span class="badge {{ $proposal->getStatusBadgeClass() }}">
                                                                    {{ $proposal->getStatusDisplayName() }}
                                                                </span>
                                                            </td>
                                                            <td>{{ $proposal->created_at->format('d/m/Y') }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        @if($service->serviceProposals->count() > 5)
                                            <div class="text-center">
                                                <small class="text-muted">
                                                    Và {{ $service->serviceProposals->count() - 5 }} đề xuất khác...
                                                </small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Timestamps -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <small class="text-muted">
                                <i class="fas fa-clock mr-1"></i>
                                Tạo lúc: {{ $service->created_at->format('d/m/Y H:i') }}
                                @if($service->updated_at != $service->created_at)
                                    | Cập nhật: {{ $service->updated_at->format('d/m/Y H:i') }}
                                @endif
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Service Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModalLabel">
                    <i class="fas fa-times-circle mr-2"></i>Từ chối dịch vụ
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="" id="rejectForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="rejection_reason">Lý do từ chối:</label>
                        <textarea name="rejection_reason" id="rejection_reason" 
                                  class="form-control" rows="4" 
                                  placeholder="Nhập lý do từ chối dịch vụ..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Hủy
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times mr-1"></i>Từ chối
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.table td {
    vertical-align: middle !important;
    padding: 0.75rem !important;
}

.badge {
    font-size: 0.875em !important;
    padding: 0.375rem 0.75rem !important;
    display: inline-block !important;
    visibility: visible !important;
}

.text-muted {
    color: #6c757d !important;
    visibility: visible !important;
}

.card-body {
    padding: 1.25rem !important;
}

.table-borderless td {
    border: none !important;
    padding: 0.5rem 0 !important;
}
</style>
@endpush

@push('scripts')
<script>
function showRejectModal(serviceId) {
    // Set form action
    document.getElementById('rejectForm').action = '/services/' + serviceId + '/reject';
    
    // Clear previous content
    document.getElementById('rejection_reason').value = '';
    
    // Show modal
    $('#rejectModal').modal('show');
}
</script>
@endpush
