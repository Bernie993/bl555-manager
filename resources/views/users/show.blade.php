@extends('layouts.app')

@section('title', 'Chi tiết Người dùng')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Quản lý Người dùng</a></li>
    <li class="breadcrumb-item active">Chi tiết Người dùng</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-user me-2"></i>
                Chi tiết Người dùng: {{ $user->name }}
            </h1>
            <div class="btn-group">
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>
                    Quay lại
                </a>
                @if(auth()->user()->hasPermission('users.update'))
                <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">
                    <i class="fas fa-edit me-2"></i>
                    Chỉnh sửa
                </a>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Thông tin Người dùng
                </h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Tên:</strong>
                    </div>
                    <div class="col-sm-9">
                        {{ $user->name }}
                        @if($user->id === auth()->id())
                            <span class="badge bg-info ms-2">Bạn</span>
                        @endif
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Email:</strong>
                    </div>
                    <div class="col-sm-9">
                        {{ $user->email }}
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Vai trò:</strong>
                    </div>
                    <div class="col-sm-9">
                        @if($user->role)
                            <span class="badge bg-primary fs-6">{{ $user->role->display_name }}</span>
                            <div class="mt-2 text-muted small">{{ $user->role->description }}</div>
                        @else
                            <span class="badge bg-secondary">Chưa có vai trò</span>
                        @endif
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Trạng thái:</strong>
                    </div>
                    <div class="col-sm-9">
                        <span class="badge bg-{{ $user->is_active ? 'success' : 'danger' }} fs-6">
                            {{ $user->is_active ? 'Hoạt động' : 'Vô hiệu hóa' }}
                        </span>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Ngày tạo:</strong>
                    </div>
                    <div class="col-sm-9">
                        {{ $user->created_at->format('d/m/Y H:i') }}
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Cập nhật lần cuối:</strong>
                    </div>
                    <div class="col-sm-9">
                        {{ $user->updated_at->format('d/m/Y H:i') }}
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Employment Information Card -->
        @if($user->hire_date || $user->permanent_date || $user->resignation_date)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-alt me-2"></i>
                    Thông tin Công việc
                </h5>
            </div>
            <div class="card-body">
                @if($user->hire_date)
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Ngày nhận việc:</strong>
                    </div>
                    <div class="col-sm-9">
                        <span class="badge bg-info fs-6">{{ $user->hire_date->format('d/m/Y') }}</span>
                        <div class="mt-1 text-muted small">
                            <i class="fas fa-clock me-1"></i>
                            {{ $user->hire_date->diffForHumans() }}
                        </div>
                    </div>
                </div>
                @endif
                
                @if($user->permanent_date)
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Ngày chuyển chính:</strong>
                    </div>
                    <div class="col-sm-9">
                        <span class="badge bg-success fs-6">{{ $user->permanent_date->format('d/m/Y') }}</span>
                        <div class="mt-1 text-muted small">
                            <i class="fas fa-clock me-1"></i>
                            {{ $user->permanent_date->diffForHumans() }}
                        </div>
                    </div>
                </div>
                @endif
                
                @if($user->resignation_date)
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Ngày nghỉ việc:</strong>
                    </div>
                    <div class="col-sm-9">
                        <span class="badge bg-danger fs-6">{{ $user->resignation_date->format('d/m/Y') }}</span>
                        <div class="mt-1 text-muted small">
                            <i class="fas fa-clock me-1"></i>
                            {{ $user->resignation_date->diffForHumans() }}
                        </div>
                    </div>
                </div>
                @endif
                
                <!-- Work Duration Calculation -->
                @if($user->hire_date)
                <div class="row">
                    <div class="col-sm-3">
                        <strong>Thời gian làm việc:</strong>
                    </div>
                    <div class="col-sm-9">
                        @php
                            $endDate = $user->resignation_date ?? now();
                            $workDuration = $user->hire_date->diff($endDate);
                            $years = $workDuration->y;
                            $months = $workDuration->m;
                            $days = $workDuration->d;
                        @endphp
                        
                        <div class="d-flex flex-wrap gap-2">
                            @if($years > 0)
                                <span class="badge bg-primary">{{ $years }} năm</span>
                            @endif
                            @if($months > 0)
                                <span class="badge bg-primary">{{ $months }} tháng</span>
                            @endif
                            @if($days > 0 && $years == 0)
                                <span class="badge bg-primary">{{ $days }} ngày</span>
                            @endif
                        </div>
                        
                        @if($user->resignation_date)
                            <div class="mt-1 text-danger small">
                                <i class="fas fa-info-circle me-1"></i>
                                Đã nghỉ việc
                            </div>
                        @else
                            <div class="mt-1 text-success small">
                                <i class="fas fa-check-circle me-1"></i>
                                Đang làm việc
                            </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif
        
        @if($user->role && $user->role->permissions->count() > 0)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-key me-2"></i>
                    Quyền hạn
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @php
                        $groupedPermissions = $user->role->permissions->groupBy('module');
                    @endphp
                    @foreach($groupedPermissions as $module => $permissions)
                    <div class="col-md-4 mb-3">
                        <h6 class="text-primary">
                            @switch($module)
                                @case('websites')
                                    <i class="fas fa-globe me-2"></i>Website
                                    @break
                                @case('budgets')
                                    <i class="fas fa-wallet me-2"></i>Ngân sách
                                    @break
                                @case('users')
                                    <i class="fas fa-users me-2"></i>Người dùng
                                    @break
                                @default
                                    {{ ucfirst($module) }}
                            @endswitch
                        </h6>
                        <ul class="list-unstyled ms-3">
                            @foreach($permissions as $permission)
                            <li class="mb-1">
                                <i class="fas fa-check text-success me-2"></i>
                                {{ $permission->display_name }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-user-circle me-2"></i>
                    Avatar
                </h6>
            </div>
            <div class="card-body text-center">
                <div class="avatar-large mb-3">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <h5>{{ $user->name }}</h5>
                <p class="text-muted">{{ $user->email }}</p>
            </div>
        </div>
        
        @if(auth()->user()->hasPermission('users.update') || (auth()->user()->hasPermission('users.delete') && $user->id !== auth()->id()))
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-cogs me-2"></i>
                    Thao tác
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if(auth()->user()->hasPermission('users.update'))
                    <a href="{{ route('users.edit', $user) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-2"></i>
                        Chỉnh sửa
                    </a>
                    @endif
                    
                    @if(auth()->user()->hasPermission('users.delete') && $user->id !== auth()->id())
                    <form action="{{ route('users.destroy', $user) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100 btn-delete">
                            <i class="fas fa-trash me-2"></i>
                            Xóa Người dùng
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<style>
.avatar-large {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 24px;
    margin: 0 auto;
}
</style>
@endsection
