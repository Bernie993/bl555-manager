@extends('layouts.app')

@section('title', 'Tạo Đề xuất Dịch vụ')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('service-proposals.index') }}">Quản lý Đề xuất Dịch vụ</a></li>
    <li class="breadcrumb-item active">Tạo Đề xuất</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-plus me-2"></i>
                @if(isset($bulkServices) && $bulkServices)
                    Tạo Đề xuất Dịch vụ Hàng loạt
                @else
                    Tạo Đề xuất Dịch vụ
                @endif
            </h1>
            <a href="{{ route('service-proposals.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>
                Quay lại
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        @if(isset($bulkServices) && $bulkServices)
            <!-- Bulk Services Display -->
            @php
                \Log::info('Rendering bulk services:', [
                    'bulkServices' => $bulkServices,
                    'count' => count($bulkServices)
                ]);
            @endphp
            @foreach($bulkServices as $partnerId => $services)
                @php
                    \Log::info('Rendering form for partner:', [
                        'partnerId' => $partnerId,
                        'services' => $services,
                        'partnerName' => $services[0]['partner_name'] ?? 'N/A',
                        'servicesCount' => count($services)
                    ]);
                @endphp
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-user-tie me-2"></i>
                            Đối tác: {{ $services[0]['partner_name'] ?? 'N/A' }}
                            <span class="badge badge-info ms-2">{{ count($services) }} dịch vụ</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="bulk-proposal-form" data-partner-id="{{ $partnerId }}">
                            <input type="hidden" name="partner_id" value="{{ $partnerId }}">
                            <!-- Debug: Partner {{ $partnerId }} form rendered -->

                            <!-- Services List -->
                            <div class="mb-3">
                                <label class="form-label"><strong>Dịch vụ đã chọn:</strong></label>
                                @if($websites->count() == 0)
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <strong>Cảnh báo:</strong> Bạn chưa có domain nào được phân công. Vui lòng liên hệ quản lý để được gán domain trước khi tạo đề xuất.
                                    </div>
                                @endif
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Tên dịch vụ</th>
                                                <th>Website</th>
                                                <th>Domain đích</th>
                                                <th>Giá</th>
                                                <th>Số lượng</th>
                                                <th>Thành tiền</th>
                                                <th width="50">Hành động</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($services as $index => $service)
                                                <tr>
                                                    <td>
                                                        {{ $service['name'] }}
                                                        <input type="hidden" name="services[{{ $index }}][service_id]" value="{{ $service['id'] }}">
                                                        <input type="hidden" name="services[{{ $index }}][service_name]" value="{{ $service['name'] }}">
                                                        <input type="hidden" name="services[{{ $index }}][unit_price]" value="{{ $service['price'] }}">
                                                        <input type="hidden" name="services[{{ $index }}][keywords]" value="{{ $service['keywords'] }}">
                                                    </td>
                                                    <td>
                                                        <a href="{{ $service['website'] }}" target="_blank" class="text-decoration-none">
                                                            {{ Str::limit($service['website'], 30) }}
                                                            <i class="fas fa-external-link-alt fa-xs"></i>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <select class="form-select form-select-sm"
                                                                name="services[{{ $index }}][target_domain]"
                                                                style="min-width: 150px;">
                                                            <option value="">-- Chọn domain --</option>
                                                            @if($websites->count() > 0)
                                                                @foreach($websites as $website)
                                                                    <option value="{{ $website->name }}">
                                                                        {{ $website->name }}
                                                                    </option>
                                                                @endforeach
                                                            @else
                                                                <option value="" disabled>Không có domain</option>
                                                            @endif
                                                        </select>
                                                    </td>
                                                    <td>{{ number_format($service['price']) }} VNĐ</td>
                                                    <td>
                                                        <input type="number"
                                                               name="services[{{ $index }}][quantity]"
                                                               class="form-control form-control-sm quantity-input"
                                                               value="1"
                                                               min="1"
                                                               data-price="{{ $service['price'] }}"
                                                               data-index="{{ $index }}">
                                                    </td>
                                                    <td>
                                                        <span class="amount-display" data-index="{{ $index }}">{{ number_format($service['price']) }} VNĐ</span>
                                                        <input type="hidden" name="services[{{ $index }}][amount]" class="amount-input" value="{{ $service['price'] }}">
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-danger remove-service-btn"
                                                                data-service-id="{{ $service['id'] }}"
                                                                data-partner-id="{{ $partnerId }}"
                                                                title="Xóa dịch vụ">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-info">
                                                <th colspan="6">Tổng cộng:</th>
                                                <th>
                                                    <span class="total-amount">{{ number_format(array_sum(array_column($services, 'price'))) }} VNĐ</span>
                                                </th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            <!-- Common fields for this partner -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="supplier_name_{{ $partnerId }}" class="form-label">Nhà cung cấp <span class="text-danger">*</span></label>
                                    <input type="text"
                                           class="form-control"
                                           id="supplier_name_{{ $partnerId }}"
                                           name="supplier_name"
                                           value="{{ $services[0]['supplier_name'] ?? '' }}"
                                           required
                                           readonly>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="supplier_telegram_{{ $partnerId }}" class="form-label">Telegram NCC</label>
                                    <input type="text"
                                           class="form-control"
                                           id="supplier_telegram_{{ $partnerId }}"
                                           name="supplier_telegram"
                                           value="{{ $services[0]['supplier_telegram'] ?? '' }}"
                                           readonly>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="proposal_link_{{ $partnerId }}" class="form-label">Link đề xuất <span class="text-danger">*</span></label>
                                <input type="url"
                                       class="form-control"
                                       id="proposal_link_{{ $partnerId }}"
                                       name="proposal_link"
                                       required
                                       placeholder="https://example.com/proposal">
                            </div>

                            <!-- Auto-selected budget (hidden) -->
                            @if($budgets->isNotEmpty())
                                <input type="hidden" name="budget_id" value="{{ $budgets->first()->id }}">
                            @endif

                            <div class="mb-3">
                                <label for="notes_{{ $partnerId }}" class="form-label">Ghi chú</label>
                                <textarea class="form-control"
                                          id="notes_{{ $partnerId }}"
                                          name="notes"
                                          rows="3"
                                          placeholder="Ghi chú thêm về đề xuất này..."></textarea>
                            </div>

                        </div>
                    </div>
                </div>
            @endforeach
            @php
                \Log::info('Finished rendering bulk services forms');
            @endphp
        @else
            <!-- Single Service Form -->
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('service-proposals.store') }}" method="POST">
                        @csrf

                        <!-- Hidden service ID if coming from services page -->
                        @if(isset($serviceData['service_id']))
                            <input type="hidden" name="service_id" value="{{ $serviceData['service_id'] }}">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Tạo đề xuất từ dịch vụ:</strong> {{ $serviceData['service_name'] ?? 'N/A' }}
                                <br><small class="text-muted">
                                    <i class="fas fa-barcode me-1"></i>
                                    Mã đơn hàng sẽ được tạo tự động: <strong>{{ strtoupper(auth()->user()->name) }}-{{ now()->format('dmY') }}-XX</strong>
                                </small>
                            </div>
                        @endif

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="service_name" class="form-label">Tên dịch vụ <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('service_name') is-invalid @enderror"
                                   id="service_name"
                                   name="service_name"
                                   value="{{ old('service_name', $serviceData['service_name'] ?? '') }}"
                                   required
                                   placeholder="Nhập tên dịch vụ cần đề xuất"
                                   @if(isset($serviceData['service_name'])) readonly @endif>
                            @error('service_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="quantity" class="form-label">Số lượng <span class="text-danger">*</span></label>
                            <input type="number"
                                   class="form-control @error('quantity') is-invalid @enderror"
                                   id="quantity"
                                   name="quantity"
                                   value="{{ old('quantity', 1) }}"
                                   required
                                   min="1"
                                   placeholder="1">
                            @error('quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="target_domain" class="form-label">
                                <i class="fas fa-globe me-1"></i>
                                Domain đích
                            </label>
                            <select class="form-select @error('target_domain') is-invalid @enderror"
                                    id="target_domain"
                                    name="target_domain">
                                <option value="">-- Chọn domain để áp dụng dịch vụ --</option>
                                @if($websites->count() > 0)
                                    @foreach($websites as $website)
                                        <option value="{{ $website->name }}"
                                                {{ old('target_domain') == $website->name ? 'selected' : '' }}>
                                            {{ $website->name }}
                                            @if($website->seoer_name && auth()->user()->role->name !== 'seoer')
                                                ({{ $website->seoer_name }})
                                            @endif
                                        </option>
                                    @endforeach
                                @else
                                    <option value="" disabled>Không có domain nào được gán cho bạn</option>
                                @endif
                            </select>
                            @error('target_domain')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                @if($websites->count() > 0)
                                    Chọn domain/website mà dịch vụ này sẽ được áp dụng
                                @else
                                    <span class="text-warning">Bạn chưa có domain nào được phân công. Vui lòng liên hệ quản lý để được gán domain.</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="supplier_name" class="form-label">Nhà cung cấp <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('supplier_name') is-invalid @enderror"
                                   id="supplier_name"
                                   name="supplier_name"
                                   value="{{ old('supplier_name', $serviceData['supplier_name'] ?? '') }}"
                                   required
                                   placeholder="Tên nhà cung cấp"
                                   @if(isset($serviceData['supplier_name'])) readonly @endif>
                            @error('supplier_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="proposal_link" class="form-label">Link đề xuất</label>
                            <input type="url"
                                   class="form-control @error('proposal_link') is-invalid @enderror"
                                   id="proposal_link"
                                   name="proposal_link"
                                   value="{{ old('proposal_link') }}"
                                   placeholder="https://example.com/proposal">
                            @error('proposal_link')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="supplier_telegram" class="form-label">Telegram NCC</label>
                            <input type="text"
                                   class="form-control @error('supplier_telegram') is-invalid @enderror"
                                   id="supplier_telegram"
                                   name="supplier_telegram"
                                   value="{{ old('supplier_telegram', $serviceData['supplier_telegram'] ?? '') }}"
                                   placeholder="@username hoặc link Telegram"
                                   @if(isset($serviceData['supplier_telegram'])) readonly @endif>
                            @error('supplier_telegram')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="unit_price" class="form-label">Đơn giá (VNĐ) <span class="text-danger">*</span></label>
                            <input type="number"
                                   class="form-control @error('unit_price') is-invalid @enderror"
                                   id="unit_price"
                                   name="unit_price"
                                   value="{{ old('unit_price', $serviceData['unit_price'] ?? '') }}"
                                   required
                                   min="0"
                                   step="1000"
                                   placeholder="Nhập đơn giá"
                                   @if(isset($serviceData['unit_price'])) readonly @endif>
                            @error('unit_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <!-- Hidden field for total amount calculation -->
                            <input type="hidden" id="amount" name="amount" value="{{ old('amount', $serviceData['unit_price'] ?? '') }}">
                        </div>
                    </div>

                    <!-- Auto-selected budget (hidden) -->
                    @if($budgets->isNotEmpty())
                        <input type="hidden" name="budget_id" value="{{ $budgets->first()->id }}">
                        <div class="alert alert-info">
                            <i class="fas fa-wallet me-2"></i>
                            <strong>Ngân sách được chọn:</strong> {{ $budgets->first()->seoer }} - {{ $budgets->first()->formatted_total_budget }}
                            <br><small>Còn lại: {{ $budgets->first()->formatted_remaining_amount }}</small>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Cảnh báo:</strong> Bạn chưa có ngân sách nào. Vui lòng liên hệ quản lý để tạo ngân sách.
                        </div>
                    @endif

                    @if($websites->count() == 0 && auth()->user()->role && auth()->user()->role->name === 'seoer')
                        <div class="alert alert-warning">
                            <i class="fas fa-globe me-2"></i>
                            <strong>Không có domain:</strong> Bạn chưa có domain nào được phân công. Vui lòng liên hệ quản lý để được gán domain.
                        </div>
                    @endif

                    <div class="mb-3">
                        <label for="notes" class="form-label">Ghi chú</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror"
                                  id="notes"
                                  name="notes"
                                  rows="4"
                                  placeholder="Ghi chú thêm về dịch vụ, yêu cầu đặc biệt...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('service-proposals.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>
                            Hủy
                        </a>
                        @if($budgets->isNotEmpty())
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>
                                Gửi Đề xuất
                            </button>
                        @else
                            <button type="button" class="btn btn-secondary" disabled>
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Không thể gửi (chưa có ngân sách)
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        @endif
    </div>

    <div class="col-lg-4">
        @if(isset($bulkServices) && $bulkServices)
            <!-- Bulk Services Summary -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Tóm tắt đề xuất hàng loạt
                    </h6>
                </div>
                <div class="card-body">
                    <div class="small">
                        <p><strong>Tổng số đối tác:</strong> {{ count($bulkServices) }}</p>
                        <p><strong>Tổng số dịch vụ:</strong> {{ array_sum(array_map('count', $bulkServices)) }}</p>

                        @if($budgets->isNotEmpty())
                            <div class="alert alert-info">
                                <i class="fas fa-wallet me-2"></i>
                                <strong>Ngân sách:</strong> {{ $budgets->first()->seoer }} - {{ $budgets->first()->formatted_total_budget }}
                                <br><small>Còn lại: {{ $budgets->first()->formatted_remaining_amount }}</small>
                            </div>
                        @endif

                        <p><strong>Lưu ý:</strong></p>
                        <ul>
                            <li>Mỗi đối tác sẽ có một đề xuất riêng biệt</li>
                            <li>Bạn có thể điều chỉnh số lượng cho từng dịch vụ</li>
                            <li>Bạn có thể xóa dịch vụ không cần thiết</li>
                            <li>Tất cả đề xuất sẽ được tạo cùng lúc</li>
                        </ul>

                        @if($budgets->isNotEmpty())
                            <div class="d-grid">
                                <button type="button" id="submitAllProposals" class="btn btn-primary btn-lg" onclick="submitBulkProposals()">
                                    <i class="fas fa-paper-plane me-2"></i>
                                    Gửi Tất Cả Đề Xuất
                                </button>
                            </div>
                        @else
                            <div class="d-grid">
                                <button type="button" class="btn btn-secondary btn-lg" disabled>
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Không thể gửi (chưa có ngân sách)
                                </button>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        @else
            <!-- Single Service Info -->
            <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Hướng dẫn
                </h6>
            </div>
            <div class="card-body">
                <div class="small">
                    <p><strong>Các trường bắt buộc:</strong></p>
                    <ul class="mb-3">
                        <li>Tên dịch vụ</li>
                        <li>Số lượng</li>
                        <li>Nhà cung cấp</li>
                        <li>Số tiền</li>
                    </ul>

                    <p><strong>Quy trình duyệt:</strong></p>
                    <ol class="mb-3">
                        <li>Tạo đề xuất (trạng thái: Chờ duyệt)</li>
                        <li>Admin duyệt/từ chối</li>
                        <li>Xác nhận đơn hàng (Admin hoặc bạn)</li>
                        <li>Hoàn thành thanh toán (Admin)</li>
                    </ol>

                    <p><strong>Lưu ý:</strong></p>
                    <ul>
                        <li>Chỉ có thể chỉnh sửa khi đang chờ duyệt</li>
                        <li>Liên kết ngân sách để tự động cập nhật chi tiêu</li>
                        <li>Thông tin nhà cung cấp giúp liên hệ dễ dàng</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-calculator me-2"></i>
                    Tính toán
                </h6>
            </div>
            <div class="card-body">
                <div class="small">
                    <div class="row">
                        <div class="col-6">
                            <strong>Số lượng:</strong>
                        </div>
                        <div class="col-6" id="display-quantity">
                            1
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <strong>Đơn giá:</strong>
                        </div>
                        <div class="col-6" id="display-unit-price">
                            0 VNĐ
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-6">
                            <strong>Tổng tiền:</strong>
                        </div>
                        <div class="col-6" id="display-total">
                            <strong>0 VNĐ</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

<style>
.is-invalid {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
}

.invalid-feedback {
    display: block;
    width: 100%;
    margin-top: 0.25rem;
    font-size: 0.875em;
    color: #dc3545;
}

.validation-error {
    border-left: 4px solid #dc3545;
    background-color: #f8d7da;
    border-color: #f5c6cb;
}
</style>

<script>
// Helper functions for validation
function showFieldError(field, message) {
    if (!field) return;

    // Add error class
    field.classList.add('is-invalid');

    // Remove existing error message
    const existingError = field.parentNode.querySelector('.invalid-feedback');
    if (existingError) {
        existingError.remove();
    }

    // Add new error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'invalid-feedback';
    errorDiv.textContent = message;
    field.parentNode.appendChild(errorDiv);
}

function clearValidationErrors(form) {
    // Clear all error classes and messages
    form.querySelectorAll('.is-invalid').forEach(field => {
        field.classList.remove('is-invalid');
    });

    form.querySelectorAll('.invalid-feedback').forEach(error => {
        error.remove();
    });

    // Clear general error message
    const generalError = form.querySelector('.validation-error');
    if (generalError) {
        generalError.remove();
    }
}

function showValidationError(form, message) {
    // Clear previous errors
    clearValidationErrors(form);

    // Add general error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'alert alert-danger validation-error mt-2';
    errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>' + message;

    // Insert after the services table
    const servicesTable = form.querySelector('.table-responsive');
    if (servicesTable) {
        servicesTable.parentNode.insertBefore(errorDiv, servicesTable.nextSibling);
    } else {
        form.appendChild(errorDiv);
    }
}

// Remove service functionality
document.addEventListener('click', function(e) {
    if (e.target.closest('.remove-service-btn')) {
        const btn = e.target.closest('.remove-service-btn');
        const serviceId = btn.dataset.serviceId;
        const partnerId = btn.dataset.partnerId;
        const row = btn.closest('tr');
        const form = btn.closest('.bulk-proposal-form');
        
        if (confirm('Bạn có chắc chắn muốn xóa dịch vụ này?')) {
            // Remove the row
            row.remove();
            
            // Update total
            updateFormTotal(form);
            
            // Check if this partner has no services left
            const remainingRows = form.querySelectorAll('tbody tr');
            if (remainingRows.length === 0) {
                // Remove the entire partner card
                const card = form.closest('.card');
                card.remove();
                
                // Check if there are any forms left
                const remainingForms = document.querySelectorAll('.bulk-proposal-form');
                if (remainingForms.length === 0) {
                    // Redirect back to services page
                    window.location.href = '{{ route("services.index") }}';
                }
            }
        }
    }
});

// Update form total function
function updateFormTotal(form) {
    let total = 0;
    form.querySelectorAll('.amount-input').forEach(function(input) {
        total += parseFloat(input.value) || 0;
    });
    
    const totalDisplay = form.querySelector('.total-amount');
    if (totalDisplay) {
        totalDisplay.textContent = new Intl.NumberFormat('vi-VN').format(total) + ' VNĐ';
    }
}

// Quantity input change handler
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('quantity-input')) {
        const input = e.target;
        const index = input.dataset.index;
        const price = parseFloat(input.dataset.price);
        const quantity = parseInt(input.value) || 1;
        const amount = price * quantity;
        
        // Update amount display and hidden input
        const amountDisplay = document.querySelector('.amount-display[data-index="' + index + '"]');
        const amountInput = document.querySelector('input[name="services[' + index + '][amount]"]');
        
        if (amountDisplay) {
            amountDisplay.textContent = new Intl.NumberFormat('vi-VN').format(amount) + ' VNĐ';
        }
        if (amountInput) {
            amountInput.value = amount;
        }
        
        // Update total for this form
        updateFormTotal(input.closest('.bulk-proposal-form'));
    }
});

function submitBulkProposals() {
    // Get all forms
    const forms = document.querySelectorAll('.bulk-proposal-form');
    console.log('Found forms:', forms.length);

    if (forms.length === 0) {
        alert('Không có đề xuất nào để gửi');
        return;
    }

    // Collect data from all forms
    let allProposals = [];
    let hasValidationError = false;

    forms.forEach(function(form) {
        const partnerId = form.dataset.partnerId;
        const services = [];

        // Get services data
        const rows = form.querySelectorAll('tbody tr');
        let hasError = false;

        rows.forEach(function(row) {
            const serviceId = row.querySelector('input[name*="[service_id]"]')?.value;
            const serviceName = row.querySelector('input[name*="[service_name]"]')?.value;
            const unitPrice = row.querySelector('input[name*="[unit_price]"]')?.value;
            const keywords = row.querySelector('input[name*="[keywords]"]')?.value;
            const targetDomain = row.querySelector('select[name*="[target_domain]"]')?.value;
            const quantity = row.querySelector('.quantity-input')?.value;
            const amount = row.querySelector('.amount-input')?.value;

            // Validate target domain for each service
            if (!targetDomain) {
                showFieldError(row.querySelector('select[name*="[target_domain]"]'), 'Vui lòng chọn domain đích');
                hasError = true;
            }

            if (serviceId && serviceName && unitPrice && quantity) {
                services.push({
                    service_id: serviceId,
                    service_name: serviceName,
                    target_domain: targetDomain || '',
                    unit_price: unitPrice,
                    keywords: keywords || '',
                    quantity: quantity,
                    amount: amount || (parseFloat(unitPrice) * parseInt(quantity))
                });
            }
        });

        if (hasError) {
            hasValidationError = true;
            return;
        }

        if (services.length === 0) {
            showValidationError(form, 'Vui lòng chọn ít nhất một dịch vụ cho đối tác này');
            hasValidationError = true;
            return;
        }

        // Get common data
        let supplierName = form.querySelector('input[name="supplier_name"]')?.value;
        let supplierTelegram = form.querySelector('input[name="supplier_telegram"]')?.value;
        let proposalLink = form.querySelector('input[name="proposal_link"]')?.value;
        let notes = form.querySelector('textarea[name="notes"]')?.value;

        // Clear previous errors
        clearValidationErrors(form);

        let commonHasError = false;

        if (!supplierName) {
            showFieldError(form.querySelector('input[name="supplier_name"]'), 'Vui lòng nhập tên nhà cung cấp');
            commonHasError = true;
        }

        if (!proposalLink) {
            showFieldError(form.querySelector('input[name="proposal_link"]'), 'Vui lòng nhập link đề xuất');
            commonHasError = true;
        }

        if (commonHasError) {
            hasValidationError = true;
            return;
        }

        allProposals.push({
            partner_id: partnerId,
            supplier_name: supplierName,
            supplier_telegram: supplierTelegram || '',
            proposal_link: proposalLink,
            notes: notes || '',
            services: services
        });
    });

    if (hasValidationError) {
        return;
    }

    if (allProposals.length === 0) {
        alert('Không có dữ liệu đề xuất nào để gửi');
        return;
    }

    // Show confirmation
    const confirmSubmit = confirm(`Bạn có chắc chắn muốn gửi ${allProposals.length} đề xuất cho ${allProposals.length} đối tác?`);
    if (!confirmSubmit) {
        return;
    }

    // Create and submit form
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("service-proposals.store") }}';
    form.style.display = 'none';

    // Add CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '{{ csrf_token() }}';
    form.appendChild(csrfInput);

    // Add bulk flag
    const bulkInput = document.createElement('input');
    bulkInput.type = 'hidden';
    bulkInput.name = 'is_bulk_all';
    bulkInput.value = '1';
    form.appendChild(bulkInput);

    // Add budget ID
    const budgetInput = document.createElement('input');
    budgetInput.type = 'hidden';
    budgetInput.name = 'budget_id';
    budgetInput.value = '{{ $budgets->first()->id ?? 1 }}';
    form.appendChild(budgetInput);

    // Add proposals data
    const proposalsInput = document.createElement('input');
    proposalsInput.type = 'hidden';
    proposalsInput.name = 'proposals';
    proposalsInput.value = JSON.stringify(allProposals);
    form.appendChild(proposalsInput);

    console.log('Submitting form with data:', allProposals);
    document.body.appendChild(form);
    form.submit();
}
</script>
