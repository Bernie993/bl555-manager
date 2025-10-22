<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Redirect301 extends Model
{
    use HasFactory;

    protected $table = 'redirects_301';

    protected $fillable = [
        'domain_list',
        'target_url',
        'include_www',
        'is_active',
        'cloudflare_rules',
        'created_by',
    ];

    protected $casts = [
        'include_www' => 'boolean',
        'is_active' => 'boolean',
        'cloudflare_rules' => 'array',
    ];

    /**
     * Get the user who created this redirect
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get domain list as array
     */
    public function getDomainListArrayAttribute(): array
    {
        return array_filter(array_map('trim', explode("\n", $this->domain_list)));
    }

    /**
     * Get formatted domain list for display
     */
    public function getFormattedDomainListAttribute(): string
    {
        $domains = $this->domain_list_array;
        if (count($domains) <= 3) {
            return implode(', ', $domains);
        }
        return implode(', ', array_slice($domains, 0, 3)) . ' và ' . (count($domains) - 3) . ' domain khác';
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return $this->is_active ? 'badge-success' : 'badge-secondary';
    }

    /**
     * Get status display name
     */
    public function getStatusDisplayNameAttribute(): string
    {
        return $this->is_active ? 'Hoạt động' : 'Không hoạt động';
    }

    /**
     * Scope for active redirects
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}