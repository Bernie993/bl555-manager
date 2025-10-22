<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'message',
        'data',
        'from_user_id',
        'to_user_id',
        'is_read',
        'read_at',
        'notifiable_type',
        'notifiable_id',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    /**
     * Get the user who sent the notification
     */
    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    /**
     * Get the user who receives the notification
     */
    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    /**
     * Get the notifiable model (Service, ServiceProposal, etc.)
     */
    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Scope for unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope for specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('to_user_id', $userId);
    }

    /**
     * Get formatted time ago
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get notification icon based on type
     */
    public function getIconAttribute(): string
    {
        return match($this->type) {
            'service_created' => 'fas fa-plus-circle text-success',
            'service_updated' => 'fas fa-edit text-info',
            'service_deleted' => 'fas fa-trash text-danger',
            'proposal_created' => 'fas fa-file-plus text-primary',
            'proposal_status_changed' => 'fas fa-sync-alt text-warning',
            'withdrawal_created' => 'fas fa-money-bill-wave text-success',
            'withdrawal_updated' => 'fas fa-edit text-info',
            'withdrawal_deleted' => 'fas fa-trash text-danger',
            'withdrawal_processed' => 'fas fa-check-circle text-success',
            'withdrawal_confirmed' => 'fas fa-check-double text-primary',
            default => 'fas fa-bell text-secondary',
        };
    }

    /**
     * Get notification URL
     */
    public function getUrlAttribute(): string
    {
        if (!$this->notifiable_type || !$this->notifiable_id) {
            return '#';
        }

        return match($this->notifiable_type) {
            'App\Models\Service' => route('services.show', $this->notifiable_id),
            'App\Models\ServiceProposal' => route('service-proposals.show', $this->notifiable_id),
            'App\Models\Withdrawal' => route('withdrawals.show', $this->notifiable_id),
            default => '#',
        };
    }
}