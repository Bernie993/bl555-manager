@extends('layouts.app')

@section('title', 'Tạo đề xuất từ dịch vụ')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-plus mr-2"></i>
                        Tạo đề xuất từ dịch vụ: {{ $service->name }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('services.show', $service) }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left mr-1"></i>Quay lại
                        </a>
                    </div>
                </div>

                <!-- Service Info -->
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle mr-2"></i>Thông tin dịch vụ được chọn</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Dịch vụ:</strong> {{ $service->name }}<br>
                                <strong>Loại:</strong> <span class="badge badge-info">{{ $service->getTypeDisplayName() }}</span><br>
                                <strong>Website:</strong> <a href="{{ $service->website }}" target="_blank">{{ $service->website }}</a><br>
                                <strong>Đối tác:</strong> {{ $service->partner->name }}
                            </div>
                            <div class="col-md-6">
                                <strong>Giá:</strong> <span class="text-success">{{ $service->formatted_price }}</span><br>
                                <strong>Lĩnh vực:</strong> {{ $service->category ?? 'N/A' }}<br>
                                @if($service->dr || $service->da || $service->pa || $service->tf)
                                    <strong>Chỉ số SEO:</strong> 
                                    @if($service->dr) DR:{{ $service->dr }} @endif
                                    @if($service->da) DA:{{ $service->da }} @endif
                                    @if($service->pa) PA:{{ $service->pa }} @endif
                                    @if($service->tf) TF:{{ $service->tf }} @endif
                                @endif
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('service-proposals.store') }}">
                        @csrf
                        <input type="hidden" name="service_id" value="{{ $service->id }}">
                        
                        <!-- Auto-selected budget (hidden) -->
                        @if($budgets->isNotEmpty())
                            <input type="hidden" name="budget_id" value="{{ $budgets->first()->id }}">
                            <div class="alert alert-info">
                                <strong>Ngân sách được chọn:</strong> {{ $budgets->first()->description ?? 'Ngân sách mặc định' }} 
                                - Còn lại: {{ number_format($budgets->first()->remaining_amount, 0, ',', '.') }} VNĐ
                            </div>
                        @endif

                        <div class="row">
                            <!-- Số lượng -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="quantity" class="required">Số lượng:</label>
                                    <input type="number" name="quantity" id="quantity" 
                                           class="form-control @error('quantity') is-invalid @enderror" 
                                           value="{{ old('quantity', 1) }}" required min="1" max="1000">
                                    @error('quantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Link đề xuất -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="proposed_link" class="required">Link đề xuất:</label>
                                    <input type="url" name="proposed_link" id="proposed_link" 
                                           class="form-control @error('proposed_link') is-invalid @enderror" 
                                           value="{{ old('proposed_link') }}" required
                                           placeholder="https://example.com/target-page">
                                    @error('proposed_link')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Auto-filled fields (readonly) -->
                        <div class="card mt-3">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-robot mr-2"></i>Thông tin tự động điền
                                </h5>
                            </div>
                            <div class="card-body bg-light">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Tên dịch vụ:</label>
                                            <input type="text" class="form-control" value="{{ $service->name }}" readonly>
                                            <input type="hidden" name="service_name" value="{{ $service->name }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Website đối tác:</label>
                                            <input type="text" class="form-control" value="{{ $service->website }}" readonly>
                                            <input type="hidden" name="partner_website" value="{{ $service->website }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Giá đơn vị:</label>
                                            <input type="text" class="form-control" value="{{ $service->formatted_price }}" readonly>
                                            <input type="hidden" name="unit_price" value="{{ $service->price }}">
                                            <!-- Add supplier info for backward compatibility -->
                                            <input type="hidden" name="supplier_name" value="{{ $service->partner->name ?? '' }}">
                                            <input type="hidden" name="supplier_telegram" value="{{ $service->partner->telegram ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Tổng tiền (sẽ tự động tính):</label>
                                            <input type="text" id="total_amount_display" class="form-control font-weight-bold text-success" readonly>
                                            <input type="hidden" name="amount" id="total_amount_hidden">
                                        </div>
                                    </div>
                                </div>

                                @if($service->keywords_string)
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label>Keywords:</label>
                                            <textarea class="form-control" rows="2" readonly>{{ $service->keywords_string }}</textarea>
                                            <input type="hidden" name="keywords" value="{{ $service->keywords_string }}">
                                        </div>
                                    </div>
                                </div>
                                @endif

                                @if($service->description)
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label>Mô tả dịch vụ:</label>
                                            <textarea class="form-control" rows="3" readonly>{{ $service->description }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Ghi chú thêm (optional) -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="notes">Ghi chú thêm:</label>
                                    <textarea name="notes" id="notes" 
                                              class="form-control @error('notes') is-invalid @enderror" 
                                              rows="3" placeholder="Ghi chú thêm về đề xuất này...">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('services.show', $service) }}" class="btn btn-secondary">
                                    <i class="fas fa-times mr-2"></i>Hủy
                                </a>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-paper-plane mr-2"></i>Gửi đề xuất
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.required:after {
    content: ' *';
    color: red;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    const unitPrice = {{ $service->price }};
    
    function updateTotalAmount() {
        const quantity = parseInt($('#quantity').val()) || 0;
        const totalAmount = quantity * unitPrice;
        
        $('#total_amount_display').val(new Intl.NumberFormat('vi-VN').format(totalAmount) + ' VNĐ');
        $('#total_amount_hidden').val(totalAmount);
        
        console.log('Quantity:', quantity, 'Unit Price:', unitPrice, 'Total:', totalAmount);
    }
    
    // Update total when quantity changes
    $('#quantity').on('input', updateTotalAmount);
    
    // Initial calculation
    updateTotalAmount();
    
    // Debug form submission
    $('form').on('submit', function(e) {
        console.log('Form submitted!');
        console.log('Form data:', $(this).serialize());
        
        // Check required fields
        const quantity = $('#quantity').val();
        const proposalLink = $('#proposed_link').val();
        const amount = $('#total_amount_hidden').val();
        
        console.log('Quantity:', quantity);
        console.log('Proposal Link:', proposalLink);
        console.log('Amount:', amount);
        
        if (!quantity || !proposalLink || !amount) {
            console.error('Missing required fields');
            e.preventDefault();
            alert('Vui lòng điền đầy đủ thông tin bắt buộc');
            return false;
        }
    });
});
</script>
@endpush
