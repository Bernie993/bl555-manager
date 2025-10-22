@extends('layouts.app')

@section('title', 'Quản lý Đề xuất Dịch vụ')

@section('breadcrumb')
    <li class="breadcrumb-item active">Quản lý Đề xuất Dịch vụ</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-clipboard-list me-2"></i>
                Quản lý Đề xuất Dịch vụ
            </h1>
            <div class="d-flex gap-2">
                @can('withdrawals.read')
                <a href="{{ route('withdrawals.index') }}" class="btn btn-success">
                    <i class="fas fa-money-bill-wave me-2"></i>
                    Quản lý Rút tiền
                </a>
                @endcan
                <a href="{{ route('services.index') }}" class="btn btn-primary">
                    <i class="fas fa-cogs me-2"></i>
                    Quản lý Dịch vụ
                </a>
            </div>
        </div>
        
        <!-- Total Amount Display - Will be created by JavaScript -->
        <div id="total-amount-container"></div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('service-proposals.index') }}">
            <div class="row g-3">
                <!-- Service Filter -->
                @php
                    $isPartner = auth()->user()->role && auth()->user()->role->name === 'partner';
                    $serviceColSize = $isPartner ? 'col-md-6' : 'col-md-3';
                @endphp
                <div class="{{ $serviceColSize }}">
                    <label for="service_id" class="form-label">Dịch vụ</label>
                    <select class="form-select" id="service_id" name="service_id">
                        <option value="">Tất cả dịch vụ</option>
                        @if($isPartner)
                            <!-- Partner: Only their services -->
                            @foreach(auth()->user()->services()->orderBy('name')->get() as $service)
                                <option value="{{ $service->id }}" {{ request('service_id') == $service->id ? 'selected' : '' }}>
                                    {{ $service->name }}
                                </option>
                            @endforeach
                        @else
                            <!-- Other roles: All services -->
                            @foreach(\App\Models\Service::with('partner')->orderBy('name')->get() as $service)
                                <option value="{{ $service->id }}" {{ request('service_id') == $service->id ? 'selected' : '' }}>
                                    {{ $service->name }} ({{ $service->partner->name ?? 'N/A' }})
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                
                <!-- Partner Filter -->
                @if(!$isPartner)
                <div class="col-md-3">
                    <label for="partner_id" class="form-label">Đối tác</label>
                    <select class="form-select" id="partner_id" name="partner_id">
                        <option value="">Tất cả đối tác</option>
                        @foreach(\App\Models\User::whereHas('role', function($q) { $q->where('name', 'partner'); })->get() as $partner)
                            <option value="{{ $partner->id }}" {{ request('partner_id') == $partner->id ? 'selected' : '' }}>
                                {{ $partner->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif
                
                <!-- Domain Filter -->
                @if(!$isPartner)
                <div class="col-md-2">
                    <label for="target_domain" class="form-label">Domain</label>
                    @if(auth()->user()->role->name === 'seoer')
                        <!-- Seoer: Dropdown with only their domains -->
                        <select class="form-select" id="target_domain" name="target_domain">
                            <option value="">Tất cả domain</option>
                            @foreach(\App\Models\Website::where('seoer_id', auth()->id())->orderBy('name')->get() as $website)
                                <option value="{{ $website->name }}" {{ request('target_domain') === $website->name ? 'selected' : '' }}>
                                    {{ $website->name }}
                                </option>
                            @endforeach
                        </select>
                    @else
                        <!-- Admin/IT/TL: Dropdown with all domains -->
                        <select class="form-select" id="target_domain" name="target_domain">
                            <option value="">Tất cả domain</option>
                            @foreach(\App\Models\Website::orderBy('name')->get() as $website)
                                <option value="{{ $website->name }}" {{ request('target_domain') === $website->name ? 'selected' : '' }}>
                                    {{ $website->name }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>
                @endif
                
                <!-- Date Range Filter -->
                <div class="{{ $isPartner ? 'col-md-3' : 'col-md-2' }}">
                    <label for="date_range" class="form-label">Thời gian</label>
                    <input type="text" name="date_range" id="date_range" class="form-control" 
                           placeholder="Chọn khoảng thời gian" 
                           value="{{ request('date_range') }}" readonly>
                    <input type="hidden" name="start_date" id="start_date" value="{{ request('start_date') }}">
                    <input type="hidden" name="end_date" id="end_date" value="{{ request('end_date') }}">
                </div>
                
                <!-- Status Filter -->
                <div class="{{ $isPartner ? 'col-md-3' : 'col-md-2' }}">
                    <label for="status" class="form-label">Trạng thái</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Tất cả trạng thái</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Từ chối</option>
                        <option value="partner_confirmed" {{ request('status') === 'partner_confirmed' ? 'selected' : '' }}>Đối tác xác nhận</option>
                        <option value="partner_completed" {{ request('status') === 'partner_completed' ? 'selected' : '' }}>Đối tác hoàn thành</option>
                        <option value="seoer_confirmed" {{ request('status') === 'seoer_confirmed' ? 'selected' : '' }}>Seoer xác nhận</option>
                        <option value="admin_completed" {{ request('status') === 'admin_completed' ? 'selected' : '' }}>Quản lý hoàn thành</option>
                        <option value="payment_confirmed" {{ request('status') === 'payment_confirmed' ? 'selected' : '' }}>Trợ lý xác nhận hoàn thành</option>
                    </select>
                </div>
            </div>
            
            <!-- Search and Filter Button Row -->
            <div class="row g-3 mt-2">
                <div class="col-md-6">
                    <label for="search" class="form-label">Tìm kiếm</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ request('search') }}" placeholder="Tên dịch vụ hoặc nhà cung cấp...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-search me-2"></i>
                            Lọc
                        </button>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <a href="{{ route('service-proposals.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>
                            Xóa bộ lọc
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Service Proposals Table -->
<div class="card">
    <div class="card-body">
        @if($proposals->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Dịch vụ</th>
                            <th>Nhà cung cấp</th>
                            <th>Số tiền</th>
                            <th>Trạng thái</th>
                            <th>Người tạo</th>
                            <th>Ngày tạo</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($proposals as $proposal)
                        <tr>
                            <td>
                                <strong>{{ $proposal->service_name }}</strong>
                                @if($proposal->target_domain)
                                    <br><small class="text-info">
                                        <i class="fas fa-globe me-1"></i>{{ $proposal->target_domain }}
                                    </small>
                                @endif
                                <br><small class="text-muted">SL: {{ $proposal->quantity }}</small>
                            </td>
                            <td>
                                {{ $proposal->supplier_name }}
                                @if($proposal->proposal_link)
                                    <br><a href="{{ $proposal->proposal_link }}" target="_blank" class="text-primary">
                                        <i class="fas fa-external-link-alt me-1"></i>Link đề xuất
                                    </a>
                                @endif
                                @if($proposal->result_link)
                                    <br><a href="{{ $proposal->result_link }}" target="_blank" class="text-success">
                                        <i class="fas fa-file-download me-1"></i>Link kết quả
                                    </a>
                                @endif
                            </td>
                            <td>
                                <strong class="text-primary">{{ $proposal->formatted_amount }}</strong>
                            </td>
                            <td>
                                <span class="badge {{ $proposal->getStatusBadgeClass() }}">
                                    {{ $proposal->getStatusDisplayName() }}
                                </span>
                                @php
                                    $availableActions = $proposal->getAvailableActionsFor(auth()->user());
                                @endphp
                                @if(count($availableActions) > 0)
                                    <br><small class="text-info">Có thể thao tác</small>
                                @endif
                            </td>
                            <td>
                                {{ $proposal->creator->name }}
                                @if($proposal->creator->id === auth()->id())
                                    <span class="badge bg-info ms-1">Bạn</span>
                                @endif
                            </td>
                            <td>{{ $proposal->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('service-proposals.show', $proposal) }}" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    @php
                                        $userRole = auth()->user()->role->name ?? '';
                                        $canEdit = in_array($userRole, ['admin', 'it']) || 
                                                  ($proposal->created_by === auth()->id() && $proposal->status === 'pending');
                                    @endphp
                                    @if($canEdit)
                                    <a href="{{ route('service-proposals.edit', $proposal) }}" class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endif
                                    
                                    <!-- Dynamic Actions Based on Role and Status -->
                                    @php
                                        $availableActions = $proposal->getAvailableActionsFor(auth()->user());
                                    @endphp
                                    
                                    @foreach($availableActions as $action)
                                        @php
                                            $routeName = match($action['action']) {
                                                'approve' => 'service-proposals.approve',
                                                'reject' => 'service-proposals.reject',
                                                'partner_confirm' => 'service-proposals.partner-confirm',
                                                'partner_complete' => 'service-proposals.partner-complete',
                                                'seoer_confirm' => 'service-proposals.seoer-confirm',
                                                'admin_complete' => 'service-proposals.admin-complete',
                                                'payment_confirm' => 'service-proposals.payment-confirm',
                                                default => null
                                            };
                                            
                                            $icon = match($action['action']) {
                                                'approve' => 'fas fa-check',
                                                'reject' => 'fas fa-times',
                                                'partner_confirm' => 'fas fa-handshake',
                                                'partner_complete' => 'fas fa-check-circle',
                                                'seoer_confirm' => 'fas fa-user-check',
                                                'admin_complete' => 'fas fa-check-double',
                                                'payment_confirm' => 'fas fa-money-check-alt',
                                                default => 'fas fa-cog'
                                            };
                                        @endphp
                                        
                                        @if($routeName)
                                            @if($action['action'] === 'partner_complete')
                                                <!-- Special handling for partner complete - show modal -->
                                                <button type="button" class="btn btn-sm {{ $action['class'] }}" 
                                                        title="{{ $action['label'] }}"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#partnerCompleteModal{{ $proposal->id }}">
                                                    <i class="{{ $icon }}"></i>
                                                </button>
                                            @else
                                                <!-- Regular form submission for other actions -->
                                                <form action="{{ route($routeName, $proposal) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm {{ $action['class'] }}" title="{{ $action['label'] }}">
                                                        <i class="{{ $icon }}"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @endif
                                    @endforeach
                                    
                                    @php
                                        $allowedDeleteStatuses = ['pending', 'approved'];
                                        $statusAllowsDelete = in_array($proposal->status, $allowedDeleteStatuses);
                                        
                                        $canDelete = $statusAllowsDelete && (
                                            in_array($userRole, ['admin', 'it']) || 
                                            ($proposal->created_by === auth()->id() && $proposal->status === 'pending')
                                        );
                                    @endphp
                                    @if($canDelete)
                                    <form action="{{ route('service-proposals.destroy', $proposal) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger btn-delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $proposals->withQueryString()->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Không có đề xuất dịch vụ nào</h5>
                <p class="text-muted">
                    @if(request()->hasAny(['search', 'status']))
                        Không tìm thấy đề xuất phù hợp với bộ lọc.
                    @else
                        Hãy tạo đề xuất dịch vụ đầu tiên của bạn.
                    @endif
                </p>
                @if(auth()->user()->hasPermission('service_proposals.create'))
                <a href="{{ route('service-proposals.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    Tạo Đề xuất
                </a>
                @endif
            </div>
        @endif
    </div>
</div>

<!-- Status Legend -->
<div class="card mt-4">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="fas fa-info-circle me-2"></i>
            Quy trình Đề xuất Dịch vụ
        </h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex flex-wrap gap-3 align-items-center">
                    <div class="d-flex align-items-center">
                        <span class="badge bg-warning me-2">Chờ duyệt</span>
                        <i class="fas fa-arrow-right text-muted me-2"></i>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-info me-2">Đã duyệt</span>
                        <i class="fas fa-arrow-right text-muted me-2"></i>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-primary me-2">Đối tác xác nhận</span>
                        <i class="fas fa-arrow-right text-muted me-2"></i>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-dark me-2">Đối tác hoàn thành</span>
                        <i class="fas fa-arrow-right text-muted me-2"></i>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-secondary me-2">Seoer xác nhận</span>
                        <i class="fas fa-arrow-right text-muted me-2"></i>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-success me-2">Quản lý hoàn thành</span>
                        <i class="fas fa-arrow-right text-muted me-2"></i>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-success">Đã thanh toán</span>
                    </div>
                </div>
                <div class="mt-3">
                    <small class="text-muted">
                        <strong>Quy trình mới:</strong> Admin/IT duyệt → Partner xác nhận → Partner hoàn thành → <strong>Seoer xác nhận</strong> → Admin/IT xác nhận hoàn thành → Trợ lý xác nhận hoàn thành.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Partner Complete Modals -->
@foreach($proposals as $proposal)
    @if($proposal->canBePartnerCompletedBy(auth()->user()))
    <div class="modal fade" id="partnerCompleteModal{{ $proposal->id }}" tabindex="-1" aria-labelledby="partnerCompleteModalLabel{{ $proposal->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="partnerCompleteModalLabel{{ $proposal->id }}">
                        <i class="fas fa-check-circle me-2"></i>
                        Xác nhận hoàn thành dịch vụ
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('service-proposals.partner-complete', $proposal) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="modal-body">
                        <div class="mb-3">
                            <h6 class="fw-bold">{{ $proposal->service_name }}</h6>
                            <p class="text-muted mb-3">Nhà cung cấp: {{ $proposal->supplier_name }}</p>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Vui lòng cung cấp link file kết quả đã hoàn thành để Seoer có thể kiểm tra và xác nhận.
                        </div>
                        
                        <div class="mb-3">
                            <label for="result_link{{ $proposal->id }}" class="form-label">
                                <i class="fas fa-link me-1"></i>
                                Link file kết quả <span class="text-danger">*</span>
                            </label>
                            <input type="url" 
                                   class="form-control" 
                                   id="result_link{{ $proposal->id }}" 
                                   name="result_link" 
                                   placeholder="https://drive.google.com/... hoặc https://dropbox.com/..."
                                   required>
                            <div class="form-text">
                                Nhập link Google Drive, Dropbox, hoặc link khác chứa file kết quả đã hoàn thành
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>
                            Hủy
                        </button>
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-check-circle me-1"></i>
                            Xác nhận hoàn thành
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
@endforeach

@endsection

@push('styles')
<!-- Date Range Picker CSS -->
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.css" />
<style>
.daterangepicker {
    z-index: 9999 !important;
}
.daterangepicker .ranges li {
    color: #333;
    background-color: #f5f5f5;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin-bottom: 8px;
    cursor: pointer;
    padding: 8px 12px;
}
.daterangepicker .ranges li:hover {
    background-color: #e6e6e6;
    border-color: #adadad;
}
.daterangepicker .ranges li.active {
    background-color: #667eea;
    border-color: #667eea;
    color: white;
}
.daterangepicker .calendar-table {
    border: 1px solid #ddd;
    border-radius: 4px;
}
.daterangepicker td.active, .daterangepicker td.active:hover {
    background-color: #667eea;
    border-color: #667eea;
    color: #fff;
}

/* Force total amount display to always be visible */
#total-amount-top {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    position: relative !important;
    z-index: 9999 !important;
    background-color: #d4edda !important;
    border: 1px solid #c3e6cb !important;
    border-radius: 0.375rem !important;
    padding: 1rem 1.5rem !important;
    margin-bottom: 1.5rem !important;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
}
</style>
@endpush

@push('scripts')
<!-- Date Range Picker JS -->
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.min.js"></script>

<script>
$(document).ready(function() {
    console.log('Initializing Service Proposals filters...');
    
    // Initialize date range picker
    setTimeout(function() {
        try {
            $('#date_range').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Hủy',
                    applyLabel: 'Áp dụng',
                    format: 'DD/MM/YYYY',
                    separator: ' - ',
                    customRangeLabel: 'Tùy chọn',
                    daysOfWeek: ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'],
                    monthNames: [
                        'Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6',
                        'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'
                    ],
                    firstDay: 1
                },
                ranges: {
                   'Hôm nay': [moment(), moment()],
                   'Hôm qua': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                   '7 ngày qua': [moment().subtract(6, 'days'), moment()],
                   '30 ngày qua': [moment().subtract(29, 'days'), moment()],
                   'Tháng này': [moment().startOf('month'), moment().endOf('month')],
                   'Tháng trước': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                opens: 'left',
                drops: 'down',
                buttonClasses: 'btn btn-sm',
                applyClass: 'btn-primary',
                cancelClass: 'btn-secondary'
            });
            
            // Handle apply event
            $('#date_range').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
                $('#start_date').val(picker.startDate.format('YYYY-MM-DD'));
                $('#end_date').val(picker.endDate.format('YYYY-MM-DD'));
            });

            $('#date_range').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                $('#start_date').val('');
                $('#end_date').val('');
            });
            
            // Set initial value if dates are provided
            @if(request('start_date') && request('end_date'))
                $('#date_range').val('{{ \Carbon\Carbon::parse(request("start_date"))->format("d/m/Y") }}' + ' - ' + '{{ \Carbon\Carbon::parse(request("end_date"))->format("d/m/Y") }}');
            @endif
            
        } catch (error) {
            console.error('Error initializing date range picker:', error);
        }
    }, 500);

    // Auto-submit form when filters change
    $('#service_id, #partner_id, #status, #target_domain').change(function() {
        $(this).closest('form').submit();
    });
    
    // Create total amount display with JavaScript
    function createTotalAmountDisplay() {
        const container = document.getElementById('total-amount-container');
        if (!container) return;
        
        // Remove existing display
        container.innerHTML = '';
        
        // Get data from PHP
        const totalAmount = {{ $totalAmount }};
        const targetDomain = '{{ request('target_domain') }}';
        
        // Create the display element
        const displayHtml = `
            <div class="alert alert-success mb-4" id="total-amount-display" style="display: block !important; visibility: visible !important; opacity: 1 !important; position: relative !important; z-index: 9999 !important; background-color: #d4edda !important; border: 1px solid #c3e6cb !important; border-radius: 0.375rem !important; padding: 1rem 1.5rem !important; margin-bottom: 1.5rem !important; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-calculator me-2"></i>
                        <strong>${targetDomain ? `Tổng số tiền cho domain "${targetDomain}":` : 'Tổng số tiền tất cả đề xuất:'}</strong>
                    </div>
                    <div>
                        <span class="h4 text-success mb-0">${totalAmount.toLocaleString('vi-VN')} VNĐ</span>
                    </div>
                </div>
            </div>
        `;
        
        container.innerHTML = displayHtml;
        console.log('Total amount display created by JavaScript');
    }
    
    // Create display when page loads
    $(document).ready(function() {
        createTotalAmountDisplay();
    });
    
    // Recreate display every 200ms to ensure it stays visible
    setInterval(function() {
        createTotalAmountDisplay();
    }, 200);
    
    // Recreate display after any form submission
    $(document).on('submit', 'form', function() {
        setTimeout(createTotalAmountDisplay, 100);
    });
});
</script>
@endpush

