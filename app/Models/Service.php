<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_id',
        'name',
        'type',
        'website',
        'dr',
        'da',
        'pa',
        'tf',
        'ip',
        'keywords',
        'category',
        'price',
        'description',
        'quote_file',
        'demo_file',
        'ref_domain',
        'traffic',
        'is_active',
        'approval_status',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'keywords' => 'array',
        'approved_at' => 'datetime',
    ];

    /**
     * Service types
     */
    public const TYPES = [
        'entity' => 'Entity',
        'backlink' => 'Backlink',
        'textlink' => 'Textlink',
        'guest_post' => 'Guest Post',
        'content' => 'Content',
    ];

    /**
     * Approval statuses
     */
    public const APPROVAL_STATUSES = [
        'pending' => 'Chờ TL xác duyệt',
        'approved' => 'Đã duyệt',
        'rejected' => 'Từ chối',
    ];

    /**
     * Get the partner that owns the service
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    /**
     * Get the user who approved this service
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the service proposals for this service
     */
    public function serviceProposals(): HasMany
    {
        return $this->hasMany(ServiceProposal::class);
    }

    /**
     * Get type display name
     */
    public function getTypeDisplayName(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    /**
     * Get approval status display name
     */
    public function getApprovalStatusDisplayName(): string
    {
        return self::APPROVAL_STATUSES[$this->approval_status] ?? $this->approval_status;
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 0, ',', '.') . ' VNĐ';
    }

    /**
     * Get keywords as string
     */
    public function getKeywordsStringAttribute(): string
    {
        if (is_array($this->keywords)) {
            return implode(', ', $this->keywords);
        }
        return $this->keywords ?? '';
    }

    /**
     * Scope for active services
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for approved services
     */
    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved');
    }

    /**
     * Scope for pending approval services
     */
    public function scopePendingApproval($query)
    {
        return $query->where('approval_status', 'pending');
    }

    /**
     * Scope for specific type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for specific partner
     */
    public function scopeOfPartner($query, $partnerId)
    {
        return $query->where('partner_id', $partnerId);
    }

    /**
     * Check if user can manage this service
     */
    public function canBeManageBy($user): bool
    {
        // Only the partner who owns the service can manage it
        return $user && $user->id === $this->partner_id;
    }

    /**
     * Check if user can approve this service
     */
    public function canBeApprovedBy($user): bool
    {
        // Only assistant (TL) can approve services
        return $user && $user->role && $user->role->name === 'assistant' && $this->approval_status === 'pending';
    }

    /**
     * Check if service is approved
     */
    public function isApproved(): bool
    {
        return $this->approval_status === 'approved';
    }

    /**
     * Check if service is pending approval
     */
    public function isPendingApproval(): bool
    {
        return $this->approval_status === 'pending';
    }

    /**
     * Check if service is rejected
     */
    public function isRejected(): bool
    {
        return $this->approval_status === 'rejected';
    }
}