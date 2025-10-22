<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CloudflareService;

class TestSync extends Command
{
    protected $signature = 'test:sync {--limit=5 : Limit number of domains to sync}';
    protected $description = 'Test Cloudflare sync functionality';

    public function handle()
    {
        $limit = $this->option('limit');
        
        $this->info("Testing Cloudflare sync with limit: {$limit}");
        $this->line('');
        
        try {
            $cloudflareService = new CloudflareService();
            
            // Get domains
            $this->info('1. Getting domains from Cloudflare...');
            $domains = $cloudflareService->getAllDomains();
            
            if (empty($domains)) {
                $this->error('No domains found!');
                return 1;
            }
            
            $this->info("   ✓ Found " . count($domains) . " domains");
            
            // Limit for testing
            $testDomains = array_slice($domains, 0, $limit);
            $this->info("   → Testing with first {$limit} domains");
            
            $this->line('');
            $this->info('2. Syncing to database...');
            
            $stats = [
                'total' => 0,
                'new' => 0,
                'updated' => 0,
                'errors' => []
            ];
            
            foreach ($testDomains as $domain) {
                try {
                    $website = \App\Models\Website::where('name', $domain['domain'])->first();
                    
                    if ($website) {
                        $website->update([
                            'cloudflare_zone_id' => $domain['zone_id'],
                            'status' => $domain['paused'] ? 'inactive' : 'active',
                        ]);
                        $stats['updated']++;
                        $this->line("   ✓ Updated: {$domain['domain']}");
                    } else {
                        \App\Models\Website::create([
                            'name' => $domain['domain'],
                            'cloudflare_zone_id' => $domain['zone_id'],
                            'status' => $domain['paused'] ? 'inactive' : 'active',
                            'seoer' => 'Auto-sync',
                            'notes' => 'Synced from Cloudflare on ' . now()->format('d/m/Y H:i'),
                        ]);
                        $stats['new']++;
                        $this->line("   + Created: {$domain['domain']}");
                    }
                    
                    $stats['total']++;
                    
                } catch (\Exception $e) {
                    $stats['errors'][] = "Error with {$domain['domain']}: " . $e->getMessage();
                    $this->error("   ✗ Error: {$domain['domain']} - " . $e->getMessage());
                }
            }
            
            $this->line('');
            $this->info('3. Results:');
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Total processed', $stats['total']],
                    ['New websites', $stats['new']],
                    ['Updated websites', $stats['updated']],
                    ['Errors', count($stats['errors'])],
                ]
            );
            
            if (count($stats['errors']) > 0) {
                $this->line('');
                $this->warn('Errors encountered:');
                foreach ($stats['errors'] as $error) {
                    $this->line("  - {$error}");
                }
            }
            
            $this->line('');
            $this->info('Test completed successfully!');
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('Test failed: ' . $e->getMessage());
            return 1;
        }
    }
}