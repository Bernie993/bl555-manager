@extends('layouts.app')

@section('title', 'Lỗi hệ thống')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Lỗi hệ thống (500)
                    </h4>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-server fa-5x text-danger mb-3"></i>
                    </div>
                    
                    <h5 class="text-danger mb-3">{{ $message ?? 'Đã xảy ra lỗi hệ thống' }}</h5>
                    
                    @if(isset($details) && config('app.debug'))
                    <div class="alert alert-warning text-start">
                        <strong>Chi tiết lỗi:</strong><br>
                        <code>{{ $details }}</code>
                    </div>
                    @endif
                    
                    <div class="mt-4">
                        <a href="{{ route('dashboard') }}" class="btn btn-primary me-2">
                            <i class="fas fa-home me-2"></i>
                            Về Dashboard
                        </a>
                        <button onclick="window.location.reload()" class="btn btn-outline-secondary">
                            <i class="fas fa-redo me-2"></i>
                            Thử lại
                        </button>
                    </div>
                    
                    <div class="mt-3">
                        <small class="text-muted">
                            Vui lòng thử lại sau hoặc liên hệ administrator nếu lỗi tiếp tục xảy ra.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
