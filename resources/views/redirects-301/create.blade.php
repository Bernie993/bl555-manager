@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Lập lệnh 301</h3>
                    <div class="card-tools">
                        <a href="{{ route('redirects-301.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>

                <form action="{{ route('redirects-301.store') }}" method="POST">
                    @csrf
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
                                required>{{ old('domain_list') }}</textarea>
                            @error('domain_list')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Nhập mỗi domain trên một dòng. Ví dụ:<br>
                                example1.com<br>
                                example2.com<br>
                                subdomain.example3.com
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="target_url">URL cần chuyển 301:</label>
                            <input 
                                type="url" 
                                class="form-control @error('target_url') is-invalid @enderror" 
                                id="target_url" 
                                name="target_url" 
                                placeholder="Nhập URL đích (ví dụ: https://example.com)"
                                value="{{ old('target_url') }}"
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
                                    {{ old('include_www') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="include_www">
                                    Bao gồm www trong 301
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                Nếu chọn, hệ thống sẽ tạo thêm rule redirect cho phiên bản www của mỗi domain
                            </small>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Lưu ý:</strong> 
                            <ul class="mb-0 mt-2">
                                <li>Hệ thống sẽ tự động tạo 2 Page Rules trên Cloudflare cho mỗi domain</li>
                                <li>Rule 1: Redirect domain chính (ví dụ: example.com/* → target.com/$1)</li>
                                <li>Rule 2: Redirect phiên bản www nếu được chọn (ví dụ: www.example.com/* → target.com/$1)</li>
                                <li>Tất cả các redirect sẽ sử dụng status code 301 (Permanent Redirect)</li>
                            </ul>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save
                        </button>
                        <a href="{{ route('redirects-301.index') }}" class="btn btn-secondary">
                            Cancel
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
            // Split by various delimiters and clean up
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

    // Preview rules
    function updatePreview() {
        const domainList = $('#domain_list').val().trim();
        const targetUrl = $('#target_url').val().trim();
        const includeWww = $('#include_www').is(':checked');
        
        if (domainList && targetUrl) {
            const domains = domainList.split('\n')
                .map(domain => domain.trim())
                .filter(domain => domain.length > 0);
            
            let previewHtml = '<h6>Preview Rules sẽ được tạo:</h6><ul>';
            
            domains.forEach((domain, index) => {
                if (index < 3) { // Show max 3 examples
                    previewHtml += `<li><strong>${domain}/*</strong> → ${targetUrl}/$1</li>`;
                    if (includeWww) {
                        const wwwDomain = domain.startsWith('www.') ? domain : 'www.' + domain;
                        previewHtml += `<li><strong>${wwwDomain}/*</strong> → ${targetUrl}/$1</li>`;
                    }
                }
            });
            
            if (domains.length > 3) {
                previewHtml += `<li class="text-muted">... và ${(domains.length - 3) * (includeWww ? 2 : 1)} rule khác</li>`;
            }
            
            previewHtml += '</ul>';
            
            if ($('#rules-preview').length === 0) {
                $('.alert-info').after(`<div id="rules-preview" class="alert alert-secondary">${previewHtml}</div>`);
            } else {
                $('#rules-preview').html(previewHtml);
            }
        } else {
            $('#rules-preview').remove();
        }
    }
    
    $('#domain_list, #target_url, #include_www').on('input change', updatePreview);
});
</script>
@endpush
@endsection
