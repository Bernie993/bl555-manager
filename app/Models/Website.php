<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\CloudflareService;

class Website extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'seoer', // Giữ lại để tương thích
        'seoer_id',
        'status',
        'category',
        'has_301_redirect',
        'redirect_to_domain',
        'cloudflare_zone_id',
        'delivery_date',
        'purchase_date',
        'expiry_date',
        'bot_open_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'delivery_date' => 'date',
            'purchase_date' => 'date',
            'expiry_date' => 'date',
            'bot_open_date' => 'date',
            'has_301_redirect' => 'boolean',
        ];
    }

    /**
     * Get status badge class for display
     */
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'active' => 'badge-success',
            'inactive' => 'badge-danger',
            'maintenance' => 'badge-warning',
            default => 'badge-secondary',
        };
    }

    /**
     * Get status display name
     */
    public function getStatusDisplayName(): string
    {
        return match($this->status) {
            'active' => 'Hoạt động',
            'inactive' => 'Không hoạt động',
            'maintenance' => 'Bảo trì',
            default => 'Không xác định',
        };
    }

    /**
     * Get category badge class for display
     */
    public function getCategoryBadgeClass(): string
    {
        return match($this->category) {
            'brand' => 'badge-primary',
            'phishing' => 'badge-danger',
            'key_nganh' => 'badge-success',
            'pbn' => 'badge-warning',
            default => 'badge-secondary',
        };
    }

    /**
     * Get category display name
     */
    public function getCategoryDisplayName(): string
    {
        return match($this->category) {
            'brand' => 'Brand',
            'phishing' => 'Phishing',
            'key_nganh' => 'Key ngành',
            'pbn' => 'PBN',
            default => 'Chưa phân loại',
        };
    }

    /**
     * Get the seoer user
     */
    public function seoerUser()
    {
        return $this->belongsTo(User::class, 'seoer_id');
    }

    /**
     * Get seoer name (backward compatibility)
     */
    public function getSeoerNameAttribute(): string
    {
        if ($this->seoerUser) {
            return $this->seoerUser->name;
        }
        return $this->seoer ?? 'Chưa phân công';
    }

    /**
     * Check Cloudflare 301 redirect status
     */
    public function checkCloudflare301Status(): array
    {
        $cloudflareService = new CloudflareService();
        $result = $cloudflareService->check301Redirect($this->name);
        
        // Update cloudflare_zone_id if we got it
        if ($result['zone_id'] && !$this->cloudflare_zone_id) {
            $this->update(['cloudflare_zone_id' => $result['zone_id']]);
        }
        
        return $result;
    }

    /**
     * Create 301 redirect via Cloudflare
     */
    public function create301Redirect(string $redirectTo): array
    {
        $cloudflareService = new CloudflareService();
        return $cloudflareService->create301Redirect($this->name, $redirectTo);
    }

    /**
     * Get cached 301 redirect status
     */
    public function get301StatusAttribute(): array
    {
        return cache()->remember(
            "website_301_status_{$this->id}",
            now()->addMinutes(10), // Cache for 10 minutes
            fn() => $this->checkCloudflare301Status()
        );
    }
}
