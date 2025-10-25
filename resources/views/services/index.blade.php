@extends('layouts.app')

@section('title', 'Quản lý Dịch vụ')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">
                            <i class="fas fa-cogs mr-2"></i>
                            Quản lý Dịch vụ
                        </h3>
                        @if(auth()->user()->role && auth()->user()->role->name === 'partner')
                            <a href="{{ route('services.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus mr-2"></i>Thêm dịch vụ mới
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Filters -->
                <div class="card-body">
                    <form method="GET" action="{{ route('services.index') }}" class="mb-4">
                        <div class="filter-section">
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="type">Loại dịch vụ:</label>
                                        <select name="type" id="type" class="form-control form-control-sm">
                                            <option value="">Tất cả</option>
                                            @foreach(\App\Models\Service::TYPES as $key => $value)
                                                <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>
                                                    {{ $value }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                @if(!empty($partners))
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="partner_name">Đối tác:</label>
                                        <select name="partner_name" id="partner_name" class="form-control form-control-sm">
                                            <option value="">Tất cả</option>
                                            @foreach($partners as $partner)
                                                <option value="{{ $partner->id }}" {{ request('partner_name') == $partner->id ? 'selected' : '' }}>
                                                    {{ $partner->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                @endif

                                @if(auth()->user()->role && in_array(auth()->user()->role->name, ['admin', 'it', 'assistant']))
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="approval_status">Trạng thái duyệt:</label>
                                        <select name="approval_status" id="approval_status" class="form-control form-control-sm">
                                            <option value="">Tất cả</option>
                                            @foreach(\App\Models\Service::APPROVAL_STATUSES as $key => $value)
                                                <option value="{{ $key }}" {{ request('approval_status') == $key ? 'selected' : '' }}>
                                                    {{ $value }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                @endif

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="price_from">Giá từ:</label>
                                        <input type="number" name="price_from" id="price_from" class="form-control form-control-sm" 
                                               placeholder="0" min="0" step="1000"
                                               value="{{ request('price_from') }}">
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="price_to">Giá đến:</label>
                                        <input type="number" name="price_to" id="price_to" class="form-control form-control-sm" 
                                               placeholder="1000000" min="0" step="1000"
                                               value="{{ request('price_to') }}">
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="dr_from">DR từ:</label>
                                        <input type="number" name="dr_from" id="dr_from" class="form-control form-control-sm" 
                                               placeholder="0" min="0" max="100" step="1"
                                               value="{{ request('dr_from') }}">
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="dr_to">DR đến:</label>
                                        <input type="number" name="dr_to" id="dr_to" class="form-control form-control-sm" 
                                               placeholder="100" min="0" max="100" step="1"
                                               value="{{ request('dr_to') }}">
                                    </div>
                                </div>
                            </div>

                            <!-- Second Row -->
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="ref_domain_search">Ref domain:</label>
                                        <input type="text" name="ref_domain_search" id="ref_domain_search" class="form-control form-control-sm" 
                                               placeholder="Tìm theo domain..."
                                               value="{{ request('ref_domain_search') }}">
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="traffic_from">Traffic từ:</label>
                                        <input type="number" name="traffic_from" id="traffic_from" class="form-control form-control-sm" 
                                               placeholder="0" min="0" step="1000"
                                               value="{{ request('traffic_from') }}">
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="traffic_to">Traffic đến:</label>
                                        <input type="number" name="traffic_to" id="traffic_to" class="form-control form-control-sm" 
                                               placeholder="1000000" min="0" step="1000"
                                               value="{{ request('traffic_to') }}">
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="sort_by">Sắp xếp:</label>
                                        <select name="sort_by" id="sort_by" class="form-control form-control-sm">
                                            <option value="created_at_desc" {{ request('sort_by') == 'created_at_desc' ? 'selected' : '' }}>
                                                Mới nhất
                                            </option>
                                            <option value="price_asc" {{ request('sort_by') == 'price_asc' ? 'selected' : '' }}>
                                                Giá thấp → cao
                                            </option>
                                            <option value="price_desc" {{ request('sort_by') == 'price_desc' ? 'selected' : '' }}>
                                                Giá cao → thấp
                                            </option>
                                            <option value="name_asc" {{ request('sort_by') == 'name_asc' ? 'selected' : '' }}>
                                                Tên A → Z
                                            </option>
                                            <option value="name_desc" {{ request('sort_by') == 'name_desc' ? 'selected' : '' }}>
                                                Tên Z → A
                                            </option>
                                            <option value="dr_desc" {{ request('sort_by') == 'dr_desc' ? 'selected' : '' }}>
                                                DR cao → thấp
                                            </option>
                                            <option value="dr_asc" {{ request('sort_by') == 'dr_asc' ? 'selected' : '' }}>
                                                DR thấp → cao
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Row -->
                            <div class="row mt-2">
                                <div class="col-md-10">
                                    <div class="form-group mb-0">
                                        <input type="text" name="search" id="search" class="form-control form-control-sm" 
                                               placeholder="🔍 Tìm kiếm theo tên dịch vụ, website, lĩnh vực..." 
                                               value="{{ request('search') }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-info btn-sm mr-2">
                                            <i class="fas fa-search"></i> Lọc
                                        </button>
                                        <a href="{{ route('services.index') }}" class="btn btn-secondary btn-sm">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Info Row -->
                            <div class="row mt-2">
                                <div class="col-md-12">
                                    <div class="text-right">
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle"></i> 
                                            Tìm thấy {{ $services->total() }} dịch vụ
                                        </small>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </form>

                    @if(auth()->user()->role && auth()->user()->role->name === 'seoer')
                        <!-- Bulk Actions for SEOer -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <button type="button" id="selectAllBtn" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-check-square"></i> Chọn tất cả
                                    </button>
                                    <button type="button" id="deselectAllBtn" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-square"></i> Bỏ chọn tất cả
                                    </button>
                                </div>
                                <div>
                                    <button type="button" id="bulkCreateProposalBtn" class="btn btn-success" disabled>
                                        <i class="fas fa-plus"></i> Tạo đề xuất (<span id="selectedCount">0</span>)
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Services Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    @if(auth()->user()->role && auth()->user()->role->name === 'seoer')
                                        <th width="50">
                                            <input type="checkbox" id="selectAllCheckbox" class="form-check-input">
                                        </th>
                                    @endif
                                    <th>ID</th>
                                    <th>Tên dịch vụ</th>
                                    <th>Loại</th>
                                    <th>File báo giá</th>
                                    <th>File demo</th>
                                    @if(auth()->user()->role && auth()->user()->role->name !== 'partner')
                                        <th>Đối tác</th>
                                    @endif
                                    <th>DR</th>
                                    <th>Ref domain</th>
                                    <th>Traffic</th>
                                    <th>Lĩnh vực</th>
                                    <th>Giá</th>
                                    <th>Trạng Thái</th>
                                    @if(auth()->user()->role && in_array(auth()->user()->role->name, ['admin', 'it', 'assistant', 'partner']))
                                        <th>Duyệt</th>
                                    @endif
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($services as $service)
                                    <tr data-service-id="{{ $service->id }}">
                                        @if(auth()->user()->role && auth()->user()->role->name === 'seoer')
                                            <td>
                                                @if($service->is_active)
                                                    <input type="checkbox" 
                                                           class="form-check-input service-checkbox" 
                                                           value="{{ $service->id }}"
                                                           data-service-name="{{ $service->name }}"
                                                           data-partner-id="{{ $service->partner_id }}"
                                                           data-partner-name="{{ $service->partner->name ?? 'N/A' }}"
                                                           data-website="{{ $service->website }}"
                                                           data-price="{{ $service->price }}"
                                                           data-keywords="{{ $service->keywords_string }}"
                                                           data-supplier-name="{{ $service->partner->name ?? '' }}"
                                                           data-supplier-telegram="{{ $service->partner->telegram ?? '' }}">
                                                @endif
                                            </td>
                                        @endif
                                        <td>{{ $service->id }}</td>
                                        <td>
                                            <strong>{{ $service->name }}</strong>
                                            @if($service->keywords_string)
                                                <br><small class="text-muted">{{ Str::limit($service->keywords_string, 50) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($service->type === 'entity')
                                                <span class="badge badge-primary">{{ $service->getTypeDisplayName() }}</span>
                                            @elseif($service->type === 'backlink')
                                                <span class="badge badge-info">{{ $service->getTypeDisplayName() }}</span>
                                            @elseif($service->type === 'textlink')
                                                <span class="badge badge-warning">{{ $service->getTypeDisplayName() }}</span>
                                            @elseif($service->type === 'guest_post')
                                                <span class="badge badge-success">{{ $service->getTypeDisplayName() }}</span>
                                            @else
                                                <span class="badge badge-secondary">{{ $service->getTypeDisplayName() }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($service->quote_file)
                                                <a href="{{ $service->quote_file }}" target="_blank" class="text-decoration-none">
                                                    <i class="fas fa-file-pdf text-danger"></i> Xem file
                                                </a>
                                            @else
                                                <span class="text-muted">Chưa có</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($service->demo_file)
                                                <a href="{{ $service->demo_file }}" target="_blank" class="text-decoration-none">
                                                    <i class="fas fa-file-image text-info"></i> Xem demo
                                                </a>
                                            @else
                                                <span class="text-muted">Chưa có</span>
                                            @endif
                                        </td>
                                        @if(auth()->user()->role && auth()->user()->role->name !== 'partner')
                                            <td>{{ $service->partner->name ?? 'N/A' }}</td>
                                        @endif
                                        <td>
                                            @if($service->dr)
                                                <span class="badge badge-info">{{ $service->dr }}</span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($service->ref_domain)
                                                <a href="{{ $service->ref_domain }}" target="_blank" class="text-decoration-none">
                                                    {{ Str::limit($service->ref_domain, 20) }}
                                                    <i class="fas fa-external-link-alt fa-xs"></i>
                                                </a>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($service->traffic)
                                                <span class="badge badge-success">{{ $service->traffic }}</span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>{{ $service->category ?? 'N/A' }}</td>
                                        <td><strong>{{ $service->formatted_price }}</strong></td>
                                        <td class="service-status-cell">
                                            @if($service->is_active)
                                                <span class="badge badge-success text-white">
                                                    <i class="fas fa-check-circle"></i> Hoạt động
                                                </span>
                                            @else
                                                <span class="badge badge-dark text-white">
                                                    <i class="fas fa-pause-circle"></i> Tạm dừng
                                                </span>
                                            @endif
                                        </td>
                                        @if(auth()->user()->role && in_array(auth()->user()->role->name, ['admin', 'it', 'assistant', 'partner']))
                                        <td class="approval-status-cell">
                                            @if($service->approval_status === 'pending')
                                                <span class="badge badge-warning text-dark">
                                                    <i class="fas fa-clock"></i> {{ $service->getApprovalStatusDisplayName() }}
                                                </span>
                                            @elseif($service->approval_status === 'approved')
                                                <span class="badge badge-success text-white">
                                                    <i class="fas fa-check"></i> {{ $service->getApprovalStatusDisplayName() }}
                                                </span>
                                                @if($service->approvedBy)
                                                    <br><small class="text-success"><strong>{{ $service->approvedBy->name }}</strong></small>
                                                @endif
                                            @elseif($service->approval_status === 'rejected')
                                                <span class="badge badge-danger text-white">
                                                    <i class="fas fa-times"></i> {{ $service->getApprovalStatusDisplayName() }}
                                                </span>
                                                @if($service->rejection_reason)
                                                    <br><small class="text-danger" title="{{ $service->rejection_reason }}">{{ Str::limit($service->rejection_reason, 30) }}</small>
                                                @endif
                                            @endif
                                        </td>
                                        @endif
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('services.show', $service) }}" 
                                                   class="btn btn-sm btn-info" title="Xem chi tiết">
                                                    <i class="fas fa-eye"></i>
                                                </a>

                                                @if($service->canBeManageBy(auth()->user()))
                                                    <a href="{{ route('services.edit', $service) }}" 
                                                       class="btn btn-sm btn-warning" title="Chỉnh sửa">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    
                                                    <form method="POST" action="{{ route('services.destroy', $service) }}" 
                                                          style="display: inline;" 
                                                          onsubmit="return confirm('Bạn có chắc chắn muốn xóa dịch vụ này?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Xóa">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif

                                                @if($service->canBeApprovedBy(auth()->user()))
                                                    <button type="button" class="btn btn-sm btn-success approve-service-btn" 
                                                            title="Duyệt" data-service-id="{{ $service->id }}">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    
                                                    <button type="button" class="btn btn-sm btn-danger" title="Từ chối"
                                                            onclick="showRejectModal({{ $service->id }})">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                @endif

                                                @if(auth()->user()->role && auth()->user()->role->name === 'seoer' && $service->is_active && $service->isApproved())
                                                    <a href="{{ route('services.create-proposal', $service) }}" 
                                                       class="btn btn-sm btn-success" title="Tạo đề xuất">
                                                        <i class="fas fa-plus"></i> Đề xuất
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ auth()->user()->role && auth()->user()->role->name === 'partner' ? '12' : (auth()->user()->role && auth()->user()->role->name === 'seoer' ? '13' : '13') }}" class="text-center">
                                            <div class="py-4">
                                                <i class="fas fa-cogs fa-3x text-muted mb-3"></i>
                                                <h5 class="text-muted">Chưa có dịch vụ nào</h5>
                                                @if(auth()->user()->role && auth()->user()->role->name === 'partner')
                                                    <p class="text-muted">Bạn chưa tạo dịch vụ nào.</p>
                                                    <a href="{{ route('services.create') }}" class="btn btn-primary">
                                                        <i class="fas fa-plus mr-2"></i>Tạo dịch vụ đầu tiên
                                                    </a>
                                                @else
                                                    <p class="text-muted">Không có dịch vụ nào phù hợp với bộ lọc.</p>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($services->hasPages())
                        <div class="pagination-container">
                            <div class="pagination-info">
                                Hiển thị {{ $services->firstItem() }} - {{ $services->lastItem() }} 
                                trong tổng số {{ $services->total() }} dịch vụ
                            </div>
                            {{ $services->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<style>
/* Custom badge styles for better visibility */
.badge {
    font-size: 0.85em;
    padding: 0.4em 0.8em;
    border-radius: 0.375rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.3em;
}

.badge-primary {
    background-color: #007bff !important;
    color: white !important;
}

.badge-info {
    background-color: #17a2b8 !important;
    color: white !important;
}

.badge-warning {
    background-color: #ffc107 !important;
    color: #212529 !important;
}

.badge-success {
    background-color: #28a745 !important;
    color: white !important;
}

.badge-danger {
    background-color: #dc3545 !important;
    color: white !important;
}

.badge-dark {
    background-color: #343a40 !important;
    color: white !important;
}

.badge-secondary {
    background-color: #6c757d !important;
    color: white !important;
}

/* Service table improvements */
.table td {
    vertical-align: middle;
}

.service-status-cell {
    min-width: 120px;
}

.approval-status-cell {
    min-width: 150px;
}

/* Filter improvements */
.form-group label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.btn-info {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    border: none;
    border-radius: 0.375rem;
}

.btn-secondary {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    border: none;
    border-radius: 0.375rem;
}

.filter-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 1rem 1.5rem;
    border-radius: 0.75rem;
    margin-bottom: 1rem;
    border: 1px solid #dee2e6;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.form-control-sm {
    height: calc(1.8125rem + 2px);
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    border-radius: 0.375rem;
    border: 1px solid #ced4da;
    transition: all 0.15s ease-in-out;
}

.form-control-sm:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
}

.btn-sm {
    padding: 0.25rem 0.75rem;
    font-size: 0.875rem;
    border-radius: 0.375rem;
    font-weight: 500;
}

.form-group label {
    font-size: 0.8rem;
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.3rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.filter-section .row {
    align-items: end;
}

.service-count {
    font-size: 0.75rem;
    color: #6c757d;
    font-weight: 500;
}
</style>
<script>
console.log('Script is loading...');

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded');
    
    // Format price inputs
    const priceInputs = document.querySelectorAll('#price_from, #price_to, #traffic_from, #traffic_to');
    priceInputs.forEach(input => {
        input.addEventListener('input', function() {
            // Remove non-numeric characters
            this.value = this.value.replace(/[^0-9]/g, '');
        });
        
        input.addEventListener('blur', function() {
            if (this.value) {
                // Format number with thousands separator for display
                const value = parseInt(this.value);
                if (!isNaN(value) && value > 0) {
                    this.title = new Intl.NumberFormat('vi-VN').format(value) + ' VNĐ';
                }
            }
        });
    });

    // Format DR inputs
    const drInputs = document.querySelectorAll('#dr_from, #dr_to');
    drInputs.forEach(input => {
        input.addEventListener('input', function() {
            // Remove non-numeric characters and limit to 0-100
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value > 100) {
                this.value = 100;
            }
        });
    });

    // Auto submit form when filters change
    const autoSubmitElements = document.querySelectorAll('#type, #partner_name, #approval_status, #sort_by');
    autoSubmitElements.forEach(element => {
        element.addEventListener('change', function() {
            // Add a small delay to improve UX
            setTimeout(() => {
                this.form.submit();
            }, 100);
        });
    });

    // Auto submit form when numeric filters change (with debounce)
    const numericFilters = document.querySelectorAll('#price_from, #price_to, #dr_from, #dr_to, #traffic_from, #traffic_to');
    let debounceTimer;
    numericFilters.forEach(element => {
        element.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                this.form.submit();
            }, 1000); // Wait 1 second after user stops typing
        });
    });

    // Auto submit form when ref domain search changes (with debounce)
    const refDomainSearch = document.querySelector('#ref_domain_search');
    if (refDomainSearch) {
        refDomainSearch.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                this.form.submit();
            }, 1000); // Wait 1 second after user stops typing
        });
    }

    // Enhanced search with Enter key
    const searchInput = document.querySelector('#search');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.form.submit();
            }
        });
    }
    
    @if(auth()->user()->role && auth()->user()->role->name === 'seoer')
    console.log('SEOer detected, initializing bulk functionality');
    
    // Simple function to update count and button
    function updateBulkActions() {
        var checkedBoxes = document.querySelectorAll('.service-checkbox:checked');
        var count = checkedBoxes.length;
        
        console.log('Checked boxes count:', count);
        
        // Update count display
        var countSpan = document.getElementById('selectedCount');
        if (countSpan) {
            countSpan.textContent = count;
        }
        
        // Update button state
        var bulkBtn = document.getElementById('bulkCreateProposalBtn');
        if (bulkBtn) {
            bulkBtn.disabled = (count === 0);
        }
    }
    
    // Listen for checkbox changes
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('service-checkbox')) {
            console.log('Checkbox changed');
            updateBulkActions();
        }
    });
    
    // Select all button
    var selectAllBtn = document.getElementById('selectAllBtn');
    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Select all clicked');
            var checkboxes = document.querySelectorAll('.service-checkbox');
            checkboxes.forEach(function(cb) {
                cb.checked = true;
            });
            updateBulkActions();
        });
    }
    
    // Deselect all button
    var deselectAllBtn = document.getElementById('deselectAllBtn');
    if (deselectAllBtn) {
        deselectAllBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Deselect all clicked');
            var checkboxes = document.querySelectorAll('.service-checkbox');
            checkboxes.forEach(function(cb) {
                cb.checked = false;
            });
            updateBulkActions();
        });
    }
    
    // Bulk create proposal button
    var bulkBtn = document.getElementById('bulkCreateProposalBtn');
    if (bulkBtn) {
        bulkBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Bulk create clicked');
            
            var checkedBoxes = document.querySelectorAll('.service-checkbox:checked');
            if (checkedBoxes.length === 0) {
                alert('Vui lòng chọn ít nhất một dịch vụ');
                return;
            }
            
            // Collect service data
            var services = [];
            checkedBoxes.forEach(function(cb) {
                console.log('Processing checkbox:', cb.value);
                console.log('Partner ID:', cb.getAttribute('data-partner-id'));
                console.log('Partner Name:', cb.getAttribute('data-partner-name'));
                
                services.push({
                    id: cb.value,
                    name: cb.getAttribute('data-service-name'),
                    partner_id: cb.getAttribute('data-partner-id'),
                    partner_name: cb.getAttribute('data-partner-name'),
                    website: cb.getAttribute('data-website'),
                    price: cb.getAttribute('data-price'),
                    keywords: cb.getAttribute('data-keywords') || '',
                    supplier_name: cb.getAttribute('data-supplier-name') || '',
                    supplier_telegram: cb.getAttribute('data-supplier-telegram') || ''
                });
            });
            
            console.log('Services to submit:', services);
            console.log('Services JSON:', JSON.stringify(services));
            
            // Create and submit form
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("services.bulk-create-proposals") }}';
            
            var csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);
            
            var servicesInput = document.createElement('input');
            servicesInput.type = 'hidden';
            servicesInput.name = 'services';
            servicesInput.value = JSON.stringify(services);
            form.appendChild(servicesInput);
            
            document.body.appendChild(form);
            form.submit();
        });
    }
    
    // Initial update
    updateBulkActions();
    @endif

    // AJAX approve service functionality
    document.addEventListener('click', function(e) {
        if (e.target.closest('.approve-service-btn')) {
            e.preventDefault();
            const button = e.target.closest('.approve-service-btn');
            const serviceId = button.getAttribute('data-service-id');
            
            if (confirm('Bạn có chắc chắn muốn duyệt dịch vụ này?')) {
                approveService(serviceId, button);
            }
        }
    });

    function approveService(serviceId, button) {
        // Show loading state
        const originalContent = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        button.disabled = true;

        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        fetch(`/services/${serviceId}/approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the approval status in the table
                updateApprovalStatus(serviceId, data.service);
                
                // Show success message
                showNotification('success', data.message);
                
                // Hide the approve button and show approved status
                button.style.display = 'none';
            } else {
                showNotification('error', data.message);
                // Restore button
                button.innerHTML = originalContent;
                button.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('error', 'Có lỗi xảy ra khi duyệt dịch vụ');
            // Restore button
            button.innerHTML = originalContent;
            button.disabled = false;
        });
    }

    function updateApprovalStatus(serviceId, serviceData) {
        // Find the row containing this service
        const serviceRow = document.querySelector(`tr[data-service-id="${serviceId}"]`);
        if (!serviceRow) {
            // If not found by data attribute, find by looking for the approve button
            const approveBtn = document.querySelector(`.approve-service-btn[data-service-id="${serviceId}"]`);
            if (approveBtn) {
                serviceRow = approveBtn.closest('tr');
            }
        }

        if (serviceRow) {
            // Update the approval status cell
            const approvalCell = serviceRow.querySelector('.approval-status-cell');
            if (approvalCell) {
                let statusHtml = '';
                if (serviceData.approval_status === 'approved') {
                    statusHtml = `
                        <span class="badge badge-success text-white">
                            <i class="fas fa-check"></i> ${serviceData.approval_status_display}
                        </span>
                        ${serviceData.approved_by ? `<br><small class="text-success"><strong>${serviceData.approved_by}</strong></small>` : ''}
                    `;
                } else if (serviceData.approval_status === 'rejected') {
                    statusHtml = `
                        <span class="badge badge-danger text-white">
                            <i class="fas fa-times"></i> ${serviceData.approval_status_display}
                        </span>
                        ${serviceData.rejection_reason ? `<br><small class="text-danger" title="${serviceData.rejection_reason}">${serviceData.rejection_reason.substring(0, 30)}${serviceData.rejection_reason.length > 30 ? '...' : ''}</small>` : ''}
                    `;
                }
                approvalCell.innerHTML = statusHtml;
            }
        }
    }

    function showNotification(type, message) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
        notification.style.position = 'fixed';
        notification.style.top = '20px';
        notification.style.right = '20px';
        notification.style.zIndex = '9999';
        notification.style.minWidth = '300px';
        
        notification.innerHTML = `
            ${message}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 5000);
    }
});

