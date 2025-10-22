@extends('layouts.app')

@section('title', 'Truy cập bị từ chối')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-ban me-2"></i>
                        Truy cập bị từ chối (403)
                    </h4>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-lock fa-5x text-danger mb-3"></i>
                    </div>
                    
                    <h5 class="text-danger mb-3">{{ $message ?? 'Bạn không có quyền truy cập trang này' }}</h5>
                    
                    @if(isset($details))
                    <div class="alert alert-warning">
                        <strong>Chi tiết:</strong> {{ $details }}
                    </div>
                    @endif
                    
                    <div class="mt-4">
                        <a href="{{ route('dashboard') }}" class="btn btn-primary me-2">
                            <i class="fas fa-home me-2"></i>
                            Về Dashboard
                        </a>
                        <a href="{{ route('websites.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-globe me-2"></i>
                            Quản lý Website
                        </a>
                    </div>
                    
                    <div class="mt-3">
                        <small class="text-muted">
                            Nếu bạn nghĩ đây là lỗi, vui lòng liên hệ administrator.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
