<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Hệ thống quản lý BL555')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        .top-navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .top-navbar .navbar-brand {
            color: white !important;
            font-weight: bold;
            font-size: 1.5rem;
        }
        .top-navbar .nav-link {
            color: rgba(255,255,255,0.8) !important;
            padding: 12px 16px;
            margin: 0 2px;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        .top-navbar .nav-link:hover,
        .top-navbar .nav-link.active {
            color: white !important;
            background: rgba(255,255,255,0.15);
            transform: translateY(-2px);
        }
        .user-info {
            color: rgba(255,255,255,0.9);
        }
        .user-info .badge {
            background-color: rgba(255,255,255,0.2) !important;
            color: white;
        }
        .main-content {
            background-color: #f8f9fa;
            min-height: calc(100vh - 80px);
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 25px;
            padding: 10px 25px;
        }
        .navbar-brand {
            font-weight: bold;
            color: #667eea !important;
        }
        .table th {
            /*background-color: #f8f9fa;*/
            border: none;
            font-weight: 600;
        }
        .badge {
            border-radius: 20px;
            padding: 5px 12px;
        }

        /* Responsive styles */
        @media (max-width: 991.98px) {
            .top-navbar .navbar-nav .nav-link {
                padding: 8px 16px;
                margin: 2px 0;
            }

            .top-navbar .navbar-collapse {
                margin-top: 1rem;
                padding-top: 1rem;
                border-top: 1px solid rgba(255,255,255,0.2);
            }

            .user-info .badge {
                display: block;
                margin-top: 0.25rem;
            }
        }

        @media (max-width: 767.98px) {
            .top-navbar .navbar-brand {
                font-size: 1.2rem;
            }

            .main-content .p-4 {
                padding: 1rem !important;
            }
        }
    </style>

    @stack('styles')
</head>
<body>
    <!-- Top Navigation -->
    <nav class="navbar navbar-expand-lg top-navbar">
        <div class="container-fluid">
            <!-- Brand -->
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <i class="fas fa-chart-line me-2"></i>
                BL555 Manager
            </a>

            <!-- Mobile toggle button -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    style="border: 1px solid rgba(255,255,255,0.3);">
                <span class="navbar-toggler-icon" style="background-image: url('data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 30 30\'%3e%3cpath stroke=\'rgba%28255, 255, 255, 0.8%29\' stroke-linecap=\'round\' stroke-miterlimit=\'10\' stroke-width=\'2\' d=\'M4 7h22M4 15h22M4 23h22\'/%3e%3c/svg%3e');"></span>
            </button>

            <!-- Navigation Menu -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <i class="fas fa-tachometer-alt me-1"></i>
                            Dashboard
                        </a>
                    </li>

                    @if(auth()->user()->hasPermission('websites.read'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('websites.*') ? 'active' : '' }}" href="{{ route('websites.index') }}">
                            <i class="fas fa-globe me-1"></i>
                            Quản lý Website
                        </a>
                    </li>
                    @endif

                    @if(auth()->user()->hasPermission('budgets.read'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('budgets.*') ? 'active' : '' }}" href="{{ route('budgets.index') }}">
                            <i class="fas fa-wallet me-1"></i>
                            Quản lý Ngân sách
                        </a>
                    </li>
                    @endif

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('service-proposals.*') ? 'active' : '' }}" href="{{ route('service-proposals.index') }}">
                            <i class="fas fa-clipboard-list me-1"></i>
                            Đề xuất Dịch vụ
                        </a>
                    </li>

                    @if(auth()->user()->hasPermission('withdrawals.read'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('withdrawals.*') ? 'active' : '' }}" href="{{ route('withdrawals.index') }}">
                            <i class="fas fa-money-bill-wave me-1"></i>
                            Quản lý Rút tiền
                        </a>
                    </li>
                    @endif

                    @if(auth()->user()->hasPermission('services.read'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('services.*') ? 'active' : '' }}" href="{{ route('services.index') }}">
                            <i class="fas fa-cogs me-1"></i>
                            Quản lý Dịch vụ
                        </a>
                    </li>
                    @endif

                    @if(auth()->user()->hasPermission('users.read'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                            <i class="fas fa-users me-1"></i>
                            Quản lý Người dùng
                        </a>
                    </li>
                    @endif

                    @if(auth()->user()->hasPermission('audit_logs.read'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}" href="{{ route('audit-logs.index') }}">
                            <i class="fas fa-history me-1"></i>
                            Lịch sử thao tác
                        </a>
                    </li>
                    @endif

                </ul>

                <!-- User Info & Actions -->
                <div class="navbar-nav ms-auto d-flex align-items-center">
                    <!-- Notification Bell -->
                    <div class="nav-item dropdown me-3">
                        <button class="btn btn-link position-relative p-0 border-0"
                                type="button"
                                id="notificationDropdown"
                                data-bs-toggle="dropdown"
                                aria-expanded="false">
                            <i class="fas fa-bell fa-lg" style="color: rgba(255,255,255,0.8);"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                  id="notification-count"
                                  style="font-size: 0.6rem; display: none;">
                                0
                            </span>
                        </button>

                        <div class="dropdown-menu dropdown-menu-end shadow"
                             aria-labelledby="notificationDropdown"
                             style="width: 400px; max-height: 500px; overflow-y: auto;">
                            <div class="dropdown-header d-flex justify-content-between align-items-center">
                                <span class="fw-bold">Thông báo</span>
                                <button class="btn btn-sm btn-link text-decoration-none p-0"
                                        id="mark-all-read"
                                        style="font-size: 0.8rem;">
                                    Đánh dấu tất cả đã đọc
                                </button>
                            </div>
                            <div class="dropdown-divider"></div>
                            <div id="notification-list">
                                <div class="text-center py-3">
                                    <i class="fas fa-spinner fa-spin"></i>
                                    <div class="small text-muted">Đang tải...</div>
                                </div>
                            </div>
                            <div class="dropdown-divider"></div>
                            <div class="dropdown-item text-center">
                                <a href="{{ route('notifications.index') }}" class="text-decoration-none">
                                    Xem tất cả thông báo
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- User Info -->
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle user-info" href="#" id="userDropdown" role="button"
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user me-2"></i>
                            {{ auth()->user()->name }}
                            <span class="badge ms-2">{{ auth()->user()->role->display_name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="{{ route('profile') }}">
                                    <i class="fas fa-user-edit me-2"></i>
                                    Thông tin cá nhân
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt me-2"></i>
                                        Đăng xuất
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Breadcrumb Navigation -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom" style="min-height: 60px;">
            <div class="container-fluid">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        @yield('breadcrumb')
                    </ol>
                </nav>

                <div class="ms-auto d-flex align-items-center">
                    <span class="text-muted">{{ now()->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <div class="p-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <!-- Bootstrap JS -->
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script>
        // Auto hide alerts after 5 seconds
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Confirm delete actions
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.btn-delete');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (confirm('Bạn có chắc chắn muốn xóa?')) {
                        this.closest('form').submit();
                    }
                });
            });

            // Notification system
            initNotificationSystem();
        });

        function initNotificationSystem() {
            // Load notifications on dropdown show
            const notificationDropdown = document.getElementById('notificationDropdown');
            const notificationList = document.getElementById('notification-list');
            const notificationCount = document.getElementById('notification-count');
            const markAllReadBtn = document.getElementById('mark-all-read');

            // Load initial count
            updateNotificationCount();

            // Load notifications when dropdown is shown
            notificationDropdown.addEventListener('click', function() {
                loadNotifications();
            });

            // Mark all as read
            markAllReadBtn.addEventListener('click', function() {
                markAllNotificationsAsRead();
            });

            // Poll for new notifications every 30 seconds
            setInterval(updateNotificationCount, 30000);
        }

        function updateNotificationCount() {
            fetch('{{ route("notifications.unread-count") }}')
                .then(response => response.json())
                .then(data => {
                    const badge = document.getElementById('notification-count');
                    if (data.count > 0) {
                        badge.textContent = data.count > 99 ? '99+' : data.count;
                        badge.style.display = 'block';
                    } else {
                        badge.style.display = 'none';
                    }
                })
                .catch(error => console.error('Error loading notification count:', error));
        }

        function loadNotifications() {
            const notificationList = document.getElementById('notification-list');

            fetch('{{ route("notifications.data") }}?limit=10')
                .then(response => response.json())
                .then(data => {
                    if (data.notifications.length === 0) {
                        notificationList.innerHTML = `
                            <div class="text-center py-3">
                                <i class="fas fa-bell-slash text-muted"></i>
                                <div class="small text-muted">Không có thông báo nào</div>
                            </div>
                        `;
                        return;
                    }

                    let html = '';
                    data.notifications.forEach(notification => {
                        const readClass = notification.is_read ? '' : 'bg-light';
                        html += `
                            <div class="dropdown-item ${readClass} notification-item"
                                 data-id="${notification.id}"
                                 data-url="${notification.url}"
                                 style="cursor: pointer; white-space: normal;">
                                <div class="d-flex">
                                    <div class="me-3">
                                        <i class="${notification.icon}"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold small">${notification.title}</div>
                                        <div class="text-muted small">${notification.message}</div>
                                        <div class="text-muted" style="font-size: 0.7rem;">
                                            <i class="fas fa-user me-1"></i>${notification.from_user}
                                            <span class="ms-2"><i class="fas fa-clock me-1"></i>${notification.time_ago}</span>
                                        </div>
                                    </div>
                                    ${!notification.is_read ? '<div class="text-primary"><i class="fas fa-circle" style="font-size: 0.5rem;"></i></div>' : ''}
                                </div>
                            </div>
                        `;
                    });

                    notificationList.innerHTML = html;

                    // Add click handlers
                    document.querySelectorAll('.notification-item').forEach(item => {
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
                                updateNotificationCount();
                                if (url && url !== '#') {
                                    window.location.href = url;
                                }
                            });
                        });
                    });
                })
                .catch(error => {
                    console.error('Error loading notifications:', error);
                    notificationList.innerHTML = `
                        <div class="text-center py-3">
                            <i class="fas fa-exclamation-triangle text-warning"></i>
                            <div class="small text-muted">Lỗi tải thông báo</div>
                        </div>
                    `;
                });
        }

        function markAllNotificationsAsRead() {
            fetch('{{ route("notifications.mark-all-read") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
            }).then(() => {
                updateNotificationCount();
                loadNotifications();
            }).catch(error => console.error('Error marking all as read:', error));
        }
    </script>

    @stack('scripts')
</body>
</html>
