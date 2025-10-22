@extends('layouts.app')

@section('title', 'Thông tin cá nhân')

@section('breadcrumb')
    <li class="breadcrumb-item active">Thông tin cá nhân</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-user-circle me-2"></i>
                Thông tin cá nhân
            </h1>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Tên người dùng <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $user->name) }}" 
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
                                   value="{{ old('email', $user->email) }}" 
                                   required
                                   placeholder="Nhập email">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Mật khẩu mới</label>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password"
                                   placeholder="Để trống nếu không đổi mật khẩu">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Để trống nếu không muốn thay đổi mật khẩu</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label">Xác nhận mật khẩu mới</label>
                            <input type="password" 
                                   class="form-control" 
                                   id="password_confirmation" 
                                   name="password_confirmation"
                                   placeholder="Nhập lại mật khẩu mới">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Số điện thoại</label>
                            <input type="text" 
                                   class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" 
                                   name="phone" 
                                   value="{{ old('phone', $user->phone) }}" 
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
                                   value="{{ old('telegram', $user->telegram) }}" 
                                   placeholder="@username hoặc link Telegram">
                            @error('telegram')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    @if($user->role && $user->role->name === 'partner')
                    <!-- Payment info for partner role -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-credit-card me-2"></i>
                                Thông tin nhận thanh toán
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="payment_info" class="form-label">Thông tin nhận thanh toán</label>
                                <textarea class="form-control @error('payment_info') is-invalid @enderror" 
                                          id="payment_info" 
                                          name="payment_info" 
                                          rows="4"
                                          placeholder="Nhập thông tin tài khoản ngân hàng, ví điện tử hoặc thông tin thanh toán khác...">{{ old('payment_info', $user->payment_info) }}</textarea>
                                @error('payment_info')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Thông tin chi tiết về cách thức nhận thanh toán</div>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>
                            Hủy
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            Cập nhật thông tin
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
                    Thông tin hiện tại
                </h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="avatar-large mb-3">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                    <h6>{{ $user->name }}</h6>
                    <p class="text-muted small">{{ $user->email }}</p>
                    <span class="badge bg-{{ $user->is_active ? 'success' : 'danger' }}">
                        {{ $user->is_active ? 'Hoạt động' : 'Vô hiệu hóa' }}
                    </span>
                </div>
                
                <div class="small">
                    <div class="row mb-2">
                        <div class="col-6">
                            <strong>Vai trò:</strong>
                        </div>
                        <div class="col-6">
                            {{ $user->role ? $user->role->display_name : 'Chưa có' }}
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6">
                            <strong>Tạo lúc:</strong>
                        </div>
                        <div class="col-6">
                            {{ $user->created_at->format('d/m/Y') }}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <strong>Cập nhật:</strong>
                        </div>
                        <div class="col-6">
                            {{ $user->updated_at->format('d/m/Y') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-key me-2"></i>
                    Quyền hạn của bạn
                </h6>
            </div>
            <div class="card-body">
                @if($user->role && $user->role->permissions->count() > 0)
                    <div class="small">
                        <p><strong>{{ $user->role->display_name }}:</strong></p>
                        <ul class="mb-0">
                            @foreach($user->role->permissions as $permission)
                            <li class="mb-1">
                                <i class="fas fa-check text-success me-2"></i>
                                {{ $permission->display_name }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <p class="text-muted small">Chưa có quyền nào</p>
                @endif
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Lưu ý
                </h6>
            </div>
            <div class="card-body">
                <div class="small">
                    <ul class="mb-0">
                        <li>Email phải là duy nhất trong hệ thống</li>
                        <li>Để trống mật khẩu nếu không muốn thay đổi</li>
                        <li>Bạn không thể thay đổi vai trò của mình</li>
                        <li>Thông tin cá nhân sẽ được lưu trữ an toàn</li>
                        @if($user->role && $user->role->name === 'partner')
                        <li>Thông tin thanh toán chỉ được xem bởi quản trị viên</li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<style>
.avatar-large {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 18px;
    margin: 0 auto;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password confirmation validation
    const password = document.getElementById('password');
    const passwordConfirmation = document.getElementById('password_confirmation');
    
    function validatePasswordMatch() {
        if (password.value && passwordConfirmation.value) {
            if (password.value !== passwordConfirmation.value) {
                passwordConfirmation.setCustomValidity('Mật khẩu xác nhận không khớp');
            } else {
                passwordConfirmation.setCustomValidity('');
            }
        } else {
            passwordConfirmation.setCustomValidity('');
        }
    }
    
    password.addEventListener('input', validatePasswordMatch);
    passwordConfirmation.addEventListener('input', validatePasswordMatch);
});
</script>
@endsection
