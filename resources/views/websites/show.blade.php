@extends('layouts.app')

@section('title', 'Chi tiết Website')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('websites.index') }}">Quản lý Website</a></li>
    <li class="breadcrumb-item active">Chi tiết Website</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-eye me-2"></i>
                Chi tiết Website: {{ $website->name }}
            </h1>
            <div class="btn-group">
                <a href="{{ route('websites.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>
                    Quay lại
                </a>
                @if(auth()->user()->hasPermission('websites.update'))
                <a href="{{ route('websites.edit', $website) }}" class="btn btn-primary">
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
                    Thông tin Website
                </h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Tên Website:</strong>
                    </div>
                    <div class="col-sm-9">
                        {{ $website->name }}
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Seoer:</strong>
                    </div>
                    <div class="col-sm-9">
                        {{ $website->seoer }}
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Trạng thái:</strong>
                    </div>
                    <div class="col-sm-9">
                        <span class="badge bg-{{ $website->status === 'active' ? 'success' : ($website->status === 'inactive' ? 'danger' : 'warning') }}">
                            {{ $website->getStatusDisplayName() }}
                        </span>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Phân loại:</strong>
                    </div>
                    <div class="col-sm-9">
                        <span class="badge {{ $website->getCategoryBadgeClass() }}">
                            {{ $website->getCategoryDisplayName() }}
                        </span>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Ngày giao web:</strong>
                    </div>
                    <div class="col-sm-9">
                        {{ $website->delivery_date ? $website->delivery_date->format('d/m/Y') : 'Chưa có thông tin' }}
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Ngày mua web:</strong>
                    </div>
                    <div class="col-sm-9">
                        {{ $website->purchase_date ? $website->purchase_date->format('d/m/Y') : 'Chưa có thông tin' }}
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Ngày hết hạn:</strong>
                    </div>
                    <div class="col-sm-9">
                        @if($website->expiry_date)
                            {{ $website->expiry_date->format('d/m/Y') }}
                            @if($website->expiry_date->isPast())
                                <span class="badge bg-danger ms-2">Đã hết hạn</span>
                            @elseif($website->expiry_date->diffInDays() <= 30)
                                <span class="badge bg-warning ms-2">Sắp hết hạn ({{ $website->expiry_date->diffInDays() }} ngày)</span>
                            @endif
                        @else
                            Chưa có thông tin
                        @endif
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Ngày mở bot:</strong>
                    </div>
                    <div class="col-sm-9">
                        {{ $website->bot_open_date ? $website->bot_open_date->format('d/m/Y') : 'Chưa có thông tin' }}
                    </div>
                </div>
                
                @if($website->notes)
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Ghi chú:</strong>
                    </div>
                    <div class="col-sm-9">
                        <div class="bg-light p-3 rounded">
                            {!! nl2br(e($website->notes)) !!}
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-clock me-2"></i>
                    Thông tin thời gian
                </h6>
            </div>
            <div class="card-body">
                <div class="small">
                    <div class="row mb-2">
                        <div class="col-6">
                            <strong>Tạo lúc:</strong>
                        </div>
                        <div class="col-6">
                            {{ $website->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <strong>Cập nhật:</strong>
                        </div>
                        <div class="col-6">
                            {{ $website->updated_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        @if(auth()->user()->hasPermission('websites.update') || auth()->user()->hasPermission('websites.delete'))
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-cogs me-2"></i>
                    Thao tác
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if(auth()->user()->hasPermission('websites.update'))
                    <a href="{{ route('websites.edit', $website) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-2"></i>
                        Chỉnh sửa
                    </a>
                    @endif
                    
                    @if(auth()->user()->hasPermission('websites.delete'))
                    <form action="{{ route('websites.destroy', $website) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100 btn-delete">
                            <i class="fas fa-trash me-2"></i>
                            Xóa Website
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
