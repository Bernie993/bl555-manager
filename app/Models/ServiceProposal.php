<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Auditable;

class ServiceProposal extends Model
{
    use HasFactory; // , Auditable;

    protected $fillable = [
        'service_id',
        'service_name',
        'target_domain',
        'quantity',
        'supplier_name',
        'supplier_phone', // Temporary - for backward compatibility
        'supplier_telegram',
        'proposal_link',
        'amount',
        'unit_price',
        'status',
        'created_by',
        'approved_by',
        'partner_confirmed_by',
        'partner_completed_by',
        'result_link',
        'seoer_confirmed_by',
        'admin_completed_by',
        'payment_confirmed_by',
        'budget_id',
        'notes',
        'approved_at',
        'partner_confirmed_at',
        'partner_completed_at',
        'seoer_confirmed_at',
        'admin_completed_at',
        'payment_confirmed_at',
        'confirmed_at',
        'completed_at',
    ];

    /**
     * Audit field display names
     */
    protected $auditFieldNames = [
        'service_name' => 'Tên dịch vụ',
        'target_domain' => 'Domain đích',
        'quantity' => 'Số lượng',
        'supplier_name' => 'Nhà cung cấp',
        'supplier_phone' => 'SDT NCC',
        'supplier_telegram' => 'Telegram NCC',
        'proposal_link' => 'Link đề xuất',
        'result_link' => 'Link kết quả',
        'amount' => 'Số tiền',
        'status' => 'Trạng thái',
        'budget_id' => 'Ngân sách',
        'notes' => 'Ghi chú',
    ];

    /**
     * Fields excluded from audit
     */
    protected $auditExcluded = [
        'created_at',
        'updated_at',
        'created_by',
        'approved_by',
        'partner_confirmed_by',
        'partner_completed_by',
        'seoer_confirmed_by',
        'admin_completed_by',
        'payment_confirmed_by',
        'approved_at',
        'partner_confirmed_at',
        'partner_completed_at',
        'seoer_confirmed_at',
        'admin_completed_at',
        'payment_confirmed_at',
        'confirmed_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'approved_at' => 'datetime',
            'partner_confirmed_at' => 'datetime',
            'partner_completed_at' => 'datetime',
            'seoer_confirmed_at' => 'datetime',
            'admin_completed_at' => 'datetime',
            'payment_confirmed_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Get the user who created this proposal
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who approved this proposal
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the partner who confirmed this proposal
     */
    public function partnerConfirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partner_confirmed_by');
    }

