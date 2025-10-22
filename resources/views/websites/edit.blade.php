@extends('layouts.app')

@section('title', 'Sửa Website')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('websites.index') }}">Quản lý Website</a></li>
    <li class="breadcrumb-item active">Sửa Website</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-edit me-2"></i>
                Sửa Website: {{ $website->name }}
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
                <form action="{{ route('websites.update', $website) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="name" class="form-label">Tên Website <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $website->name) }}" 
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="seoer_id" class="form-label">Seoer <span class="text-danger">*</span></label>
                            <select class="form-select @error('seoer_id') is-invalid @enderror" id="seoer_id" name="seoer_id" required>
                                <option value="">Chọn Seoer</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('seoer_id', $website->seoer_id) == $user->id ? 'selected' : '' }}>
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
                            @if($website->seoer && !$website->seoer_id)
                                <div class="form-text text-warning">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    Hiện tại: {{ $website->seoer }} (cần cập nhật)
                                </div>
                            @endif
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="status" class="form-label">Trạng thái <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="active" {{ old('status', $website->status) === 'active' ? 'selected' : '' }}>Hoạt động</option>
                                <option value="inactive" {{ old('status', $website->status) === 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
                                <option value="maintenance" {{ old('status', $website->status) === 'maintenance' ? 'selected' : '' }}>Bảo trì</option>
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
                                <option value="brand" {{ old('category', $website->category) === 'brand' ? 'selected' : '' }}>Brand</option>
                                <option value="phishing" {{ old('category', $website->category) === 'phishing' ? 'selected' : '' }}>Phishing</option>
                                <option value="key_nganh" {{ old('category', $website->category) === 'key_nganh' ? 'selected' : '' }}>Key ngành</option>
                                <option value="pbn" {{ old('category', $website->category) === 'pbn' ? 'selected' : '' }}>PBN</option>
                            </select>
                            @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    
                    <!-- 301 Redirect Section -->
                    <div class="card mb-3 border-info">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-external-link-alt me-2"></i>
                                Trạng thái 301 Redirect
                                <button type="button" class="btn btn-sm btn-light ms-2" onclick="check301Status()">
                                    <i class="fas fa-sync-alt me-1"></i>
                                    Kiểm tra lại
                                </button>
                            </h6>
                        </div>
                        <div class="card-body">
                            <!-- Current Status Display -->
                            <div id="current_301_status" class="alert alert-secondary">
                                <i class="fas fa-spinner fa-spin me-2"></i>
                                Đang kiểm tra trạng thái 301 redirect...
                            </div>
                            
                            <!-- Only IT and Admin can modify redirect settings -->
                            @if(auth()->user()->role && in_array(auth()->user()->role->name, ['it', 'admin']))
                            <div class="border-top pt-3 mt-3">
                                <h6 class="text-warning">
                                    <i class="fas fa-tools me-2"></i>
                                    Cài đặt 301 Redirect 
                                    <span class="badge bg-warning text-dark">IT & Admin</span>
                                </h6>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="has_301_redirect" name="has_301_redirect" value="1" {{ old('has_301_redirect', $website->has_301_redirect) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="has_301_redirect">
                                                <strong>Cập nhật 301 redirect</strong>
                                            </label>
                                        </div>
                                        <div class="form-text">Đánh dấu để tạo/cập nhật rule 301 redirect</div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3" id="redirect_domain_section" style="display: {{ old('has_301_redirect', $website->has_301_redirect) ? 'block' : 'none' }};">
                                        <label for="redirect_to_domain" class="form-label">Domain đích <span class="text-danger">*</span></label>
                                        <input type="url" 
                                               class="form-control @error('redirect_to_domain') is-invalid @enderror" 
                                               id="redirect_to_domain" 
                                               name="redirect_to_domain" 
                                               value="{{ old('redirect_to_domain', $website->redirect_to_domain) }}"
                                               placeholder="https://example.com">
                                        @error('redirect_to_domain')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Domain đầy đủ mà website này sẽ redirect đến</div>
                                    </div>
                                </div>
                            </div>
                            @else
                            <div class="alert alert-warning">
                                <i class="fas fa-lock me-2"></i>
                                Chỉ có IT và Admin mới có thể chỉnh sửa cài đặt 301 redirect.
                            </div>
                            @endif
                            
                            @if($website->cloudflare_zone_id)
                            <div class="row">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <h6 class="alert-heading">
                                            <i class="fas fa-cloud me-2"></i>
                                            Kiểm tra Cloudflare
                                        </h6>
                                        <p class="mb-2">
                                            <strong>Zone ID:</strong> {{ $website->cloudflare_zone_id }}
                                        </p>
                                        <button type="button" class="btn btn-sm btn-outline-info" onclick="checkCloudflareStatus()">
                                            <i class="fas fa-sync me-2"></i>
                                            Kiểm tra trạng thái 301
                                        </button>
                                        <div id="cloudflare-result" class="mt-2"></div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="row">
                        
                        <div class="col-md-6 mb-3">
                            <label for="delivery_date" class="form-label">Ngày giao web</label>
                            <input type="date" 
                                   class="form-control @error('delivery_date') is-invalid @enderror" 
                                   id="delivery_date" 
                                   name="delivery_date" 
                                   value="{{ old('delivery_date', $website->delivery_date?->format('Y-m-d')) }}">
                            @error('delivery_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="bot_open_date" class="form-label">Ngày mở bot</label>
                            <input type="date" 
                                   class="form-control @error('bot_open_date') is-invalid @enderror" 
                                   id="bot_open_date" 
                                   name="bot_open_date" 
                                   value="{{ old('bot_open_date', $website->bot_open_date?->format('Y-m-d')) }}">
                            @error('bot_open_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="purchase_date" class="form-label">Ngày mua web</label>
                            <input type="date" 
                                   class="form-control @error('purchase_date') is-invalid @enderror" 
                                   id="purchase_date" 
                                   name="purchase_date" 
                                   value="{{ old('purchase_date', $website->purchase_date?->format('Y-m-d')) }}">
                            @error('purchase_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="expiry_date" class="form-label">Ngày hết hạn</label>
                            <input type="date" 
                                   class="form-control @error('expiry_date') is-invalid @enderror" 
                                   id="expiry_date" 
                                   name="expiry_date" 
                                   value="{{ old('expiry_date', $website->expiry_date?->format('Y-m-d')) }}">
                            @error('expiry_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Ghi chú</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" 
                                  name="notes" 
                                  rows="4">{{ old('notes', $website->notes) }}</textarea>
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
                            Cập nhật
                        </button>
                    </div>
                </form>
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
    
    // Check 301 status on page load
    check301Status();
});

function check301Status() {
    const statusDiv = document.getElementById('current_301_status');
    
    statusDiv.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang kiểm tra trạng thái 301 redirect...';
    statusDiv.className = 'alert alert-secondary';
    
    fetch('{{ route("websites.check-301-status", $website) }}')
        .then(response => response.json())
        .then(data => {
            if (data.has_redirect) {
                statusDiv.innerHTML = `
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <div>
                            <strong>Website có 301 redirect</strong><br>
                            <small class="text-muted">Redirect đến: <a href="${data.redirect_to}" target="_blank">${data.redirect_to}</a></small>
                        </div>
                    </div>
                `;
                statusDiv.className = 'alert alert-success';
            } else {
                statusDiv.innerHTML = `
                    <div class="d-flex align-items-center">
                        <i class="fas fa-times-circle text-danger me-2"></i>
                        <div>
                            <strong>Website không có 301 redirect</strong><br>
                            <small class="text-muted">Không tìm thấy rule 301 redirect trên Cloudflare</small>
                        </div>
                    </div>
                `;
                statusDiv.className = 'alert alert-warning';
            }
            
            if (data.error) {
                statusDiv.innerHTML = `
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        <div>
                            <strong>Lỗi kiểm tra:</strong><br>
                            <small class="text-muted">${data.error}</small>
                        </div>
                    </div>
                `;
                statusDiv.className = 'alert alert-danger';
            }
        })
        .catch(error => {
            statusDiv.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                    <div>
                        <strong>Lỗi kết nối:</strong><br>
                        <small class="text-muted">Không thể kiểm tra trạng thái 301 redirect</small>
                    </div>
                </div>
            `;
            statusDiv.className = 'alert alert-danger';
        });
}
</script>
@endsection
