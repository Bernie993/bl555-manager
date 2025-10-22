@extends('layouts.app')

@section('title', 'Thêm Người dùng')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Quản lý Người dùng</a></li>
    <li class="breadcrumb-item active">Thêm Người dùng</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-plus me-2"></i>
                Thêm Người dùng
            </h1>
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
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
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Tên người dùng <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
                                   required
                                   placeholder="Nhập tên người dùng">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   required
                                   placeholder="Nhập email">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   required
                                   placeholder="Nhập mật khẩu">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                            <input type="password" 
                                   class="form-control" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   required
                                   placeholder="Nhập lại mật khẩu">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="role_id" class="form-label">Vai trò <span class="text-danger">*</span></label>
                            <select class="form-select @error('role_id') is-invalid @enderror" id="role_id" name="role_id" required>
                                <option value="">Chọn vai trò</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                        {{ $role->display_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Trạng thái</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Kích hoạt tài khoản
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Số điện thoại</label>
                            <input type="text" 
                                   class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" 
                                   name="phone" 
                                   value="{{ old('phone') }}" 
                                   placeholder="Nhập số điện thoại">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="telegram" class="form-label">Telegram</label>
                            <input type="text" 
                                   class="form-control @error('telegram') is-invalid @enderror" 
                                   id="telegram" 
                                   name="telegram" 
                                   value="{{ old('telegram') }}" 
                                   placeholder="@username hoặc link Telegram">
                            @error('telegram')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Employment Dates Section -->
                    <div class="card mb-3" id="employment-section">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-calendar-alt me-2"></i>
                                <span id="section-title">Thông tin công việc</span>
                            </h6>
                        </div>
                        <div class="card-body">
                            <!-- Employment dates for non-partner roles -->
                            <div id="employment-dates">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="hire_date" class="form-label">Ngày nhận việc</label>
                                        <input type="date" 
                                               class="form-control @error('hire_date') is-invalid @enderror" 
                                               id="hire_date" 
                                               name="hire_date" 
                                               value="{{ old('hire_date') }}">
                                        @error('hire_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Ngày bắt đầu làm việc</div>
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <label for="permanent_date" class="form-label">Ngày chuyển chính</label>
                                        <input type="date" 
                                               class="form-control @error('permanent_date') is-invalid @enderror" 
                                               id="permanent_date" 
                                               name="permanent_date" 
                                               value="{{ old('permanent_date') }}">
                                        @error('permanent_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Ngày chuyển thành nhân viên chính thức</div>
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <label for="resignation_date" class="form-label">Ngày nghỉ việc</label>
                                        <input type="date" 
                                               class="form-control @error('resignation_date') is-invalid @enderror" 
                                               id="resignation_date" 
                                               name="resignation_date" 
                                               value="{{ old('resignation_date') }}">
                                        @error('resignation_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Ngày kết thúc làm việc (nếu có)</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Payment info for partner role -->
                            <div id="payment-info" style="display: none;">
                                <div class="mb-3">
                                    <label for="payment_info" class="form-label">Thông tin nhận thanh toán</label>
                                    <textarea class="form-control @error('payment_info') is-invalid @enderror" 
                                              id="payment_info" 
                                              name="payment_info" 
                                              rows="4"
                                              placeholder="Nhập thông tin tài khoản ngân hàng, ví điện tử hoặc thông tin thanh toán khác...">{{ old('payment_info') }}</textarea>
                                    @error('payment_info')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Thông tin chi tiết về cách thức nhận thanh toán</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>
                            Hủy
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            Tạo Người dùng
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
                    <i class="fas fa-info-circle me-2"></i>
                    Hướng dẫn
                </h6>
            </div>
            <div class="card-body">
                <div class="small">
                    <p><strong>Các trường bắt buộc:</strong></p>
                    <ul class="mb-3">
                        <li>Tên người dùng</li>
                        <li>Email (phải duy nhất)</li>
                        <li>Mật khẩu (tối thiểu 8 ký tự)</li>
                        <li>Vai trò</li>
                    </ul>
                    
                    <p><strong>Vai trò:</strong></p>
                    <ul class="mb-3">
                        @foreach($roles as $role)
                        <li><strong>{{ $role->display_name }}:</strong> {{ $role->description }}</li>
                        @endforeach
                    </ul>
                    
                    <p><strong>Lưu ý:</strong></p>
                    <ul>
                        <li>Email phải là duy nhất trong hệ thống</li>
                        <li>Mật khẩu sẽ được mã hóa tự động</li>
                        <li>Tài khoản mặc định được kích hoạt</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-key me-2"></i>
                    Quyền hạn theo vai trò
                </h6>
            </div>
            <div class="card-body">
                <div class="small" id="role-permissions">
                    <p class="text-muted">Chọn vai trò để xem quyền hạn</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role_id');
    const permissionsDiv = document.getElementById('role-permissions');
    const sectionTitle = document.getElementById('section-title');
    const employmentDates = document.getElementById('employment-dates');
    const paymentInfo = document.getElementById('payment-info');
    
    const rolePermissions = {
        @foreach($roles as $role)
        {{ $role->id }}: {
            name: '{{ $role->display_name }}',
            roleName: '{{ $role->name }}',
            permissions: [
                @foreach($role->permissions as $permission)
                '{{ $permission->display_name }}',
                @endforeach
            ]
        },
        @endforeach
    };
    
    function updateFormSections() {
        const roleId = roleSelect.value;
        
        if (roleId && rolePermissions[roleId]) {
            const role = rolePermissions[roleId];
            
            // Update permissions display
            let html = `<p><strong>${role.name}:</strong></p><ul class="mb-0">`;
            
            if (role.permissions.length > 0) {
                role.permissions.forEach(permission => {
                    html += `<li><i class="fas fa-check text-success me-2"></i>${permission}</li>`;
                });
            } else {
                html += '<li class="text-muted">Không có quyền nào</li>';
            }
            
            html += '</ul>';
            permissionsDiv.innerHTML = html;
            
            // Update form sections based on role
            if (role.roleName === 'partner') {
                sectionTitle.textContent = 'Thông tin nhận thanh toán';
                employmentDates.style.display = 'none';
                paymentInfo.style.display = 'block';
            } else {
                sectionTitle.textContent = 'Thông tin công việc';
                employmentDates.style.display = 'block';
                paymentInfo.style.display = 'none';
            }
        } else {
            permissionsDiv.innerHTML = '<p class="text-muted">Chọn vai trò để xem quyền hạn</p>';
            sectionTitle.textContent = 'Thông tin công việc';
            employmentDates.style.display = 'block';
            paymentInfo.style.display = 'none';
        }
    }
    
    roleSelect.addEventListener('change', updateFormSections);
    
    // Initialize form sections on page load
    updateFormSections();
});
</script>
@endsection
