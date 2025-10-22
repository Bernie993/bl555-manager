<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\Auditable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'is_active',
        'hire_date',
        'permanent_date',
        'resignation_date',
        'phone',
        'telegram',
        'payment_info',
    ];

    /**
     * Audit field display names
     */
    protected $auditFieldNames = [
        'name' => 'Tên',
        'email' => 'Email',
        'role_id' => 'Vai trò',
        'is_active' => 'Trạng thái hoạt động',
        'hire_date' => 'Ngày nhận việc',
        'permanent_date' => 'Ngày chuyển chính',
        'resignation_date' => 'Ngày nghỉ việc',
    ];

    /**
     * Fields excluded from audit
     */
    protected $auditExcluded = [
        'password',
        'remember_token',
        'email_verified_at',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'hire_date' => 'date',
            'permanent_date' => 'date',
            'resignation_date' => 'date',
        ];
    }

    /**
     * Get the role that owns the user.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Check if user has permission
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->role) {
            return false;
        }
        return $this->role->permissions->contains('name', $permission);
    }

    /**
     * Check if user can perform action on module
     */
    public function canAccess(string $action, string $module): bool
    {
        return $this->hasPermission("{$module}.{$action}");
    }

    /**
     * Get service proposals created by this user
     */
    public function serviceProposals()
    {
        return $this->hasMany(ServiceProposal::class, 'created_by');
    }

    /**
     * Get service proposals approved by this user
     */
    public function approvedServiceProposals()
    {
        return $this->hasMany(ServiceProposal::class, 'approved_by');
    }

    /**
     * Get websites assigned to this user as seoer
     */
    public function websites()
    {
        return $this->hasMany(Website::class, 'seoer_id');
    }

    /**
     * Get user roles (many-to-many relationship)
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Get notifications sent to this user
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'to_user_id');
    }

    /**
     * Get notifications sent by this user
     */
    public function sentNotifications()
    {
        return $this->hasMany(Notification::class, 'from_user_id');
    }

    /**
     * Get unread notifications count
     */
    public function getUnreadNotificationsCountAttribute(): int
    {
        return $this->notifications()->unread()->count();
    }

    /**
     * Check if user has specific role
     */
    public function hasRole($role): bool
    {
        // Nếu có single role (role_id)
        if ($this->role) {
            if (is_string($role)) {
                return $this->role->name === $role;
            }
            return $this->role->id === $role;
        }

        // Nếu có many-to-many roles
        if (is_string($role)) {
            return $this->roles()->where('name', $role)->exists();
        }
        
        return $this->roles()->where('id', $role)->exists();
    }

    /**
     * Get role name (for backward compatibility)
     */
    public function getRoleName(): string
    {
        if ($this->role) {
            return $this->role->name;
        }
        
        $firstRole = $this->roles()->first();
        return $firstRole ? $firstRole->name : '';
    }

    /**
     * Get the services owned by the partner
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'partner_id');
    }

    /**
     * Get the budgets assigned to the user (by name match)
     */
    public function budgets()
    {
        return Budget::where('seoer', $this->name);
    }

    /**
     * Get withdrawals for this partner
     */
    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class, 'partner_id');
    }

    /**
     * Get total withdrawn amount for partner
     */
    public function getTotalWithdrawnAmount(): float
    {
        if (!$this->hasRole('partner')) {
            return 0;
        }

        return (float) DB::table('withdrawal_service_proposals')
            ->join('withdrawals', 'withdrawals.id', '=', 'withdrawal_service_proposals.withdrawal_id')
            ->join('service_proposals', 'service_proposals.id', '=', 'withdrawal_service_proposals.service_proposal_id')
            ->whereIn('service_proposals.service_id', $this->services()->pluck('id'))
            ->whereIn('withdrawals.status', ['assistant_completed', 'partner_confirmed'])
            ->sum('withdrawal_service_proposals.amount');
    }

    /**
     * Get total pending withdrawal amount for partner
     */
    public function getTotalPendingAmount(): float
    {
        if (!$this->hasRole('partner')) {
            return 0;
        }

        $completedProposals = ServiceProposal::whereIn('service_id', $this->services()->pluck('id'))
            ->whereIn('status', ['payment_confirmed', 'completed'])
            ->get();
            
        $totalPending = 0;
        foreach ($completedProposals as $proposal) {
            $totalPending += $proposal->getRemainingWithdrawableAmount();
        }
        
        return $totalPending;
    }

    /**
     * Get count of proposals using partner's services
     */
    public function getProposalsCount(): int
    {
        if (!$this->hasRole('partner')) {
            return 0;
        }

        return ServiceProposal::whereIn('service_id', $this->services()->pluck('id'))->count();
    }
}
