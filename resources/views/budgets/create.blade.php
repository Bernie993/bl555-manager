@extends('layouts.app')

@section('title', 'Tạo Ngân sách')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-plus me-2"></i>
                        Tạo Ngân sách Mới
                    </h5>
                </div>

                <div class="card-body">
                    <form action="{{ route('budgets.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="seoer" class="form-label">Seoer <span class="text-danger">*</span></label>
                                    <select class="form-control @error('seoer') is-invalid @enderror" 
                                            id="seoer" 
                                            name="seoer" 
                                            required>
                                        <option value="">-- Chọn Seoer --</option>
                                        @foreach($seoers as $seoer)
                                            <option value="{{ $seoer->name }}" 
                                                    {{ old('seoer') == $seoer->name ? 'selected' : '' }}>
                                                {{ $seoer->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('seoer')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="total_budget" class="form-label">Tổng ngân sách (VNĐ) <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           class="form-control @error('total_budget') is-invalid @enderror" 
                                           id="total_budget" 
                                           name="total_budget" 
                                           value="{{ old('total_budget') }}" 
                                           min="0" 
                                           step="1000"
                                           required>
                                    @error('total_budget')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="period_start" class="form-label">Ngày bắt đầu</label>
                                    <input type="date" 
                                           class="form-control @error('period_start') is-invalid @enderror" 
                                           id="period_start" 
                                           name="period_start" 
                                           value="{{ old('period_start') }}">
                                    @error('period_start')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="period_end" class="form-label">Ngày kết thúc</label>
                                    <input type="date" 
                                           class="form-control @error('period_end') is-invalid @enderror" 
                                           id="period_end" 
                                           name="period_end" 
                                           value="{{ old('period_end') }}">
                                    @error('period_end')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="description" class="form-label">Mô tả</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="4" 
                                      placeholder="Nhập mô tả cho ngân sách...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                Tạo Ngân sách
                            </button>
                            <a href="{{ route('budgets.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>
                                Hủy
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Format number input
    $('#total_budget').on('input', function() {
        let value = $(this).val().replace(/[^\d]/g, '');
        if (value) {
            $(this).val(parseInt(value));
        }
    });

    // Validate date range
    $('#period_start, #period_end').on('change', function() {
        let startDate = $('#period_start').val();
        let endDate = $('#period_end').val();
        
        if (startDate && endDate && startDate > endDate) {
            alert('Ngày bắt đầu không thể lớn hơn ngày kết thúc!');
            $('#period_end').val('');
        }
    });
});
</script>
@endpush
