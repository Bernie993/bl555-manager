<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CloudflareService
{
    private string $apiToken;
    private string $baseUrl = 'https://api.cloudflare.com/client/v4';

    public function __construct()
    {
        $this->apiToken = config('services.cloudflare.api_token');
    }

    /**
     * Get zone ID by domain name
     */
    public function getZoneId(string $domain): ?string
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Content-Type' => 'application/json',
            ])->get($this->baseUrl . '/zones', [
                'name' => $this->extractDomain($domain),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['result'][0]['id'])) {
                    return $data['result'][0]['id'];
                }
            }

            Log::warning('Cloudflare API: Could not get zone ID for domain: ' . $domain);
            return null;
        } catch (\Exception $e) {
            Log::error('Cloudflare API Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if domain has 301 redirect rules
     */
    public function check301Redirect(string $domain): array
    {
        try {
            $cleanDomain = $this->extractDomain($domain);
            Log::info("Checking 301 redirect for domain: {$cleanDomain}");
            
            $zoneId = $this->getZoneId($cleanDomain);
            if (!$zoneId) {
                Log::warning("Zone not found for domain: {$cleanDomain}");
                return [
                    'has_redirect' => false,
                    'redirect_to' => null,
                    'error' => "Không tìm thấy zone cho domain: {$cleanDomain}",
                    'zone_id' => null
                ];
            }

            Log::info("Found zone ID: {$zoneId} for domain: {$cleanDomain}");
            
            $redirectTo = null;
            $hasRedirect = false;

            // Check Page Rules for 301 redirects
            $pageRulesResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Content-Type' => 'application/json',
            ])->get($this->baseUrl . "/zones/{$zoneId}/pagerules");

            if ($pageRulesResponse->successful()) {
                $pageRules = $pageRulesResponse->json()['result'] ?? [];
                Log::info("Found " . count($pageRules) . " page rules for zone: {$zoneId}");
                
                foreach ($pageRules as $rule) {
                    if (isset($rule['actions']) && isset($rule['status']) && $rule['status'] === 'active') {
                        foreach ($rule['actions'] as $action) {
                            if ($action['id'] === 'forwarding_url' && 
                                isset($action['value']['status_code']) && 
                                $action['value']['status_code'] == 301) {
                                $hasRedirect = true;
                                $redirectTo = $action['value']['url'] ?? null;
                                Log::info("Found 301 page rule redirect to: {$redirectTo}");
                                break 2;
                            }
                        }
                    }
                }
            } else {
                Log::error("Page rules API failed: " . $pageRulesResponse->body());
            }

            // Check Redirect Rules (newer Cloudflare feature) if no page rule found
            if (!$hasRedirect) {
                $redirectRulesResponse = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiToken,
                    'Content-Type' => 'application/json',
                ])->get($this->baseUrl . "/zones/{$zoneId}/rulesets");

                if ($redirectRulesResponse->successful()) {
                    $rulesets = $redirectRulesResponse->json()['result'] ?? [];
                    Log::info("Found " . count($rulesets) . " rulesets for zone: {$zoneId}");
                    
                    foreach ($rulesets as $ruleset) {
                        if (isset($ruleset['rules']) && isset($ruleset['phase']) && 
                            $ruleset['phase'] === 'http_request_redirect') {
                            foreach ($ruleset['rules'] as $rule) {
                                if (isset($rule['action']) && $rule['action'] === 'redirect' &&
                                    isset($rule['action_parameters']['from_value']['status_code']) &&
                                    $rule['action_parameters']['from_value']['status_code'] == 301) {
                                    $hasRedirect = true;
                                    $redirectTo = $rule['action_parameters']['from_value']['target_url'] ?? null;
                                    Log::info("Found 301 redirect rule to: {$redirectTo}");
                                    break 2; // Break out of both foreach loops
                                }
                            }
                            if ($hasRedirect) break; // Break out of outer foreach if found
                        }
                    }
                } else {
                    Log::error("Redirect rules API failed: " . $redirectRulesResponse->body());
                }
            }

            // Also check DNS records for CNAME that might indicate redirect
            if (!$hasRedirect) {
                $dnsResponse = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiToken,
                    'Content-Type' => 'application/json',
                ])->get($this->baseUrl . "/zones/{$zoneId}/dns_records", [
                    'type' => 'CNAME',
                    'name' => $cleanDomain
                ]);

                if ($dnsResponse->successful()) {
                    $dnsRecords = $dnsResponse->json()['result'] ?? [];
                    foreach ($dnsRecords as $record) {
                        if (isset($record['content']) && $record['content'] !== $cleanDomain) {
                            // This might be a redirect via CNAME
                            $redirectTo = 'https://' . $record['content'];
                            $hasRedirect = true;
                            Log::info("Found potential CNAME redirect to: {$redirectTo}");
                            break;
                        }
                    }
                }
            }

            Log::info("Final result for {$cleanDomain}: has_redirect={$hasRedirect}, redirect_to={$redirectTo}");

            return [
                'has_redirect' => $hasRedirect,
                'redirect_to' => $redirectTo,
                'error' => null,
                'zone_id' => $zoneId,
                'debug' => [
                    'domain' => $cleanDomain,
                    'zone_id' => $zoneId,
                    'checked_page_rules' => true,
                    'checked_redirect_rules' => true,
                    'checked_dns' => true
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Cloudflare 301 Check Error for domain ' . $domain . ': ' . $e->getMessage());
            return [
                'has_redirect' => false,
                'redirect_to' => null,
                'error' => 'Lỗi kết nối Cloudflare API: ' . $e->getMessage(),
                'zone_id' => null
            ];
        }
    }

    /**
     * Create 301 redirect rule
     */
    public function create301Redirect(string $domain, string $redirectTo): array
    {
        try {
            $zoneId = $this->getZoneId($domain);
            if (!$zoneId) {
                return [
                    'success' => false,
                    'error' => 'Không tìm thấy zone cho domain này'
                ];
            }

            // Create page rule for 301 redirect
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . "/zones/{$zoneId}/pagerules", [
                'targets' => [
                    [
                        'target' => 'url',
                        'constraint' => [
                            'operator' => 'matches',
                            'value' => $domain . '/*'
                        ]
                    ]
                ],
                'actions' => [
                    [
                        'id' => 'forwarding_url',
                        'value' => [
                            'url' => $redirectTo . '/$1',
                            'status_code' => 301
                        ]
                    ]
                ],
                'status' => 'active'
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'rule_id' => $response->json()['result']['id'] ?? null
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Không thể tạo rule 301: ' . $response->body()
                ];
            }

        } catch (\Exception $e) {
            Log::error('Cloudflare Create 301 Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Lỗi kết nối Cloudflare API: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get all domains from all zones in Cloudflare account
     */
    public function getAllDomains(): array
    {
        try {
            $allDomains = [];
            $page = 1;
            $perPage = 50;
            
            do {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiToken,
                    'Content-Type' => 'application/json',
                ])->timeout(30)->get($this->baseUrl . '/zones', [
                    'page' => $page,
                    'per_page' => $perPage,
                    'status' => 'active'
                ]);

                if (!$response->successful()) {
                    Log::error('Cloudflare zones API failed: ' . $response->body());
                    break;
                }

                $data = $response->json();
                $zones = $data['result'] ?? [];
                
                foreach ($zones as $zone) {
                    $domainInfo = [
                        'domain' => $zone['name'],
                        'zone_id' => $zone['id'],
                        'status' => $zone['status'],
                        'paused' => $zone['paused'] ?? false,
                        'plan' => $zone['plan']['name'] ?? 'Unknown',
                        'created_on' => $zone['created_on'] ?? null,
                    ];
                    
                    // Skip 301 check during bulk sync to improve performance
                    // Will be checked individually later
                    $domainInfo['has_301_redirect'] = false;
                    $domainInfo['redirect_to'] = null;
                    
                    $allDomains[] = $domainInfo;
                }

                // Check if there are more pages
                $hasMore = isset($data['result_info']['total_pages']) && 
                          $page < $data['result_info']['total_pages'];
                $page++;
                
            } while ($hasMore);

            Log::info('Retrieved ' . count($allDomains) . ' domains from Cloudflare');
            return $allDomains;

        } catch (\Exception $e) {
            Log::error('Cloudflare get all domains error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Sync domains from Cloudflare to local database
     */
    public function syncDomainsToDatabase(): array
    {
        try {
            $cloudflaredomains = $this->getAllDomains();
            $syncResults = [
                'total_cf_domains' => count($cloudflaredomains),
                'new_domains' => 0,
                'updated_domains' => 0,
                'errors' => []
            ];

            foreach ($cloudflaredomains as $cfDomain) {
                try {
                    // Check if website already exists
                    $website = \App\Models\Website::where('name', $cfDomain['domain'])->first();
                    
                    if ($website) {
                        // Update existing website
                        $website->update([
                            'cloudflare_zone_id' => $cfDomain['zone_id'],
                            'has_301_redirect' => $cfDomain['has_301_redirect'],
                            'redirect_to_domain' => $cfDomain['redirect_to'],
                            'status' => $cfDomain['paused'] ? 'inactive' : 'active',
                        ]);
                        $syncResults['updated_domains']++;
                        Log::info("Updated existing website: {$cfDomain['domain']}");
                    } else {
                        // Create new website
                        \App\Models\Website::create([
                            'name' => $cfDomain['domain'],
                            'cloudflare_zone_id' => $cfDomain['zone_id'],
                            'has_301_redirect' => $cfDomain['has_301_redirect'],
                            'redirect_to_domain' => $cfDomain['redirect_to'],
                            'status' => $cfDomain['paused'] ? 'inactive' : 'active',
                            'seoer' => 'Chưa phân công',
                            'notes' => 'Đồng bộ từ Cloudflare vào ' . now()->format('d/m/Y H:i'),
                        ]);
                        $syncResults['new_domains']++;
                        Log::info("Created new website: {$cfDomain['domain']}");
                    }
                } catch (\Exception $e) {
                    $syncResults['errors'][] = "Lỗi xử lý domain {$cfDomain['domain']}: " . $e->getMessage();
                    Log::error("Error processing domain {$cfDomain['domain']}: " . $e->getMessage());
                }
            }

            return $syncResults;

        } catch (\Exception $e) {
            Log::error('Cloudflare sync domains error: ' . $e->getMessage());
            return [
                'total_cf_domains' => 0,
                'new_domains' => 0,
                'updated_domains' => 0,
                'errors' => ['Lỗi đồng bộ: ' . $e->getMessage()]
            ];
        }
    }

    /**
     * Extract domain from URL
     */
    private function extractDomain(string $url): string
    {
        $url = preg_replace('/^https?:\/\//', '', $url);
        $url = preg_replace('/^www\./', '', $url);
        $url = explode('/', $url)[0];
        return $url;
    }
}
