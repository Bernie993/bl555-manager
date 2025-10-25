@extends('layouts.app')

@section('title', 'Chỉnh sửa dịch vụ')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-edit mr-2"></i>
                        Chỉnh sửa dịch vụ: {{ $service->name }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('services.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left mr-1"></i>Quay lại
                        </a>
                    </div>
                </div>

                <form method="POST" action="{{ route('services.update', $service) }}">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <!-- Tên dịch vụ -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="required">Tên dịch vụ:</label>
                                    <input type="text" name="name" id="name" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           value="{{ old('name', $service->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Loại dịch vụ -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="type" class="required">Loại dịch vụ:</label>
                                    <select name="type" id="type" class="form-control @error('type') is-invalid @enderror" required>
                                        <option value="">Chọn loại dịch vụ</option>
                                        @foreach(\App\Models\Service::TYPES as $key => $value)
                                            <option value="{{ $key }}" {{ old('type', $service->type) == $key ? 'selected' : '' }}>
                                                {{ $value }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Website -->
                            <div class="col-md-6" id="website-field" style="display: {{ $service->type === 'textlink' ? 'block' : 'none' }};">
                                <div class="form-group">
                                    <label for="website" class="required">Website:</label>
                                    <input type="url" name="website" id="website" 
                                           class="form-control @error('website') is-invalid @enderror" 
                                           value="{{ old('website', $service->website) }}"
                                           placeholder="https://example.com">
                                    @error('website')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Giá dịch vụ -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="price" class="required">Giá dịch vụ (VNĐ):</label>
                                    <input type="number" name="price" id="price" 
                                           class="form-control @error('price') is-invalid @enderror" 
                                           value="{{ old('price', $service->price) }}" required min="0" step="1">
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- SEO Metrics -->
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="dr">Domain Rating (DR):</label>
                                    <input type="number" name="dr" id="dr" 
                                           class="form-control @error('dr') is-invalid @enderror" 
                                           value="{{ old('dr', $service->dr) }}" min="0" max="100">
                                    @error('dr')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="da">Domain Authority (DA):</label>
                                    <input type="number" name="da" id="da" 
                                           class="form-control @error('da') is-invalid @enderror" 
                                           value="{{ old('da', $service->da) }}" min="0" max="100">
                                    @error('da')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="pa">Page Authority (PA):</label>
                                    <input type="number" name="pa" id="pa" 
                                           class="form-control @error('pa') is-invalid @enderror" 
                                           value="{{ old('pa', $service->pa) }}" min="0" max="100">
                                    @error('pa')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="tf">Trust Flow (TF):</label>
                                    <input type="number" name="tf" id="tf" 
                                           class="form-control @error('tf') is-invalid @enderror" 
                                           value="{{ old('tf', $service->tf) }}" min="0" max="100">
                                    @error('tf')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- File Uploads -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="quote_file">File báo giá:</label>
                                    <input type="url" name="quote_file" id="quote_file" 
                                           class="form-control @error('quote_file') is-invalid @enderror" 
                                           value="{{ old('quote_file', $service->quote_file) }}" 
                                           placeholder="https://drive.google.com/file/...">
                                    <small class="form-text text-muted">Link đến file báo giá (Google Drive, Dropbox...)</small>
                                    @error('quote_file')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="demo_file">File demo:</label>
                                    <input type="url" name="demo_file" id="demo_file" 
                                           class="form-control @error('demo_file') is-invalid @enderror" 
                                           value="{{ old('demo_file', $service->demo_file) }}" 
                                           placeholder="https://drive.google.com/file/...">
                                    <small class="form-text text-muted">Link đến file demo (Google Drive, Dropbox...)</small>
                                    @error('demo_file')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Ref Domain -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="ref_domain">Ref domain:</label>
                                    <input type="url" name="ref_domain" id="ref_domain" 
                                           class="form-control @error('ref_domain') is-invalid @enderror" 
                                           value="{{ old('ref_domain', $service->ref_domain) }}" 
                                           placeholder="https://example.com">
                                    @error('ref_domain')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Traffic -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="traffic">Traffic:</label>
                                    <input type="text" name="traffic" id="traffic" 
                                           class="form-control @error('traffic') is-invalid @enderror" 
                                           value="{{ old('traffic', $service->traffic) }}" 
                                           placeholder="100K/month, 50K/day...">
                                    @error('traffic')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- IP Address -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="ip">IP Address:</label>
                                    <input type="text" name="ip" id="ip" 
                                           class="form-control @error('ip') is-invalid @enderror" 
                                           value="{{ old('ip', $service->ip) }}" placeholder="192.168.1.1">
                                    @error('ip')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Lĩnh vực -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="category">Lĩnh vực:</label>
                                    <input type="text" name="category" id="category" 
                                           class="form-control @error('category') is-invalid @enderror" 
                                           value="{{ old('category', $service->category) }}" placeholder="Công nghệ, Du lịch, Y tế...">
                                    @error('category')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Trạng thái -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div class="form-check">
                                        <input type="checkbox" name="is_active" id="is_active" 
                                               class="form-check-input" value="1" 
                                               {{ old('is_active', $service->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Kích hoạt dịch vụ
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Keywords -->
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="keywords">Keywords:</label>
                                    <textarea name="keywords" id="keywords" 
                                              class="form-control @error('keywords') is-invalid @enderror" 
                                              rows="2" placeholder="keyword1, keyword2, keyword3...">{{ old('keywords', $service->keywords_string) }}</textarea>
                                    <small class="form-text text-muted">Nhập các từ khóa, phân cách bằng dấu phẩy</small>
                                    @error('keywords')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Mô tả -->
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="description">Mô tả dịch vụ:</label>
                                    <textarea name="description" id="description" 
                                              class="form-control @error('description') is-invalid @enderror" 
                                              rows="4" placeholder="Mô tả chi tiết về dịch vụ...">{{ old('description', $service->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('services.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times mr-2"></i>Hủy
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i>Cập nhật dịch vụ
                            </button>
                        </div>
                    </div>
                </form>
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
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const websiteField = document.getElementById('website-field');
    const websiteInput = document.getElementById('website');
    
    function toggleWebsiteField() {
        if (typeSelect.value === 'textlink') {
            websiteField.style.display = 'block';
            websiteInput.required = true;
        } else {
            websiteField.style.display = 'none';
            websiteInput.required = false;
            websiteInput.value = ''; // Clear value when hidden
        }
    }
    
    // Initial check for old values
    toggleWebsiteField();
    
    // Listen for changes
    typeSelect.addEventListener('change', toggleWebsiteField);
});
</script>
@endpush
