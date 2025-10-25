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
        $token = config('services.cloudflare.api_token');
        
        if (empty($token)) {
            throw new \Exception('Cloudflare API token is not configured. Please set CLOUDFLARE_API_TOKEN in .env or config/services.php');
        }
        
        $this->apiToken = $token;
    }

    /**
     * Get zone ID by domain name (only active zones)
     */
    public function getZoneId(string $domain): ?string
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Content-Type' => 'application/json',
            ])->get($this->baseUrl . '/zones', [
                'name' => $this->extractDomain($domain),
                'status' => 'active'  // Only get active zones, not movie or other statuses
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['result'][0]['id'])) {
                    $zoneId = $data['result'][0]['id'];
                    $zoneStatus = $data['result'][0]['status'] ?? 'unknown';
                    Log::info("Found active zone ID: {$zoneId} for domain: {$domain} with status: {$zoneStatus}");
                    return $zoneId;
                }
            }

            Log::warning('Cloudflare API: Could not get active zone ID for domain: ' . $domain);
            return null;
        } catch (\Exception $e) {
            Log::error('Cloudflare API Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all zones for a domain and prioritize active ones
     */
    public function getZonesForDomain(string $domain): array
    {
        try {
            $cleanDomain = $this->extractDomain($domain);
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Content-Type' => 'application/json',
            ])->get($this->baseUrl . '/zones', [
                'name' => $cleanDomain,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $zones = $data['result'] ?? [];
                
                // Sort zones: active first, then others
                usort($zones, function($a, $b) {
                    if ($a['status'] === 'active' && $b['status'] !== 'active') return -1;
                    if ($a['status'] !== 'active' && $b['status'] === 'active') return 1;
                    return 0;
                });
                
                Log::info("Found " . count($zones) . " zones for domain: {$cleanDomain}");
                foreach ($zones as $zone) {
                    Log::info("Zone ID: {$zone['id']}, Status: {$zone['status']}");
                }
                
                return $zones;
            }

            Log::warning('Cloudflare API: Could not get zones for domain: ' . $domain);
            return [];
        } catch (\Exception $e) {
            Log::error('Cloudflare API Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Check if domain has 301 redirect rules (prioritize active zones)
     */
    public function check301Redirect(string $domain): array
    {
        try {
            $cleanDomain = $this->extractDomain($domain);
            Log::info("Checking 301 redirect for domain: {$cleanDomain}");
            
            // Get all zones for this domain, sorted by status (active first)
            $zones = $this->getZonesForDomain($cleanDomain);
            if (empty($zones)) {
                Log::warning("No zones found for domain: {$cleanDomain}");
                return [
                    'has_redirect' => false,
                    'redirect_to' => null,
                    'error' => "Không tìm thấy zone cho domain: {$cleanDomain}",
                    'zone_id' => null
                ];
            }

            // Try each zone starting with active ones
            foreach ($zones as $zone) {
                $zoneId = $zone['id'];
                $zoneStatus = $zone['status'];
                Log::info("Checking zone ID: {$zoneId} with status: {$zoneStatus}");
                
                $result = $this->check301RedirectForZone($zoneId, $cleanDomain);
                if ($result['has_redirect']) {
                    Log::info("Found 301 redirect in zone {$zoneId} (status: {$zoneStatus})");
                    return array_merge($result, [
                        'zone_id' => $zoneId,
                        'zone_status' => $zoneStatus
                    ]);
                }
            }

            // No redirect found in any zone
            Log::info("No 301 redirect found in any zone for domain: {$cleanDomain}");
            return [
                'has_redirect' => false,
                'redirect_to' => null,
                'error' => null,
                'zone_id' => $zones[0]['id'] ?? null,
                'zone_status' => $zones[0]['status'] ?? null,
                'debug' => [
                    'domain' => $cleanDomain,
                    'checked_zones' => count($zones),
                    'zone_statuses' => array_column($zones, 'status')
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
     * Check 301 redirect for a specific zone
     */
    private function check301RedirectForZone(string $zoneId, string $cleanDomain): array
    {
        try {
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

            Log::info("Zone {$zoneId} result for {$cleanDomain}: has_redirect={$hasRedirect}, redirect_to={$redirectTo}");

            return [
                'has_redirect' => $hasRedirect,
                'redirect_to' => $redirectTo,
                'error' => null,
                'debug' => [
                    'domain' => $cleanDomain,
                    'zone_id' => $zoneId,
                    'checked_page_rules' => true,
                    'checked_redirect_rules' => true,
                    'checked_dns' => true
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Cloudflare 301 Check Error for zone ' . $zoneId . ': ' . $e->getMessage());
            return [
                'has_redirect' => false,
                'redirect_to' => null,
                'error' => 'Lỗi kết nối Cloudflare API: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create or update 301 redirect rule (prioritize active zones)
     */
    public function create301Redirect(string $domain, string $redirectTo): array
    {
        try {
            $cleanDomain = $this->extractDomain($domain);
            
            // Get all zones for this domain, sorted by status (active first)
            $zones = $this->getZonesForDomain($cleanDomain);
            if (empty($zones)) {
                return [
                    'success' => false,
                    'error' => 'Không tìm thấy zone cho domain này'
                ];
            }

            // Use the first zone (which should be active if available)
            $zoneId = $zones[0]['id'];
            $zoneStatus = $zones[0]['status'];
            Log::info("Using zone ID: {$zoneId} with status: {$zoneStatus} for creating 301 redirect");

            Log::info("Creating/updating 301 redirect for {$cleanDomain} to {$redirectTo}");

            // First, check for existing page rules (both www and non-www)
            $existingRules = [];
            $pageRulesResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Content-Type' => 'application/json',
            ])->timeout(30)->get($this->baseUrl . "/zones/{$zoneId}/pagerules");

            if ($pageRulesResponse->successful()) {
                $pageRules = $pageRulesResponse->json()['result'] ?? [];
                
                foreach ($pageRules as $rule) {
                    $targets = $rule['targets'] ?? [];
                    foreach ($targets as $target) {
                        $constraint = $target['constraint'] ?? [];
                        if (isset($constraint['value']) && 
                            (str_contains($constraint['value'], $cleanDomain) || 
                             str_contains($constraint['value'], "www.{$cleanDomain}"))) {
                            $existingRules[] = [
                                'id' => $rule['id'],
                                'pattern' => $constraint['value']
                            ];
                            Log::info("Found existing page rule: {$rule['id']} for pattern: {$constraint['value']}");
                        }
                    }
                }
            }

            Log::info("Found " . count($existingRules) . " existing rules for {$cleanDomain}");

            $updatedRules = [];
            $createdRules = [];
            $errors = [];

            if (count($existingRules) > 0) {
                // Update all existing rules
                foreach ($existingRules as $rule) {
                    $ruleData = [
                        'targets' => [
                            [
                                'target' => 'url',
                                'constraint' => [
                                    'operator' => 'matches',
                                    'value' => $rule['pattern'] // Keep original pattern
                                ]
                            ]
                        ],
                        'actions' => [
                            [
                                'id' => 'forwarding_url',
                                'value' => [
                                    'url' => rtrim($redirectTo, '/') . '/$1',
                                    'status_code' => 301
                                ]
                            ]
                        ],
                        'status' => 'active'
                    ];

                    Log::info("Updating page rule: {$rule['id']} with pattern: {$rule['pattern']}");
                    
                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $this->apiToken,
                        'Content-Type' => 'application/json',
                    ])->timeout(30)->put($this->baseUrl . "/zones/{$zoneId}/pagerules/{$rule['id']}", $ruleData);
                    
                    if ($response->successful()) {
                        $updatedRules[] = $rule['id'];
                        Log::info("Successfully updated rule: {$rule['id']}");
                    } else {
                        $error = $response->json()['errors'][0]['message'] ?? $response->body();
                        $errors[] = "Rule {$rule['id']}: {$error}";
                        Log::error("Failed to update rule {$rule['id']}: {$error}");
                    }
                }
            } else {
                // Create new rules for both www and non-www
                $patterns = [
                    "www.{$cleanDomain}/*",
                    "{$cleanDomain}/*"
                ];

                foreach ($patterns as $pattern) {
                    $ruleData = [
                        'targets' => [
                            [
                                'target' => 'url',
                                'constraint' => [
                                    'operator' => 'matches',
                                    'value' => $pattern
                                ]
                            ]
                        ],
                        'actions' => [
                            [
                                'id' => 'forwarding_url',
                                'value' => [
                                    'url' => rtrim($redirectTo, '/') . '/$1',
                                    'status_code' => 301
                                ]
                            ]
                        ],
                        'status' => 'active'
                    ];

                    Log::info("Creating new page rule for pattern: {$pattern}");
                    
                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $this->apiToken,
                        'Content-Type' => 'application/json',
                    ])->timeout(30)->post($this->baseUrl . "/zones/{$zoneId}/pagerules", $ruleData);
                    
                    if ($response->successful()) {
                        $result = $response->json()['result'] ?? [];
                        $createdRules[] = $result['id'] ?? 'unknown';
                        Log::info("Successfully created rule for pattern: {$pattern}");
                    } else {
                        $error = $response->json()['errors'][0]['message'] ?? $response->body();
                        $errors[] = "Pattern {$pattern}: {$error}";
                        Log::error("Failed to create rule for {$pattern}: {$error}");
                    }
                }
            }

            // Prepare response
            $totalRules = count($updatedRules) + count($createdRules);
            $hasErrors = count($errors) > 0;

            if ($totalRules > 0) {
                $action = count($updatedRules) > 0 ? 'updated' : 'created';
                $message = count($updatedRules) > 0 
                    ? "Đã cập nhật {$totalRules} page rules" 
                    : "Đã tạo {$totalRules} page rules";

                if ($hasErrors) {
                    $message .= " (có " . count($errors) . " lỗi)";
                }

                return [
                    'success' => true,
                    'action' => $action,
                    'updated_rules' => $updatedRules,
                    'created_rules' => $createdRules,
                    'errors' => $errors,
                    'message' => $message
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Không thể tạo/cập nhật rule 301: ' . implode(', ', $errors)
                ];
            }

        } catch (\Exception $e) {
            Log::error('Cloudflare Create/Update 301 Error: ' . $e->getMessage());
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
