<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Service;
use App\Models\ServiceProposal;
use App\Mail\NotificationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Collection;

class NotificationService
{
    /**
     * Send notification for service actions
     */
    public function sendServiceNotification(string $action, Service $service, User $actor): void
    {
        $recipients = $this->getServiceNotificationRecipients();
        $data = $this->prepareServiceNotificationData($action, $service, $actor);
        
        $this->createAndSendNotifications($recipients, $data, $service);
    }

    /**
     * Send notification for proposal creation
     */
    public function sendProposalCreatedNotification(ServiceProposal $proposal): void
    {
        $recipients = $this->getProposalNotificationRecipients($proposal);
        $data = $this->prepareProposalCreatedData($proposal);
        
        $this->createAndSendNotifications($recipients, $data, $proposal);
        
        // Send to Telegram
        try {
            app(TelegramService::class)->sendProposalCreatedNotification(
                $proposal->service_name,
                $proposal->creator->name,
                $proposal->id,
                $proposal->amount,
                $proposal->target_domain
            );
        } catch (\Exception $e) {
            \Log::error('Failed to send Telegram notification for proposal creation', [
                'proposal_id' => $proposal->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send notification for proposal status change
     */
    public function sendProposalStatusNotification(ServiceProposal $proposal, string $oldStatus, User $actor): void
    {
        $recipients = $this->getProposalStatusNotificationRecipients($proposal);
        $data = $this->prepareProposalStatusData($proposal, $oldStatus, $actor);
        
        $this->createAndSendNotifications($recipients, $data, $proposal);
        
        // Send to Telegram
        try {
            app(TelegramService::class)->sendProposalStatusNotification(
                $proposal->service_name,
                $oldStatus,
                $proposal->status,
                $actor->name,
                $proposal->id,
                $proposal->amount
            );
        } catch (\Exception $e) {
            \Log::error('Failed to send Telegram notification for proposal status change', [
                'proposal_id' => $proposal->id,
                'old_status' => $oldStatus,
                'new_status' => $proposal->status,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send notification for withdrawal actions
     */
    public function sendWithdrawalNotification(string $action, $withdrawal, User $actor): void
    {
        $recipients = $this->getWithdrawalNotificationRecipients();
        $data = $this->prepareWithdrawalNotificationData($action, $withdrawal, $actor);
        
        $this->createAndSendNotifications($recipients, $data, $withdrawal);
        
        // Send to Telegram Withdrawal Channel
        try {
            $proposalNames = $withdrawal->serviceProposals ? 
                $withdrawal->serviceProposals->pluck('service_name')->toArray() : [];
            
            $partnerName = $withdrawal->serviceProposals->first()->creator->name ?? 'Unknown';
            
            if ($action === 'created') {
                app(TelegramWithdrawalService::class)->sendWithdrawalRequestNotification(
                    $withdrawal->id,
                    $partnerName,
                    $withdrawal->amount,
                    $actor->name,
                    $proposalNames
                );
            } else {
                // For status changes, we need old status - will be handled in controller
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send Telegram withdrawal notification', [
                'withdrawal_id' => $withdrawal->id,
                'action' => $action,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send withdrawal status change notification
     */
    public function sendWithdrawalStatusChangeNotification($withdrawal, string $oldStatus, User $actor): void
    {
        // Send regular notifications
        $recipients = $this->getWithdrawalNotificationRecipients();
        $data = $this->prepareWithdrawalNotificationData('status_changed', $withdrawal, $actor);
        
        $this->createAndSendNotifications($recipients, $data, $withdrawal);
        
        // Send to Telegram Withdrawal Channel
        try {
            $proposalNames = $withdrawal->serviceProposals ? 
                $withdrawal->serviceProposals->pluck('service_name')->toArray() : [];
            
            $partnerName = $withdrawal->serviceProposals->first()->creator->name ?? 'Unknown';
            
            app(TelegramWithdrawalService::class)->sendWithdrawalStatusNotification(
                $withdrawal->id,
                $partnerName,
                $withdrawal->amount,
                $oldStatus,
                $withdrawal->status,
                $actor->name,
                $proposalNames
            );
        } catch (\Exception $e) {
            \Log::error('Failed to send Telegram withdrawal status notification', [
                'withdrawal_id' => $withdrawal->id,
                'old_status' => $oldStatus,
                'new_status' => $withdrawal->status,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get recipients for service notifications (admin, IT, assistant, partner)
     */
    private function getServiceNotificationRecipients(): Collection
    {
        return User::whereHas('role', function ($query) {
            $query->whereIn('name', ['admin', 'it', 'assistant', 'partner']);
        })->where('is_active', true)->get();
    }

    /**
     * Get recipients for proposal notifications
     */
    private function getProposalNotificationRecipients(ServiceProposal $proposal): Collection
    {
        $recipients = collect();
        
        // Admin, IT, Assistant (TL)
        $adminItTl = User::whereHas('role', function ($query) {
            $query->whereIn('name', ['admin', 'it', 'assistant']);
        })->where('is_active', true)->get();
        
        $recipients = $recipients->merge($adminItTl);
        
        // Service owner (partner)
        if ($proposal->service && $proposal->service->partner) {
            $recipients->push($proposal->service->partner);
        }
        
        return $recipients->unique('id');
    }

    /**
     * Get recipients for proposal status change notifications
     */
    private function getProposalStatusNotificationRecipients(ServiceProposal $proposal): Collection
    {
        $recipients = $this->getProposalNotificationRecipients($proposal);
        
        // Add proposal creator (seoer)
        if ($proposal->creator) {
            $recipients->push($proposal->creator);
        }
        
        return $recipients->unique('id');
    }

    /**
     * Get recipients for withdrawal notifications (admin, IT, assistant, partner)
     */
    private function getWithdrawalNotificationRecipients(): Collection
    {
        return User::whereHas('role', function ($query) {
            $query->whereIn('name', ['admin', 'it', 'assistant', 'partner']);
        })->where('is_active', true)->get();
    }

    /**
     * Prepare service notification data
     */
    private function prepareServiceNotificationData(string $action, Service $service, User $actor): array
    {
        $actionText = match($action) {
            'created' => 'đã tạo mới',
            'updated' => 'đã cập nhật',
            'deleted' => 'đã xóa',
            'approved' => 'đã duyệt',
            'rejected' => 'đã từ chối',
            default => 'đã thao tác với',
        };

        return [
            'type' => "service_{$action}",
            'title' => "Dịch vụ {$actionText}",
            'message' => "{$actor->name} {$actionText} dịch vụ \"{$service->name}\"",
            'data' => [
                'service_id' => $service->id,
                'service_name' => $service->name,
                'actor_name' => $actor->name,
                'action' => $action,
            ],
            'from_user_id' => $actor->id,
        ];
    }

    /**
     * Prepare proposal created notification data
     */
    private function prepareProposalCreatedData(ServiceProposal $proposal): array
    {
        return [
            'type' => 'proposal_created',
            'title' => 'Đề xuất mới được tạo',
            'message' => "{$proposal->creator->name} đã tạo đề xuất mới cho dịch vụ \"{$proposal->service_name}\"",
            'data' => [
                'proposal_id' => $proposal->id,
                'service_name' => $proposal->service_name,
                'creator_name' => $proposal->creator->name,
                'amount' => $proposal->amount,
            ],
            'from_user_id' => $proposal->created_by,
        ];
    }

    /**
     * Prepare proposal status change notification data
     */
    private function prepareProposalStatusData(ServiceProposal $proposal, string $oldStatus, User $actor): array
    {
        return [
            'type' => 'proposal_status_changed',
            'title' => 'Trạng thái đề xuất thay đổi',
            'message' => "{$actor->name} đã thay đổi trạng thái đề xuất \"{$proposal->service_name}\" từ \"{$this->getStatusText($oldStatus)}\" thành \"{$proposal->getStatusDisplayName()}\"",
            'data' => [
                'proposal_id' => $proposal->id,
                'service_name' => $proposal->service_name,
                'old_status' => $oldStatus,
                'new_status' => $proposal->status,
                'actor_name' => $actor->name,
            ],
            'from_user_id' => $actor->id,
        ];
    }

    /**
     * Prepare withdrawal notification data
     */
    private function prepareWithdrawalNotificationData(string $action, $withdrawal, User $actor): array
    {
        $actionText = match($action) {
            'created' => 'đã tạo yêu cầu rút tiền',
            'updated' => 'đã cập nhật yêu cầu rút tiền',
            'deleted' => 'đã xóa yêu cầu rút tiền',
            'processed' => 'đã xử lý yêu cầu rút tiền',
            'confirmed' => 'đã xác nhận yêu cầu rút tiền',
            default => 'đã thao tác với yêu cầu rút tiền',
        };

        $title = match($action) {
            'created' => 'Yêu cầu rút tiền mới',
            'updated' => 'Cập nhật yêu cầu rút tiền',
            'deleted' => 'Xóa yêu cầu rút tiền',
            'processed' => 'Xử lý yêu cầu rút tiền',
            'confirmed' => 'Xác nhận yêu cầu rút tiền',
            default => 'Thao tác yêu cầu rút tiền',
        };

        return [
            'type' => "withdrawal_{$action}",
            'title' => $title,
            'message' => "{$actor->name} {$actionText} với số tiền " . number_format($withdrawal->amount) . " VNĐ",
            'data' => [
                'withdrawal_id' => $withdrawal->id,
                'amount' => $withdrawal->amount,
                'proposal_names' => $withdrawal->serviceProposals ? $withdrawal->serviceProposals->pluck('service_name')->join(', ') : '',
                'actor_name' => $actor->name,
                'action' => $action,
                'status' => $withdrawal->status ?? 'pending',
            ],
            'from_user_id' => $actor->id,
        ];
    }

    /**
     * Create notifications and send emails
     */
    private function createAndSendNotifications(Collection $recipients, array $data, $notifiable): void
    {
        foreach ($recipients as $recipient) {
            // Skip sending notification to the actor themselves
            if (isset($data['from_user_id']) && $recipient->id == $data['from_user_id']) {
                continue;
            }

            // Create notification
            $notification = Notification::create([
                'type' => $data['type'],
                'title' => $data['title'],
                'message' => $data['message'],
                'data' => $data['data'],
                'from_user_id' => $data['from_user_id'] ?? null,
                'to_user_id' => $recipient->id,
                'notifiable_type' => get_class($notifiable),
                'notifiable_id' => $notifiable->id,
            ]);

            // Send email notification
            if ($recipient->email) {
                try {
                    Mail::to($recipient->email)->send(new NotificationMail($notification));
                } catch (\Exception $e) {
                    \Log::error('Failed to send notification email', [
                        'notification_id' => $notification->id,
                        'recipient_email' => $recipient->email,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
    }

    /**
     * Get status display text
     */
    private function getStatusText(string $status): string
    {
        return match($status) {
            'pending' => 'Chờ duyệt',
            'approved' => 'Đã duyệt',
            'rejected' => 'Từ chối',
            'confirmed' => 'Đã xác nhận',
            'partner_confirmed' => 'Đối tác xác nhận',
            'partner_completed' => 'Đối tác hoàn thành',
            'seoer_confirmed' => 'Seoer xác nhận',
            'completed' => 'Đã hoàn thành',
            'payment_confirmed' => 'Đã thanh toán',
            default => $status,
        };
    }

    /**
     * Get unread notification count for user
     */
    public function getUnreadCount(int $userId): int
    {
        return Notification::forUser($userId)->unread()->count();
    }

    /**
     * Get recent notifications for user
     */
    public function getRecentNotifications(int $userId, int $limit = 10): Collection
    {
        return Notification::forUser($userId)
            ->with(['fromUser', 'notifiable'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(int $notificationId): bool
    {
        $notification = Notification::find($notificationId);
        if ($notification) {
            $notification->markAsRead();
            return true;
        }
        return false;
    }

    /**
     * Mark all notifications as read for user
     */
    public function markAllAsRead(int $userId): void
    {
        Notification::forUser($userId)
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }
}