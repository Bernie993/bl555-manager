@extends('layouts.app')

@section('title', 'Thêm Website')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('websites.index') }}">Quản lý Website</a></li>
    <li class="breadcrumb-item active">Thêm Website</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-plus me-2"></i>
                Thêm Website
            </h1>
            <a href="{{ route('websites.index') }}" class="btn btn-outline-secondary">
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
                <form action="{{ route('websites.store') }}" method="POST">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="name" class="form-label">Tên Website <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
                                   required
                                   placeholder="Nhập tên website">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="seoer_id" class="form-label">Seoer <span class="text-danger">*</span></label>
                            <select class="form-select @error('seoer_id') is-invalid @enderror" id="seoer_id" name="seoer_id" required>
                                <option value="">Chọn Seoer</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('seoer_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                        @if($user->role)
                                            ({{ $user->role->display_name }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('seoer_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="status" class="form-label">Trạng thái <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="">Chọn trạng thái</option>
                                <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Hoạt động</option>
                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
                                <option value="maintenance" {{ old('status') === 'maintenance' ? 'selected' : '' }}>Bảo trì</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="category" class="form-label">Phân loại</label>
                            <select class="form-select @error('category') is-invalid @enderror" id="category" name="category">
                                <option value="">Chọn phân loại</option>
                                <option value="brand" {{ old('category') === 'brand' ? 'selected' : '' }}>Brand</option>
                                <option value="phishing" {{ old('category') === 'phishing' ? 'selected' : '' }}>Phishing</option>
                                <option value="key_nganh" {{ old('category') === 'key_nganh' ? 'selected' : '' }}>Key ngành</option>
                                <option value="pbn" {{ old('category') === 'pbn' ? 'selected' : '' }}>PBN</option>
                            </select>
                            @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- 301 Redirect Section - Only for IT and Admin -->
                    @if(auth()->user()->role && in_array(auth()->user()->role->name, ['it', 'admin']))
                    <div class="card mb-3 border-info">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-external-link-alt me-2"></i>
                                Cài đặt 301 Redirect
                                <span class="badge bg-warning text-dark ms-2">IT & Admin</span>
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Trạng thái 301 redirect sẽ được tự động kiểm tra qua Cloudflare API sau khi tạo website.
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="has_301_redirect" name="has_301_redirect" value="1" {{ old('has_301_redirect') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="has_301_redirect">
                                            <strong>Tạo 301 redirect ngay</strong>
                                        </label>
                                    </div>
                                    <div class="form-text">Đánh dấu để tạo rule 301 redirect qua Cloudflare</div>
                                </div>
                                
                                <div class="col-md-6 mb-3" id="redirect_domain_section" style="display: none;">
                                    <label for="redirect_to_domain" class="form-label">Domain đích <span class="text-danger">*</span></label>
                                    <input type="url" 
                                           class="form-control @error('redirect_to_domain') is-invalid @enderror" 
                                           id="redirect_to_domain" 
                                           name="redirect_to_domain" 
                                           value="{{ old('redirect_to_domain') }}"
                                           placeholder="https://example.com">
                                    @error('redirect_to_domain')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Domain đầy đủ mà website này sẽ redirect đến</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Trạng thái 301 redirect sẽ được tự động kiểm tra qua Cloudflare API sau khi tạo website.
                    </div>
                    @endif
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="delivery_date" class="form-label">Ngày giao web</label>
                            <input type="date" 
                                   class="form-control @error('delivery_date') is-invalid @enderror" 
                                   id="delivery_date" 
                                   name="delivery_date" 
                                   value="{{ old('delivery_date') }}">
                            @error('delivery_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="purchase_date" class="form-label">Ngày mua web</label>
                            <input type="date" 
                                   class="form-control @error('purchase_date') is-invalid @enderror" 
                                   id="purchase_date" 
                                   name="purchase_date" 
                                   value="{{ old('purchase_date') }}">
                            @error('purchase_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="expiry_date" class="form-label">Ngày hết hạn</label>
                            <input type="date" 
                                   class="form-control @error('expiry_date') is-invalid @enderror" 
                                   id="expiry_date" 
                                   name="expiry_date" 
                                   value="{{ old('expiry_date') }}">
                            @error('expiry_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="bot_open_date" class="form-label">Ngày mở bot</label>
                            <input type="date" 
                                   class="form-control @error('bot_open_date') is-invalid @enderror" 
                                   id="bot_open_date" 
                                   name="bot_open_date" 
                                   value="{{ old('bot_open_date') }}">
                            @error('bot_open_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Ghi chú</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" 
                                  name="notes" 
                                  rows="4"
                                  placeholder="Nhập ghi chú (tùy chọn)">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('websites.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>
                            Hủy
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            Lưu Website
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </div>
    
    <div class="col-lg-4">
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
                        <li>Tên Website</li>
                        <li>Seoer</li>
                        <li>Trạng thái</li>
                    </ul>
                    
                    <p><strong>Trạng thái:</strong></p>
                    <ul class="mb-3">
                        <li><span class="badge bg-success">Hoạt động</span> - Website đang hoạt động bình thường</li>
                        <li><span class="badge bg-danger">Không hoạt động</span> - Website tạm dừng hoạt động</li>
                        <li><span class="badge bg-warning">Bảo trì</span> - Website đang trong quá trình bảo trì</li>
                    </ul>
                    
                    <p><strong>301 Redirect:</strong></p>
                    <ul class="mb-3">
                        <li>Đánh dấu nếu website có rule 301 redirect</li>
                        <li>Cần Cloudflare Zone ID để kiểm tra tự động</li>
                        <li>Domain đích sẽ được hiển thị trong danh sách</li>
                    </ul>
                    
                    <p><strong>Lưu ý:</strong></p>
                    <ul>
                        <li>Hệ thống sẽ tự động cảnh báo khi website sắp hết hạn</li>
                        <li>Ghi chú có thể được sử dụng để lưu thông tin bổ sung</li>
                        <li>Chọn Seoer từ danh sách người dùng đang hoạt động</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const has301Checkbox = document.getElementById('has_301_redirect');
    const redirectDomainSection = document.getElementById('redirect_domain_section');
    
    function toggleRedirectDomain() {
        if (has301Checkbox.checked) {
            redirectDomainSection.style.display = 'block';
        } else {
            redirectDomainSection.style.display = 'none';
            document.getElementById('redirect_to_domain').value = '';
        }
    }
    
    has301Checkbox.addEventListener('change', toggleRedirectDomain);
    
    // Initial check
    toggleRedirectDomain();
});
</script>
@endsection
