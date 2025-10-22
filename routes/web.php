<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WebsiteController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ServiceProposalController;
use App\Services\TelegramService;
use App\Services\TelegramWithdrawalService;

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});


// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Test routes for debugging
Route::get('/debug-info', function() {
    return response()->json([
        'user' => auth()->check() ? auth()->user()->name : 'Not logged in',
        'websites_count' => \App\Models\Website::count(),
        'csrf_token' => csrf_token(),
        'config_check' => config('services.cloudflare.api_token') ? 'API Token OK' : 'API Token Missing'
    ]);
})->middleware('auth');

Route::post('/test-ajax', function() {
    return response()->json(['success' => true, 'message' => 'AJAX hoạt động!']);
})->middleware('auth');

Route::get('/debug', function() {
    return view('debug');
})->middleware('auth');

Route::get('/user-info', function() {
    $user = auth()->user();
    return response()->json([
        'user' => $user ? [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role ? [
                'id' => $user->role->id,
                'name' => $user->role->name,
                'display_name' => $user->role->display_name
            ] : null
        ] : null,
        'authenticated' => auth()->check()
    ]);
});

Route::get('/test-cloudflare-sync', function() {
    \Log::info('Test cloudflare sync route called');
    return 'Test cloudflare sync route works!';
})->middleware('auth');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Dashboard detail routes for seoer
    Route::get('/dashboard/seoer/confirmed-orders', [DashboardController::class, 'seoerConfirmedOrders'])->name('dashboard.seoer.confirmed-orders');
    Route::get('/dashboard/seoer/completed-orders', [DashboardController::class, 'seoerCompletedOrders'])->name('dashboard.seoer.completed-orders');
    Route::get('/dashboard/seoer/pending-payment-orders', [DashboardController::class, 'seoerPendingPaymentOrders'])->name('dashboard.seoer.pending-payment-orders');
    Route::get('/dashboard/seoer/budget-spent', [DashboardController::class, 'seoerBudgetSpent'])->name('dashboard.seoer.budget-spent');
    Route::get('/dashboard/seoer/budget-remaining', [DashboardController::class, 'seoerBudgetRemaining'])->name('dashboard.seoer.budget-remaining');

    // Website Management Routes
    // Cloudflare routes must come BEFORE resource routes to avoid conflicts
    Route::get('websites/cloudflare-sync', [WebsiteController::class, 'showCloudflareSync'])->name('websites.cloudflare-sync');
    Route::post('websites/sync-from-cloudflare', [WebsiteController::class, 'syncFromCloudflare'])->name('websites.sync-from-cloudflare');
    Route::post('websites/{website}/301-redirect', [WebsiteController::class, 'create301Redirect'])->name('websites.create-301-redirect');
    Route::get('websites/{website}/301-status', [WebsiteController::class, 'check301Status'])->name('websites.check-301-status');
    
    // Website resource routes with specific permissions
    Route::get('websites', [WebsiteController::class, 'index'])->name('websites.index')->middleware('permission:websites.read');
    Route::get('websites/create', [WebsiteController::class, 'create'])->name('websites.create')->middleware('permission:websites.create');
    Route::post('websites', [WebsiteController::class, 'store'])->name('websites.store')->middleware('permission:websites.create');
    Route::get('websites/{website}', [WebsiteController::class, 'show'])->name('websites.show')->middleware('permission:websites.read');
    Route::get('websites/{website}/edit', [WebsiteController::class, 'edit'])->name('websites.edit')->middleware('permission:websites.update');
    Route::put('websites/{website}', [WebsiteController::class, 'update'])->name('websites.update')->middleware('permission:websites.update');
    Route::delete('websites/{website}', [WebsiteController::class, 'destroy'])->name('websites.destroy')->middleware('permission:websites.delete');

    // Budget Management Routes with specific permissions
    Route::get('budgets', [BudgetController::class, 'index'])->name('budgets.index')->middleware('permission:budgets.read');
    Route::get('budgets/create', [BudgetController::class, 'create'])->name('budgets.create')->middleware('permission:budgets.create');
    Route::post('budgets', [BudgetController::class, 'store'])->name('budgets.store')->middleware('permission:budgets.create');
    Route::get('budgets/{budget}', [BudgetController::class, 'show'])->name('budgets.show')->middleware('permission:budgets.read');
    Route::get('budgets/{budget}/edit', [BudgetController::class, 'edit'])->name('budgets.edit')->middleware('permission:budgets.update');
    Route::put('budgets/{budget}', [BudgetController::class, 'update'])->name('budgets.update')->middleware('permission:budgets.update');
    Route::delete('budgets/{budget}', [BudgetController::class, 'destroy'])->name('budgets.destroy')->middleware('permission:budgets.delete');
    
    // Budget API Routes
    Route::get('api/budgets/domain-suggestions', [BudgetController::class, 'getDomainSuggestions'])->name('budgets.domain-suggestions')->middleware('auth');

    // User Management Routes (Only for Admin and IT) with specific permissions
    Route::get('users', [UserController::class, 'index'])->name('users.index')->middleware('permission:users.read');
    Route::get('users/create', [UserController::class, 'create'])->name('users.create')->middleware('permission:users.create');
    Route::post('users', [UserController::class, 'store'])->name('users.store')->middleware('permission:users.create');
    Route::get('users/{user}', [UserController::class, 'show'])->name('users.show')->middleware('permission:users.read');
    Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit')->middleware('permission:users.update');
    Route::put('users/{user}', [UserController::class, 'update'])->name('users.update')->middleware('permission:users.update');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy')->middleware('permission:users.delete');

    // Profile Management Routes (accessible by all authenticated users)
    Route::get('profile', [UserController::class, 'profile'])->name('profile');
    Route::put('profile', [UserController::class, 'updateProfile'])->name('profile.update');

    // Service Proposal Management Routes
    Route::get('service-proposals', [ServiceProposalController::class, 'index'])->name('service-proposals.index')->middleware('permission:service_proposals.read');
    Route::get('service-proposals/create', [ServiceProposalController::class, 'create'])->name('service-proposals.create')->middleware('permission:service_proposals.create');
    Route::post('service-proposals', [ServiceProposalController::class, 'store'])->name('service-proposals.store')->middleware('permission:service_proposals.create');
    Route::get('service-proposals/{serviceProposal}', [ServiceProposalController::class, 'show'])->name('service-proposals.show')->middleware('permission:service_proposals.read');
    Route::get('service-proposals/{serviceProposal}/edit', [ServiceProposalController::class, 'edit'])->name('service-proposals.edit')->middleware('permission:service_proposals.update');
    Route::put('service-proposals/{serviceProposal}', [ServiceProposalController::class, 'update'])->name('service-proposals.update')->middleware('permission:service_proposals.update');
    Route::delete('service-proposals/{serviceProposal}', [ServiceProposalController::class, 'destroy'])->name('service-proposals.destroy')->middleware('permission:service_proposals.delete');
    
    // Workflow actions
    Route::patch('service-proposals/{serviceProposal}/approve', [ServiceProposalController::class, 'approve'])->name('service-proposals.approve');
    Route::patch('service-proposals/{serviceProposal}/reject', [ServiceProposalController::class, 'reject'])->name('service-proposals.reject');
    Route::patch('service-proposals/{serviceProposal}/partner-confirm', [ServiceProposalController::class, 'partnerConfirm'])->name('service-proposals.partner-confirm');
    Route::patch('service-proposals/{serviceProposal}/partner-complete', [ServiceProposalController::class, 'partnerComplete'])->name('service-proposals.partner-complete');
    Route::patch('service-proposals/{serviceProposal}/seoer-confirm', [ServiceProposalController::class, 'seoerConfirm'])->name('service-proposals.seoer-confirm');
    Route::patch('service-proposals/{serviceProposal}/admin-complete', [ServiceProposalController::class, 'adminComplete'])->name('service-proposals.admin-complete');
    Route::patch('service-proposals/{serviceProposal}/payment-confirm', [ServiceProposalController::class, 'paymentConfirm'])->name('service-proposals.payment-confirm');
    // Legacy routes for backward compatibility
    Route::patch('service-proposals/{serviceProposal}/confirm', [ServiceProposalController::class, 'confirm'])->name('service-proposals.confirm');
    Route::patch('service-proposals/{serviceProposal}/complete', [ServiceProposalController::class, 'complete'])->name('service-proposals.complete');
    
    // Withdrawal routes (Admin không được phép truy cập)
    Route::middleware(['role:!admin'])->group(function () {
        Route::get('withdrawals', [App\Http\Controllers\WithdrawalController::class, 'index'])->name('withdrawals.index')->middleware('permission:withdrawals.read');
        Route::get('withdrawals/create', [App\Http\Controllers\WithdrawalController::class, 'create'])->name('withdrawals.create')->middleware('permission:withdrawals.create');
        Route::post('withdrawals', [App\Http\Controllers\WithdrawalController::class, 'store'])->name('withdrawals.store')->middleware('permission:withdrawals.create');
        Route::get('withdrawals/{withdrawal}', [App\Http\Controllers\WithdrawalController::class, 'show'])->name('withdrawals.show');
        Route::delete('withdrawals/{withdrawal}', [App\Http\Controllers\WithdrawalController::class, 'destroy'])->name('withdrawals.destroy')->middleware('permission:withdrawals.delete');
        
        // Withdrawal workflow routes
        Route::patch('withdrawals/{withdrawal}/assistant-process', [App\Http\Controllers\WithdrawalController::class, 'assistantProcess'])->name('withdrawals.assistant-process')->middleware('permission:withdrawals.update');
        Route::patch('withdrawals/{withdrawal}/partner-confirm', [App\Http\Controllers\WithdrawalController::class, 'partnerConfirm'])->name('withdrawals.partner-confirm');
    });

    // Services routes
    Route::resource('services', App\Http\Controllers\ServiceController::class);
    Route::get('services/{service}/create-proposal', [App\Http\Controllers\ServiceController::class, 'createProposal'])->name('services.create-proposal');
    Route::post('services/bulk-create-proposals', [App\Http\Controllers\ServiceController::class, 'bulkCreateProposals'])->name('services.bulk-create-proposals');
    Route::post('services/{service}/approve', [App\Http\Controllers\ServiceController::class, 'approve'])->name('services.approve')->middleware('permission:services.approve');
    Route::post('services/{service}/reject', [App\Http\Controllers\ServiceController::class, 'reject'])->name('services.reject')->middleware('permission:services.approve');

    // Notification routes
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [App\Http\Controllers\NotificationController::class, 'index'])->name('index');
        Route::get('/data', [App\Http\Controllers\NotificationController::class, 'getNotifications'])->name('data');
        Route::get('/unread-count', [App\Http\Controllers\NotificationController::class, 'getUnreadCount'])->name('unread-count');
        Route::post('/{id}/mark-read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
    });

    // Audit Log routes (Admin and IT only)
    Route::get('audit-logs', [App\Http\Controllers\AuditLogController::class, 'index'])->name('audit-logs.index')->middleware('permission:audit_logs.read');
    Route::get('audit-logs/{auditLog}', [App\Http\Controllers\AuditLogController::class, 'show'])->name('audit-logs.show')->middleware('permission:audit_logs.read');
    Route::get('audit-logs/export/csv', [App\Http\Controllers\AuditLogController::class, 'export'])->name('audit-logs.export')->middleware('permission:audit_logs.read');
    Route::get('api/audit-logs/model', [App\Http\Controllers\AuditLogController::class, 'getModelAuditLogs'])->name('audit-logs.model')->middleware('permission:audit_logs.read');

    // 301 Redirects routes (IT only)
    Route::get('redirects-301', [App\Http\Controllers\Redirect301Controller::class, 'index'])->name('redirects-301.index')->middleware('permission:redirects.read');
    Route::post('redirects-301', [App\Http\Controllers\Redirect301Controller::class, 'store'])->name('redirects-301.store')->middleware('permission:redirects.create');

    // Telegram test route (Admin and IT only)
    Route::get('test-telegram', function (TelegramService $telegramService) {
        try {
            // Test connection
            $connectionTest = $telegramService->testConnection();
            
            if (!$connectionTest['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lỗi kết nối: ' . $connectionTest['message'],
                    'error' => $connectionTest['error']
                ]);
            }

            // Get updates to find chat ID
            $updatesResult = $telegramService->getUpdates();
            
            // Test sending message
            $testMessage = "🧪 <b>Test thông báo từ BL555 Manage</b>\n\n";
            $testMessage .= "📋 <b>Dịch vụ:</b> Test Service\n";
            $testMessage .= "👤 <b>Người test:</b> " . auth()->user()->name . "\n";
            $testMessage .= "📊 <b>Trạng thái:</b> ⏳ Chờ duyệt ➜ ✅ Đã duyệt\n";
            $testMessage .= "💰 <b>Số tiền:</b> 1.000.000 VNĐ\n";
            $testMessage .= "🆔 <b>ID:</b> #TEST\n";
            $testMessage .= "⏰ <b>Thời gian:</b> " . now()->format('d/m/Y H:i:s');

            $messageSent = $telegramService->sendMessage($testMessage);

            return response()->json([
                'success' => $messageSent,
                'connection_info' => $connectionTest['bot_info'],
                'updates' => $updatesResult['success'] ? $updatesResult['updates'] : null,
                'message' => $messageSent ? 'Test thành công! Tin nhắn đã được gửi đến Telegram.' : 'Kết nối thành công nhưng không thể gửi tin nhắn.',
                'instructions' => 'Nếu không gửi được tin nhắn, hãy kiểm tra chat ID trong updates và cập nhật trong TelegramService.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi không mong muốn: ' . $e->getMessage()
            ]);
        }
    })->name('test-telegram')->middleware(['permission:services.read']);

    // Telegram withdrawal test route (Admin and IT only)
    Route::get('test-telegram-withdrawal', function (TelegramWithdrawalService $telegramWithdrawalService) {
        try {
            // Test connection
            $connectionTest = $telegramWithdrawalService->testConnection();
            
            if (!$connectionTest['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lỗi kết nối withdrawal bot: ' . $connectionTest['message'],
                    'error' => $connectionTest['error']
                ]);
            }

            // Get updates to find chat ID
            $updatesResult = $telegramWithdrawalService->getUpdates();
            
            // Test withdrawal request notification
            $requestSent = $telegramWithdrawalService->sendWithdrawalRequestNotification(
                999,
                'Test Partner',
                5000000,
                auth()->user()->name,
                ['Test Service 1', 'Test Service 2']
            );

            // Test withdrawal status change notification
            $statusSent = $telegramWithdrawalService->sendWithdrawalStatusNotification(
                999,
                'Test Partner',
                5000000,
                'pending',
                'assistant_completed',
                auth()->user()->name,
                ['Test Service 1', 'Test Service 2']
            );

            return response()->json([
                'success' => $requestSent && $statusSent,
                'connection_info' => $connectionTest['bot_info'],
                'updates' => $updatesResult['success'] ? $updatesResult['updates'] : null,
                'request_notification' => $requestSent ? 'Thành công' : 'Thất bại',
                'status_notification' => $statusSent ? 'Thành công' : 'Thất bại',
                'message' => ($requestSent && $statusSent) ? 
                    'Test withdrawal notifications thành công! Kiểm tra channel Telegram.' : 
                    'Một số thông báo không gửi được.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi không mong muốn: ' . $e->getMessage()
            ]);
        }
    })->name('test-telegram-withdrawal')->middleware(['permission:withdrawals.read']);
});
