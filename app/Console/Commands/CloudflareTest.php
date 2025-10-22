<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CloudflareService;

class CloudflareTest extends Command
{
    protected $signature = 'cloudflare:test {domain?}';
    protected $description = 'Test Cloudflare API connection and 301 redirect detection';

    public function handle()
    {
        $domain = $this->argument('domain');
        
        if (!$domain) {
            $domain = $this->ask('Nhập domain để test (ví dụ: example.com)');
        }
        
        if (!$domain) {
            $this->error('Vui lòng nhập domain để test!');
            return 1;
        }

        $this->info("Đang test Cloudflare API với domain: {$domain}");
        $this->line('');

        try {
            $cloudflareService = new CloudflareService();
            
            // Test API connection
            $this->info('1. Kiểm tra kết nối API...');
            $zoneId = $cloudflareService->getZoneId($domain);
            
            if ($zoneId) {
                $this->info("   ✓ Kết nối thành công! Zone ID: {$zoneId}");
            } else {
                $this->error("   ✗ Không thể kết nối hoặc không tìm thấy zone cho domain này");
                return 1;
            }
            
            $this->line('');
            
            // Test 301 redirect detection
            $this->info('2. Kiểm tra trạng thái 301 redirect...');
            $result = $cloudflareService->check301Redirect($domain);
            
            if ($result['error']) {
                $this->error("   ✗ Lỗi: {$result['error']}");
                return 1;
            }
            
            if ($result['has_redirect']) {
                $this->info("   ✓ Domain có 301 redirect");
                $this->info("   → Redirect đến: {$result['redirect_to']}");
            } else {
                $this->warn("   ⚠ Domain không có 301 redirect");
            }
            
            $this->line('');
            $this->info('Test hoàn tất!');
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('Test thất bại: ' . $e->getMessage());
            return 1;
        }
    }
}