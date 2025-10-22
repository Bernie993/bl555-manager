@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Sửa lệnh 301 #{{ $redirect301->id }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('redirects-301.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>

                <form action="{{ route('redirects-301.update', $redirect301) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="form-group">
                            <label for="domain_list">Danh sách domain cần chuyển hướng:</label>
                            <textarea 
                                class="form-control @error('domain_list') is-invalid @enderror" 
                                id="domain_list" 
                                name="domain_list" 
                                rows="5" 
                                placeholder="Dán danh sách domain, mỗi domain trên một dòng hoặc phân tách bằng dấu cách, dấu phẩy..."
                                required>{{ old('domain_list', $redirect301->domain_list) }}</textarea>
                            @error('domain_list')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="target_url">URL cần chuyển 301:</label>
                            <input 
                                type="url" 
                                class="form-control @error('target_url') is-invalid @enderror" 
                                id="target_url" 
                                name="target_url" 
                                placeholder="Nhập URL đích (ví dụ: https://example.com)"
                                value="{{ old('target_url', $redirect301->target_url) }}"
                                required>
                            @error('target_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input 
                                    type="checkbox" 
                                    class="custom-control-input" 
                                    id="include_www" 
                                    name="include_www" 
                                    value="1"
                                    {{ old('include_www', $redirect301->include_www) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="include_www">
                                    Bao gồm www trong 301
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input 
                                    type="checkbox" 
                                    class="custom-control-input" 
                                    id="is_active" 
                                    name="is_active" 
                                    value="1"
                                    {{ old('is_active', $redirect301->is_active) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">
                                    Kích hoạt redirect
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                Bỏ tick để tạm thời vô hiệu hóa redirect này
                            </small>
                        </div>

                        @if($redirect301->cloudflare_rules)
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle"></i> Rules hiện tại:</h6>
                                <ul class="mb-0">
                                    @foreach($redirect301->cloudflare_rules as $rule)
                                        <li>
                                            <strong>{{ $rule['url_pattern'] }}</strong> 
                                            → {{ $rule['target_url'] }} 
                                            <span class="badge badge-info">{{ $rule['status_code'] }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Cảnh báo:</strong> Việc thay đổi thông tin sẽ cập nhật lại các Page Rules trên Cloudflare
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Cập nhật
                        </button>
                        <a href="{{ route('redirects-301.index') }}" class="btn btn-secondary">
                            Hủy
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-format domain list
    $('#domain_list').on('blur', function() {
        let domains = $(this).val().trim();
        if (domains) {
            domains = domains.split(/[\n,\s]+/)
                .map(domain => domain.trim())
                .filter(domain => domain.length > 0)
                .join('\n');
            $(this).val(domains);
        }
    });

    // Validate URL format
    $('#target_url').on('blur', function() {
        let url = $(this).val().trim();
        if (url && !url.startsWith('http://') && !url.startsWith('https://')) {
            $(this).val('https://' + url);
        }
    });
});
</script>
@endpush
@endsection