// Reject service modal
function showRejectModal(serviceId) {
    const modal = `
        <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Từ chối dịch vụ</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <form id="rejectForm">
                        <div class="modal-body">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <div class="form-group">
                                <label for="rejection_reason">Lý do từ chối:</label>
                                <textarea name="rejection_reason" id="rejection_reason" 
                                          class="form-control" rows="4" 
                                          placeholder="Nhập lý do từ chối dịch vụ..." required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-danger" id="rejectSubmitBtn">Từ chối</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    $('#rejectModal').remove();
    
    // Add modal to body
    $('body').append(modal);
    
    // Handle form submission
    $('#rejectForm').on('submit', function(e) {
        e.preventDefault();
        rejectService(serviceId);
    });
    
    // Show modal
    $('#rejectModal').modal('show');
}

function rejectService(serviceId) {
    const form = document.getElementById('rejectForm');
    const formData = new FormData(form);
    const submitBtn = document.getElementById('rejectSubmitBtn');
    
    // Show loading state
    const originalContent = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
    submitBtn.disabled = true;

    fetch(`/services/${serviceId}/reject`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': formData.get('_token'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update the approval status in the table
            updateApprovalStatus(serviceId, data.service);
            
            // Show success message
            showNotification('success', data.message);
            
            // Close modal
            $('#rejectModal').modal('hide');
            
            // Hide the reject button
            const rejectBtn = document.querySelector(`button[onclick="showRejectModal(${serviceId})"]`);
            if (rejectBtn) {
                rejectBtn.style.display = 'none';
            }
        } else {
            showNotification('error', data.message);
            // Restore button
            submitBtn.innerHTML = originalContent;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('error', 'Có lỗi xảy ra khi từ chối dịch vụ');
        // Restore button
        submitBtn.innerHTML = originalContent;
        submitBtn.disabled = false;
    });
}
</script>
@endpush
