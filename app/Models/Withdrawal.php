<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Withdrawal extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'partner_id',
        'amount',
        'note',
        'status',
        'assistant_processed_by',
        'assistant_processed_at',
        'payment_proof_image',
        'assistant_note',
        'partner_confirmed_by',
        'partner_confirmed_at',
        'partner_confirmation_note',
    ];

    protected $casts = [
        'assistant_processed_at' => 'datetime',
        'partner_confirmed_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    /**
     * Audit field names for logging
     */
    protected $auditFieldNames = [
        'amount' => 'Số tiền',
        'note' => 'Ghi chú',
        'status' => 'Trạng thái',
        'assistant_note' => 'Ghi chú Assistant',
        'partner_confirmation_note' => 'Ghi chú xác nhận',
        'payment_proof_image' => 'Ảnh bill chuyển khoản',
    ];

    /**
     * Fields excluded from audit
     */
    protected $auditExcluded = [
        'created_at',
        'updated_at',
        'partner_id',
        'assistant_processed_by',
        'partner_confirmed_by',
        'assistant_processed_at',
        'partner_confirmed_at',
    ];

    /**
     * Relationships
     */
    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function assistantProcessor()
    {
        return $this->belongsTo(User::class, 'assistant_processed_by');
    }

    public function partnerConfirmer()
    {
        return $this->belongsTo(User::class, 'partner_confirmed_by');
    }

    public function serviceProposals()
    {
        return $this->belongsToMany(ServiceProposal::class, 'withdrawal_service_proposals')
                    ->withPivot('amount')
                    ->withTimestamps();
    }

    /**
     * Status display names
     */
    public function getStatusDisplayName(): string
    {
        return match ($this->status) {
            'pending' => 'Chờ thanh toán',
            'assistant_completed' => 'Trợ lý đã hoàn thành thanh toán',
            'partner_confirmed' => 'Đối tác đã xác nhận nhận tiền',
            default => 'Không xác định'
        };
    }

    /**
     * Status badge class
     */
    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            'pending' => 'bg-warning text-dark',
            'assistant_completed' => 'bg-info text-white',
            'partner_confirmed' => 'bg-success text-white',
            default => 'bg-secondary text-white'
        };
    }

    /**
     * Formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 0, ',', '.') . ' VNĐ';
    }

    /**
     * Check if withdrawal can be processed by assistant
     */
    public function canBeProcessedByAssistant(User $user): bool
    {
        return $user->role && $user->role->name === 'assistant' && $this->status === 'pending';
    }

    /**
     * Check if withdrawal can be confirmed by partner
     */
    public function canBeConfirmedByPartner(User $user): bool
    {
        return $user->role && $user->role->name === 'partner' && 
               $this->status === 'assistant_completed' && 
               $this->partner_id == $user->id;
    }

    /**
     * Get available actions for user
     */
    public function getAvailableActionsFor(User $user): array
    {
        $actions = [];

        if ($this->canBeProcessedByAssistant($user)) {
            $actions[] = [
                'action' => 'assistant_process',
                'label' => 'Xác nhận thanh toán',
                'class' => 'btn-info',
                'route' => 'withdrawals.assistant-process'
            ];
        }

        if ($this->canBeConfirmedByPartner($user)) {
            $actions[] = [
                'action' => 'partner_confirm',
                'label' => 'Xác nhận đã nhận tiền',
                'class' => 'btn-success',
                'route' => 'withdrawals.partner-confirm'
            ];
        }

        return $actions;
    }

    /**
     * Get total amount from service proposals
     */
    public function getTotalServiceProposalsAmount(): float
    {
        return $this->serviceProposals->sum('pivot.amount');
    }
}