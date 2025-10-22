@extends('layouts.app')

@section('title', 'Sửa Đề xuất Dịch vụ')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('service-proposals.index') }}">Quản lý Đề xuất Dịch vụ</a></li>
    <li class="breadcrumb-item active">Sửa Đề xuất</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-edit me-2"></i>
                Sửa Đề xuất: {{ $serviceProposal->service_name }}
            </h1>
            <a href="{{ route('service-proposals.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>
                Quay lại
            </a>
        </div>
    </div>
</div>

@if($serviceProposal->status !== 'pending')
<div class="alert alert-warning">
    <h6 class="alert-heading">
        <i class="fas fa-exclamation-triangle me-2"></i>
        Không thể chỉnh sửa
    </h6>
    <p class="mb-0">
        Đề xuất này đã có trạng thái <strong>{{ $serviceProposal->getStatusDisplayName() }}</strong> 
        và không thể chỉnh sửa được nữa.
    </p>
</div>
@endif

@if($serviceProposal->created_by !== auth()->id())
<div class="alert alert-danger">
    <h6 class="alert-heading">
        <i class="fas fa-ban me-2"></i>
        Không có quyền
    </h6>
    <p class="mb-0">
        Bạn chỉ có thể chỉnh sửa đề xuất do chính mình tạo ra.
    </p>
</div>
@endif

@if($serviceProposal->status === 'pending' && $serviceProposal->created_by === auth()->id())
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('service-proposals.update', $serviceProposal) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="service_name" class="form-label">Tên dịch vụ <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('service_name') is-invalid @enderror" 
                                   id="service_name" 
                                   name="service_name" 
                                   value="{{ old('service_name', $serviceProposal->service_name) }}" 
                                   required>
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
                                   value="{{ old('quantity', $serviceProposal->quantity) }}" 
                                   required
                                   min="1">
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
                                @foreach($websites as $website)
                                    <option value="{{ $website->name }}" 
                                            {{ old('target_domain', $serviceProposal->target_domain) == $website->name ? 'selected' : '' }}>
                                        {{ $website->name }}
                                        @if($website->seoer_name)
                                            ({{ $website->seoer_name }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('target_domain')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Chọn domain/website mà dịch vụ này sẽ được áp dụng
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
                                   value="{{ old('supplier_name', $serviceProposal->supplier_name) }}" 
                                   required>
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
                                   value="{{ old('proposal_link', $serviceProposal->proposal_link) }}"
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
                                   value="{{ old('supplier_telegram', $serviceProposal->supplier_telegram) }}">
                            @error('supplier_telegram')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="amount" class="form-label">Số tiền (VNĐ) <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control @error('amount') is-invalid @enderror" 
                                   id="amount" 
                                   name="amount" 
                                   value="{{ old('amount', $serviceProposal->amount) }}" 
                                   required
                                   min="0"
                                   step="1000">
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="budget_id" class="form-label">Liên kết với Ngân sách</label>
                        <select class="form-select @error('budget_id') is-invalid @enderror" id="budget_id" name="budget_id">
                            <option value="">Chọn ngân sách (tùy chọn)</option>
                            @foreach($budgets as $budget)
                                <option value="{{ $budget->id }}" {{ old('budget_id', $serviceProposal->budget_id) == $budget->id ? 'selected' : '' }}>
                                    {{ $budget->seoer }} - {{ $budget->formatted_total_budget }}
                                    (Còn lại: {{ $budget->formatted_remaining_amount }})
                                </option>
                            @endforeach
                        </select>
                        @error('budget_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Ghi chú</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" 
                                  name="notes" 
                                  rows="4">{{ old('notes', $serviceProposal->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('service-proposals.show', $serviceProposal) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>
                            Hủy
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            Cập nhật Đề xuất
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Current Status -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Thông tin hiện tại
                </h6>
            </div>
            <div class="card-body">
                <div class="small">
                    <div class="row mb-2">
                        <div class="col-6">
                            <strong>Trạng thái:</strong>
                        </div>
                        <div class="col-6">
                            <span class="badge {{ $serviceProposal->getStatusBadgeClass() }}">
                                {{ $serviceProposal->getStatusDisplayName() }}
                            </span>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6">
                            <strong>Tạo lúc:</strong>
                        </div>
                        <div class="col-6">
                            {{ $serviceProposal->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6">
                            <strong>Cập nhật:</strong>
                        </div>
                        <div class="col-6">
                            {{ $serviceProposal->updated_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Calculation -->
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
                            {{ $serviceProposal->quantity }}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <strong>Đơn giá:</strong>
                        </div>
                        <div class="col-6" id="display-unit-price">
                            {{ number_format($serviceProposal->amount / $serviceProposal->quantity, 0, ',', '.') }} VNĐ
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-6">
                            <strong>Tổng tiền:</strong>
                        </div>
                        <div class="col-6" id="display-total">
                            <strong>{{ $serviceProposal->formatted_amount }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Warning -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Lưu ý
                </h6>
            </div>
            <div class="card-body">
                <div class="small">
                    <ul class="mb-0">
                        <li>Chỉ có thể chỉnh sửa khi đang ở trạng thái "Chờ duyệt"</li>
                        <li>Sau khi được duyệt, đề xuất không thể chỉnh sửa</li>
                        <li>Thay đổi sẽ được lưu ngay lập tức</li>
                        <li>Liên kết ngân sách để tự động cập nhật chi tiêu</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@else
<div class="text-center py-5">
    <i class="fas fa-ban fa-3x text-muted mb-3"></i>
    <h5 class="text-muted">Không thể chỉnh sửa đề xuất này</h5>
    <p class="text-muted">
        Đề xuất chỉ có thể được chỉnh sửa khi đang ở trạng thái "Chờ duyệt" và bởi người tạo.
    </p>
    <a href="{{ route('service-proposals.show', $serviceProposal) }}" class="btn btn-primary">
        <i class="fas fa-eye me-2"></i>
        Xem chi tiết
    </a>
</div>
@endif
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const quantityInput = document.getElementById('quantity');
    const amountInput = document.getElementById('amount');
    
    function updateCalculation() {
        const quantity = parseInt(quantityInput.value) || 1;
        const totalAmount = parseFloat(amountInput.value) || 0;
        const unitPrice = quantity > 0 ? totalAmount / quantity : 0;
        
        document.getElementById('display-quantity').textContent = quantity;
        document.getElementById('display-unit-price').textContent = formatCurrency(unitPrice);
        document.getElementById('display-total').innerHTML = '<strong>' + formatCurrency(totalAmount) + '</strong>';
    }
    
    function formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN').format(amount) + ' VNĐ';
    }
    
    if (quantityInput && amountInput) {
        quantityInput.addEventListener('input', updateCalculation);
        amountInput.addEventListener('input', updateCalculation);
        
        // Initial calculation
        updateCalculation();
    }
});
</script>
@endsection


