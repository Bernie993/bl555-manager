@extends('layouts.app')

@section('title', 'Thông báo')

@section('breadcrumb')
    <li class="breadcrumb-item active">Thông báo</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-bell me-2"></i>
                Thông báo
            </h1>
            @if($unreadCount > 0)
            <button class="btn btn-outline-primary" id="mark-all-read-page">
                <i class="fas fa-check-double me-2"></i>
                Đánh dấu tất cả đã đọc ({{ $unreadCount }})
            </button>
            @endif
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                @if($notifications->isEmpty())
                    <div class="text-center py-5">
                        <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Không có thông báo nào</h5>
                        <p class="text-muted">Bạn sẽ nhận được thông báo khi có hoạt động mới.</p>
                    </div>
                @else
                    <div class="list-group list-group-flush">
                        @foreach($notifications as $notification)
                        <div class="list-group-item {{ !$notification->is_read ? 'bg-light border-start border-primary border-3' : '' }} notification-item-page" 
                             data-id="{{ $notification->id }}" 
                             data-url="{{ $notification->url }}"
                             style="cursor: pointer;">
                            <div class="d-flex align-items-start">
                                <div class="me-3 mt-1">
                                    <i class="{{ $notification->icon }} fa-lg"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <h6 class="mb-1 {{ !$notification->is_read ? 'fw-bold' : '' }}">
                                            {{ $notification->title }}
                                        </h6>
                                        <div class="text-end">
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i>
                                                {{ $notification->time_ago }}
                                            </small>
                                            @if(!$notification->is_read)
                                            <div class="text-primary mt-1">
                                                <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                                                <small>Mới</small>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                    <p class="mb-2 text-muted">{{ $notification->message }}</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="fas fa-user me-1"></i>
                                            {{ $notification->fromUser ? $notification->fromUser->name : 'System' }}
                                        </small>
                                        @if($notification->data)
                                        <div class="d-flex gap-2">
                                            @if(isset($notification->data['service_name']))
                                            <span class="badge bg-info">{{ $notification->data['service_name'] }}</span>
                                            @endif
                                            @if(isset($notification->data['amount']))
                                            <span class="badge bg-success">{{ number_format($notification->data['amount']) }} VNĐ</span>
                                            @endif
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Pagination if needed -->
                    <div class="d-flex justify-content-center mt-4">
                        <nav>
                            <ul class="pagination">
                                <li class="page-item disabled">
                                    <span class="page-link">Trước</span>
                                </li>
                                <li class="page-item active">
                                    <span class="page-link">1</span>
                                </li>
                                <li class="page-item disabled">
                                    <span class="page-link">Sau</span>
                                </li>
                            </ul>
                        </nav>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle notification clicks
    document.querySelectorAll('.notification-item-page').forEach(item => {
        item.addEventListener('click', function() {
            const id = this.dataset.id;
            const url = this.dataset.url;
            
            // Mark as read
            fetch(`{{ url('/notifications') }}/${id}/mark-read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
            }).then(() => {
                // Remove unread styling
                this.classList.remove('bg-light', 'border-start', 'border-primary', 'border-3');
                this.querySelector('.fw-bold')?.classList.remove('fw-bold');
                this.querySelector('.text-primary')?.remove();
                
                // Navigate if URL exists
                if (url && url !== '#') {
                    window.location.href = url;
                }
            }).catch(error => console.error('Error marking as read:', error));
        });
    });

    // Handle mark all as read
    const markAllBtn = document.getElementById('mark-all-read-page');
    if (markAllBtn) {
        markAllBtn.addEventListener('click', function() {
            fetch('{{ route("notifications.mark-all-read") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
            }).then(() => {
                location.reload();
            }).catch(error => console.error('Error marking all as read:', error));
        });
    }
});
</script>
@endsection
