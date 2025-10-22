@extends('layouts.app')

@section('title', 'Đồng bộ Domain từ Cloudflare')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>
                        <i class="fab fa-cloudflare me-2"></i>
                        Đồng bộ Domain từ Cloudflare
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6>Thông tin đồng bộ</h6>
                        <ul>
                            <li>Tính năng này sẽ lấy tất cả domain từ tài khoản Cloudflare của bạn</li>
                            <li>Tự động kiểm tra trạng thái 301 redirect cho từng domain</li>
                            <li>Tạo mới hoặc cập nhật thông tin website trong hệ thống</li>
                            <li>Chỉ Admin và IT mới có quyền thực hiện đồng bộ</li>
                        </ul>
                    </div>

                    <div class="text-center">
                        <button type="button" class="btn btn-primary btn-lg" id="syncButton">
                            <i class="fab fa-cloudflare me-2"></i>
                            Bắt đầu đồng bộ
                        </button>
                    </div>

                    <div id="syncResults" class="mt-4" style="display: none;">
                        <div class="card">
                            <div class="card-header">
                                <h6>Kết quả đồng bộ</h6>
                            </div>
                            <div class="card-body">
                                <div id="syncContent">
                                    <!-- Results will be shown here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('syncButton').addEventListener('click', function() {
    const button = this;
    const resultsDiv = document.getElementById('syncResults');
    const contentDiv = document.getElementById('syncContent');
    
    // Show loading
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang đồng bộ...';
    resultsDiv.style.display = 'block';
    contentDiv.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><br>Đang xử lý...</div>';
    
    // Make API call
    fetch('{{ route("websites.sync-from-cloudflare") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            contentDiv.innerHTML = `
                <div class="alert alert-success">
                    <h6>Đồng bộ thành công!</h6>
                    <p>Domain mới: ${data.data.new_domains}</p>
                    <p>Domain cập nhật: ${data.data.updated_domains}</p>
                    <p>Tổng domain Cloudflare: ${data.data.total_cf_domains}</p>
                </div>
            `;
        } else {
            contentDiv.innerHTML = `
                <div class="alert alert-danger">
                    <h6>Lỗi đồng bộ</h6>
                    <p>${data.error || 'Có lỗi xảy ra'}</p>
                </div>
            `;
        }
    })
    .catch(error => {
        contentDiv.innerHTML = `
            <div class="alert alert-danger">
                <h6>Lỗi kết nối</h6>
                <p>${error.message}</p>
            </div>
        `;
    })
    .finally(() => {
        button.disabled = false;
        button.innerHTML = '<i class="fab fa-cloudflare me-2"></i>Thử lại đồng bộ';
    });
});
</script>
@endsection