    /**
     * Get the partner who marked this as completed
     */
    public function partnerCompleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partner_completed_by');
    }

    /**
     * Get the admin who confirmed completion
     */
    public function adminCompleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_completed_by');
    }

    /**
     * Get the seoer who confirmed this proposal
     */
    public function seoerConfirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seoer_confirmed_by');
    }

    /**
     * Get the assistant who confirmed payment
     */
    public function paymentConfirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payment_confirmed_by');
    }

    /**
     * Get the budget this proposal belongs to
     */
    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    /**
     * Get the service that this proposal is based on
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get withdrawals for this service proposal
     */
    public function withdrawals()
    {
        return $this->belongsToMany(Withdrawal::class, 'withdrawal_service_proposals')
                    ->withPivot('amount')
                    ->withTimestamps();
    }

    /**
     * Get total withdrawn amount for this proposal
     */
    public function getTotalWithdrawnAmount(): float
    {
        return (float) \DB::table('withdrawal_service_proposals')
                    ->join('withdrawals', 'withdrawals.id', '=', 'withdrawal_service_proposals.withdrawal_id')
                    ->where('withdrawal_service_proposals.service_proposal_id', $this->id)
                    ->whereIn('withdrawals.status', ['pending', 'assistant_completed', 'partner_confirmed'])
                    ->sum('withdrawal_service_proposals.amount');
    }

    /**
     * Get remaining amount available for withdrawal
     */
    public function getRemainingWithdrawableAmount(): float
    {
        return max(0, $this->amount - $this->getTotalWithdrawnAmount());
    }

    /**
     * Check if proposal is fully withdrawn
     */
    public function isFullyWithdrawn(): bool
    {
        return $this->getRemainingWithdrawableAmount() <= 0;
    }

    /**
     * Get status display name
     */
    public function getStatusDisplayName(): string
    {
        // Check if has withdrawals to show payment status
        if (in_array($this->status, ['completed', 'payment_confirmed'])) {
            $totalWithdrawn = $this->getTotalWithdrawnAmount();
            if ($totalWithdrawn > 0) {
                $percentage = round(($totalWithdrawn / $this->amount) * 100);
                if ($percentage >= 100) {
                    return 'Đã thanh toán hết';
                } else {
                    return "Đã thanh toán {$percentage}%";
                }
            }
        }

        return match($this->status) {
            'pending' => 'Chờ duyệt',
            'approved' => 'Đã duyệt',
            'rejected' => 'Từ chối',
            'partner_confirmed' => 'Đối tác xác nhận',
            'partner_completed' => 'Đối tác xác nhận hoàn thành',
            'seoer_confirmed' => 'Seoer xác nhận',
            'admin_completed' => 'Quản lý xác nhận hoàn thành',
            'payment_confirmed' => 'Trợ lý xác nhận hoàn thành',
            'completed' => 'Đã hoàn thành',
            default => 'Không xác định',
        };
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClass(): string
    {
        // Special colors for payment status
        if (in_array($this->status, ['completed', 'payment_confirmed'])) {
            $totalWithdrawn = $this->getTotalWithdrawnAmount();
            if ($totalWithdrawn > 0) {
                $percentage = round(($totalWithdrawn / $this->amount) * 100);
                if ($percentage >= 100) {
                    return 'bg-success'; // Đã thanh toán hết - xanh lá
                } else {
                    return 'bg-warning'; // Đã thanh toán một phần - vàng
                }
            }
        }

        return match($this->status) {
            'pending' => 'bg-warning',
            'approved' => 'bg-info',
            'rejected' => 'bg-danger',
            'partner_confirmed' => 'bg-primary',
            'partner_completed' => 'bg-dark',
            'seoer_confirmed' => 'bg-secondary',
            'admin_completed' => 'bg-success',
            'payment_confirmed' => 'bg-success',
            'completed' => 'bg-success',
            default => 'bg-secondary',
        };
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 0, ',', '.') . ' VNĐ';
    }

    /**
     * Check if user can approve this proposal (Admin and IT)
     */
    public function canBeApprovedBy(User $user): bool
    {
        return $user->role && in_array($user->role->name, ['admin', 'it']) && $this->status === 'pending';
    }

    /**
     * Check if user can partner confirm this proposal (Partner only)
     */
    public function canBePartnerConfirmedBy(User $user): bool
    {
        return $user->role && $user->role->name === 'partner' && $this->status === 'approved';
    }

    /**
     * Check if user can partner complete this proposal (Partner only)
     */
    public function canBePartnerCompletedBy(User $user): bool
    {
        return $user->role && $user->role->name === 'partner' && $this->status === 'partner_confirmed';
    }

    /**
     * Check if user can seoer confirm this proposal (Seoer only)
     */
    public function canBeSeoerConfirmedBy(User $user): bool
    {
        return $user->role && $user->role->name === 'seoer' && $this->status === 'partner_completed';
    }

    /**
     * Check if user can admin complete this proposal (Admin and IT)
     */
    public function canBeAdminCompletedBy(User $user): bool
    {
        return $user->role && in_array($user->role->name, ['admin', 'it']) && $this->status === 'seoer_confirmed';
    }

    /**
     * Check if user can confirm payment (Assistant only)
     */
    public function canBePaymentConfirmedBy(User $user): bool
    {
        return $user->role && $user->role->name === 'assistant' && $this->status === 'admin_completed';
    }

    /**
     * Get next possible actions for current user
     */
    public function getAvailableActionsFor(User $user): array
    {
        $actions = [];

        if ($this->canBeApprovedBy($user)) {
            $actions[] = ['action' => 'approve', 'label' => 'Duyệt', 'class' => 'btn-success'];
            $actions[] = ['action' => 'reject', 'label' => 'Từ chối', 'class' => 'btn-danger'];
        }

        if ($this->canBePartnerConfirmedBy($user)) {
            $actions[] = ['action' => 'partner_confirm', 'label' => 'Đối tác xác nhận', 'class' => 'btn-primary'];
        }

        if ($this->canBePartnerCompletedBy($user)) {
            $actions[] = ['action' => 'partner_complete', 'label' => 'Xác nhận hoàn thành', 'class' => 'btn-info'];
        }

        if ($this->canBeSeoerConfirmedBy($user)) {
            $actions[] = ['action' => 'seoer_confirm', 'label' => 'Seoer xác nhận', 'class' => 'btn-secondary'];
        }

        if ($this->canBeAdminCompletedBy($user)) {
            $actions[] = ['action' => 'admin_complete', 'label' => 'Xác nhận hoàn thành', 'class' => 'btn-success'];
        }

        if ($this->canBePaymentConfirmedBy($user)) {
            $actions[] = ['action' => 'payment_confirm', 'label' => 'Xác nhận hoàn thành', 'class' => 'btn-success'];
        }

        return $actions;
    }
}
