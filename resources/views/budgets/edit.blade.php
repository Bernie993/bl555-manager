@extends('layouts.app')

@section('title', 'Sửa Ngân sách')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('budgets.index') }}">Quản lý Ngân sách</a></li>
    <li class="breadcrumb-item active">Sửa Ngân sách</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-edit me-2"></i>
                Sửa Ngân sách: {{ $budget->seoer }}
            </h1>
            <a href="{{ route('budgets.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>
                Quay lại
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('budgets.update', $budget) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="seoer" class="form-label">Seoer <span class="text-danger">*</span></label>
                            <select class="form-select @error('seoer') is-invalid @enderror" 
                                    id="seoer" 
                                    name="seoer" 
                                    required>
                                <option value="">-- Chọn Seoer --</option>
                                @foreach($seoers as $seoerUser)
                                    <option value="{{ $seoerUser->name }}" {{ (old('seoer', $budget->seoer) === $seoerUser->name) ? 'selected' : '' }}>
                                        {{ $seoerUser->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('seoer')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="total_budget" class="form-label">Tổng ngân sách (VNĐ) <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control @error('total_budget') is-invalid @enderror" 
                                   id="total_budget" 
                                   name="total_budget" 
                                   value="{{ old('total_budget', $budget->total_budget) }}" 
                                   required
                                   min="0"
                                   step="1000">
                            @error('total_budget')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Current Budget Status -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-chart-bar me-2"></i>
                                Trạng thái Ngân sách Hiện tại
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="text-center p-3 bg-primary bg-opacity-10 rounded">
                                        <h5 class="text-primary mb-1">{{ $budget->formatted_total_budget }}</h5>
                                        <small class="text-muted">Tổng ngân sách</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center p-3 bg-warning bg-opacity-10 rounded">
                                        <h5 class="text-warning mb-1">{{ $budget->formatted_spent_amount }}</h5>
                                        <small class="text-muted">Đã chi tiêu (tự động)</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center p-3 bg-success bg-opacity-10 rounded">
                                        <h5 class="text-success mb-1">{{ $budget->formatted_remaining_amount }}</h5>
                                        <small class="text-muted">Còn lại</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <div class="alert alert-info">
                                    <h6 class="alert-heading">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Tự động cập nhật
                                    </h6>
                                    <p class="mb-0">
                                        <strong>Số tiền đã chi tiêu</strong> được tự động tính từ tổng các 
                                        <strong>Đề xuất dịch vụ</strong> đã hoàn thành thanh toán và liên kết với ngân sách này.
                                        <br><small class="text-muted">
                                            Hiện có {{ $budget->serviceProposals()->where('status', 'completed')->count() }} đề xuất đã hoàn thành.
                                        </small>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="period_start" class="form-label">Ngày bắt đầu kỳ</label>
                            <input type="date" 
                                   class="form-control @error('period_start') is-invalid @enderror" 
                                   id="period_start" 
                                   name="period_start" 
                                   value="{{ old('period_start', $budget->period_start?->format('Y-m-d')) }}">
                            @error('period_start')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="period_end" class="form-label">Ngày kết thúc kỳ</label>
                            <input type="date" 
                                   class="form-control @error('period_end') is-invalid @enderror" 
                                   id="period_end" 
                                   name="period_end" 
                                   value="{{ old('period_end', $budget->period_end?->format('Y-m-d')) }}">
                            @error('period_end')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Mô tả</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" 
                                  name="description" 
                                  rows="4">{{ old('description', $budget->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('budgets.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>
                            Hủy
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            Cập nhật
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-chart-pie me-2"></i>
                    Thống kê hiện tại
                </h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    @php
                        $percentage = $budget->spending_percentage;
                        $progressClass = $percentage > 80 ? 'bg-danger' : ($percentage > 60 ? 'bg-warning' : 'bg-success');
                    @endphp
                    <div class="progress-circle-small mb-3" data-percentage="{{ $percentage }}">
                        <span class="progress-value">{{ number_format($percentage, 1) }}%</span>
                    </div>
                </div>
                
                <div class="row text-center small">
                    <div class="col-12 mb-2">
                        <div class="border-bottom pb-2">
                            <div class="text-muted">Tổng ngân sách</div>
                            <div class="h6 text-primary">{{ $budget->formatted_total_budget }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted">Đã chi</div>
                        <div class="h6 text-danger">{{ $budget->formatted_spent_amount }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted">Còn lại</div>
                        <div class="h6 text-success">{{ $budget->formatted_remaining_amount }}</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-calculator me-2"></i>
                    Tính toán tự động
                </h6>
            </div>
            <div class="card-body">
                <div class="small">
                    <div class="row">
                        <div class="col-6">
                            <strong>Tổng ngân sách:</strong>
                        </div>
                        <div class="col-6" id="display-total">
                            {{ $budget->formatted_total_budget }}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <strong>Đã chi tiêu:</strong>
                        </div>
                        <div class="col-6" id="display-spent">
                            {{ $budget->formatted_spent_amount }}
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-6">
                            <strong>Còn lại:</strong>
                        </div>
                        <div class="col-6" id="display-remaining">
                            <strong>{{ $budget->formatted_remaining_amount }}</strong>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-6">
                            <strong>Tiến độ:</strong>
                        </div>
                        <div class="col-6" id="display-percentage">
                            <strong>{{ number_format($percentage, 1) }}%</strong>
                        </div>
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
    </div>
</div>
@endsection

@section('scripts')
<style>
.progress-circle-small {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: conic-gradient(#007bff 0deg, #007bff {{ $percentage * 3.6 }}deg, #e9ecef {{ $percentage * 3.6 }}deg, #e9ecef 360deg);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    position: relative;
}

.progress-circle-small::before {
    content: '';
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: white;
    position: absolute;
}

.progress-value {
    position: relative;
    z-index: 1;
    font-weight: bold;
    font-size: 12px;
    color: #007bff;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const totalInput = document.getElementById('total_budget');
    const spentInput = document.getElementById('spent_amount');
    
    function updateCalculation() {
        const total = parseFloat(totalInput.value) || 0;
        const spent = parseFloat(spentInput.value) || 0;
        const remaining = total - spent;
        const percentage = total > 0 ? (spent / total) * 100 : 0;
        
        document.getElementById('display-total').textContent = formatCurrency(total);
        document.getElementById('display-spent').textContent = formatCurrency(spent);
        document.getElementById('display-remaining').textContent = formatCurrency(remaining);
        document.getElementById('display-percentage').innerHTML = '<strong>' + percentage.toFixed(1) + '%</strong>';
    }
    
    function formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN').format(amount) + ' VNĐ';
    }
    
    totalInput.addEventListener('input', updateCalculation);
    spentInput.addEventListener('input', updateCalculation);
});
</script>
@endsection
