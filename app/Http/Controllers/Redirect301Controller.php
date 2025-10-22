<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Redirect301;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Redirect301Controller extends Controller
{
    private $cloudflareBaseUrl = 'https://api.cloudflare.com/client/v4';
    private $apiToken;

    public function __construct()
    {
        $this->apiToken = config('services.cloudflare.api_token');
    }

    public function index()
    {
        return view('redirects-301.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'domains' => 'required|string',
            'destination_url' => 'required|string',
            'include_www' => 'sometimes|boolean'
        ]);

        $rawDomains = array_filter(array_map('trim', explode("\n", $request->domains)));
        $includeWww = $request->boolean('include_www');
        $destinationUrl = $this->normalizeDestinationUrl($request->destination_url, $includeWww);

        // Clean and process domains
        $cleanedDomains = [];
        foreach ($rawDomains as $rawDomain) {
            $cleanedDomain = $this->cleanDomain($rawDomain);
            if (!empty($cleanedDomain) && !in_array($cleanedDomain, $cleanedDomains)) {
                $cleanedDomains[] = $cleanedDomain;
            }
        }

        if (empty($cleanedDomains)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'No valid domains found after cleaning'
                ]);
            }
            return back()->withErrors(['domains' => 'No valid domains found after cleaning']);
        }

        // Process domains with Cloudflare
        $processResult = $this->processDomainsWithCloudflare($cleanedDomains, $destinationUrl, $includeWww);
        $results = $processResult['results'];
        $successCount = count(array_filter($results, fn($r) => $r['status'] === 'success'));
        $errorCount = count($results) - $successCount;

        // Store in database
        $redirect = Redirect301::create([
            'domains' => implode("\n", $rawDomains),
            'destination_url' => $destinationUrl,
            'include_www' => $includeWww,
            'is_active' => true,
            'cloudflare_rules' => json_encode($processResult['cloudflare_rules'] ?? []),
            'success_count' => $successCount,
            'error_count' => $errorCount
        ]);

        // Check if request expects JSON (AJAX)
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'results' => $results,
                'destination_url' => $destinationUrl,
                'cleaned_domains' => $cleanedDomains,
                'original_domains' => $rawDomains,
                'summary' => [
                    'success_count' => $successCount,
                    'error_count' => $errorCount,
                    'total' => count($results)
                ]
            ]);
        }

        return back()->with('success', "Processed {$successCount} domains successfully, {$errorCount} errors");
    }

    private function makeCloudflareRequest($url, $method = 'GET', $data = null)
    {
        try {
            $headers = [
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Content-Type' => 'application/json'
            ];

            $httpClient = Http::withHeaders($headers)->timeout(30);

            switch (strtoupper($method)) {
                case 'POST':
                    $response = $httpClient->post($url, $data);
                    break;
                case 'DELETE':
                    $response = $httpClient->delete($url);
                    break;
                default:
                    $response = $httpClient->get($url);
                    break;
            }

            return [
                'success' => $response->successful(),
                'data' => $response->json(),
                'http_code' => $response->status()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'data' => ['error' => $e->getMessage()],
                'http_code' => 0,
                'error' => $e->getMessage()
            ];
        }
    }

    private function getZoneIdFromDomain($domain)
    {
        // Remove www if present to get root domain
        $rootDomain = preg_replace('/^www\./', '', $domain);
        
        $url = "{$this->cloudflareBaseUrl}/zones?name=" . urlencode($rootDomain);
        $response = $this->makeCloudflareRequest($url);
        
        if ($response['success'] && isset($response['data']['result']) && count($response['data']['result']) > 0) {
            return $response['data']['result'][0]['id'];
        }
        
        return null;
    }

    private function deleteExisting301Rules($zoneId)
    {
        $url = "{$this->cloudflareBaseUrl}/zones/{$zoneId}/pagerules";
        $response = $this->makeCloudflareRequest($url);
        
        if (!$response['success']) {
            return false;
        }
        
        $rules = $response['data']['result'] ?? [];
        $deletedCount = 0;
        
        foreach ($rules as $rule) {
            // Check if this is a 301 redirect rule
            if (isset($rule['actions']) && is_array($rule['actions'])) {
                foreach ($rule['actions'] as $action) {
                    if ($action['id'] === 'forwarding_url' && 
                        isset($action['value']['status_code']) && 
                        $action['value']['status_code'] === 301) {
                        
                        $deleteUrl = "{$this->cloudflareBaseUrl}/zones/{$zoneId}/pagerules/{$rule['id']}";
                        $this->makeCloudflareRequest($deleteUrl, 'DELETE');
                        $deletedCount++;
                        break;
                    }
                }
            }
        }
        
        return $deletedCount;
    }

    private function createRedirectRule($zoneId, $domain, $destinationUrl)
    {
        $ruleData = [
            'targets' => [
                [
                    'target' => 'url',
                    'constraint' => [
                        'operator' => 'matches',
                        'value' => "*{$domain}/*"
                    ]
                ]
            ],
            'actions' => [
                [
                    'id' => 'forwarding_url',
                    'value' => [
                        'url' => $destinationUrl . '/$2',
                        'status_code' => 301
                    ]
                ]
            ],
            'status' => 'active'
        ];
        
        $url = "{$this->cloudflareBaseUrl}/zones/{$zoneId}/pagerules";
        $response = $this->makeCloudflareRequest($url, 'POST', $ruleData);
        
        return $response;
    }

    private function purgeCloudflareCache($zoneId, $domain)
    {
        // Method 1: Try purging by URLs first
        $urls = [
            "https://{$domain}",
            "https://www.{$domain}",
            "http://{$domain}",
            "http://www.{$domain}"
        ];
        
        $purgeData = [
            'files' => $urls
        ];
        
        $url = "{$this->cloudflareBaseUrl}/zones/{$zoneId}/purge_cache";
        $response = $this->makeCloudflareRequest($url, 'POST', $purgeData);
        
        // If URL purge fails, try host-based purge
        if (!$response['success']) {
            $purgeData = [
                'hosts' => [
                    $domain,
                    'www.' . $domain
                ]
            ];
            
            $response = $this->makeCloudflareRequest($url, 'POST', $purgeData);
        }
        
        // If both fail, try purge everything as last resort
        if (!$response['success']) {
            $purgeData = [
                'purge_everything' => true
            ];
            
            $response = $this->makeCloudflareRequest($url, 'POST', $purgeData);
            
            if ($response['success']) {
                return [
                    'success' => true,
                    'method' => 'purge_everything',
                    'data' => $response['data']
                ];
            }
        }
        
        return [
            'success' => $response['success'],
            'method' => isset($purgeData['files']) ? 'files' : (isset($purgeData['hosts']) ? 'hosts' : 'purge_everything'),
            'data' => $response['data'],
            'error' => !$response['success'] ? ($response['data']['errors'][0]['message'] ?? 'Unknown error') : null
        ];
    }

    // Helper function for PHP 7.4 compatibility
    private function startsWith($haystack, $needle)
    {
        return substr($haystack, 0, strlen($needle)) === $needle;
    }

    // Function to clean and extract domain from any input
    private function cleanDomain($input)
    {
        $input = trim($input);
        
        // Remove protocol (http://, https://, ftp://, etc.)
        $input = preg_replace('/^[a-zA-Z]+:\/\//', '', $input);
        
        // Remove www prefix
        $input = preg_replace('/^www\./', '', $input);
        
        // Remove path, query parameters, and fragments
        $input = preg_replace('/\/.*$/', '', $input);
        $input = preg_replace('/\?.*$/', '', $input);
        $input = preg_replace('/#.*$/', '', $input);
        
        // Remove port numbers
        $input = preg_replace('/:\d+$/', '', $input);
        
        // Remove any remaining special characters except dots and hyphens
        $input = preg_replace('/[^a-zA-Z0-9.-]/', '', $input);
        
        // Remove trailing dots
        $input = rtrim($input, '.');
        
        // Convert to lowercase
        $input = strtolower($input);
        
        return $input;
    }

    // Function to normalize destination URL
    private function normalizeDestinationUrl($url, $includeWww = false)
    {
        // Clean the domain first
        $cleanedDomain = $this->cleanDomain($url);
        
        // Add www if requested and not already present
        if ($includeWww && !$this->startsWith($cleanedDomain, 'www.')) {
            $cleanedDomain = 'www.' . $cleanedDomain;
        }
        
        // Add https:// prefix
        return 'https://' . $cleanedDomain;
    }

    private function processDomainsWithCloudflare($domains, $destinationUrl, $includeWww = false)
    {
        $results = [];
        $cloudflareRules = [];
        
        // Group domains by zone
        $domainsByZone = [];
        
        foreach ($domains as $domain) {
            $zoneId = $this->getZoneIdFromDomain($domain);
            
            if (!$zoneId) {
                $results[] = [
                    'domain' => $domain,
                    'status' => 'error',
                    'message' => 'Không thể tìm thấy Cloudflare zone cho domain này'
                ];
                continue;
            }
            
            if (!isset($domainsByZone[$zoneId])) {
                $domainsByZone[$zoneId] = [];
            }
            $domainsByZone[$zoneId][] = $domain;
        }
        
        // Process each zone
        foreach ($domainsByZone as $zoneId => $zoneDomains) {
            try {
                // Delete existing 301 rules
                $deletedCount = $this->deleteExisting301Rules($zoneId);
                
                // Create new rules for each domain
                foreach ($zoneDomains as $domain) {
                    $response = $this->createRedirectRule($zoneId, $domain, $destinationUrl);
                    
                    if ($response['success'] && isset($response['data']['success']) && $response['data']['success']) {
                        // Purge cache for this domain
                        $cacheResponse = $this->purgeCloudflareCache($zoneId, $domain);
                        
                        $cacheStatus = "⚠️ Lỗi xóa cache";
                        if ($cacheResponse['success']) {
                            $method = $cacheResponse['method'];
                            $cacheStatus = "✅ Cache đã xóa ({$method})";
                        } else {
                            $error = $cacheResponse['error'] ?? 'Unknown error';
                            $cacheStatus = "⚠️ Lỗi xóa cache: {$error}";
                        }
                        
                        $wwwNote = $includeWww ? " (với www)" : "";
                        $results[] = [
                            'domain' => $domain,
                            'status' => 'success',
                            'message' => "301 redirect rule đã được tạo thành công → {$destinationUrl}{$wwwNote} | {$cacheStatus}"
                        ];
                        
                        $cloudflareRules[] = [
                            'domain' => $domain,
                            'status' => 'success',
                            'rule_id' => $response['data']['result']['id'] ?? null
                        ];
                    } else {
                        $errorMessage = 'Không thể tạo redirect rule';
                        if (isset($response['data']['errors'][0]['message'])) {
                            $errorMessage = $response['data']['errors'][0]['message'];
                        } elseif (isset($response['data']['error'])) {
                            $errorMessage = $response['data']['error'];
                        }
                        
                        $results[] = [
                            'domain' => $domain,
                            'status' => 'error',
                            'message' => $errorMessage
                        ];
                        
                        $cloudflareRules[] = [
                            'domain' => $domain,
                            'status' => 'error',
                            'error' => $errorMessage
                        ];
                    }
                }
            } catch (\Exception $e) {
                // If there's an error processing the zone, mark all domains in that zone as failed
                foreach ($zoneDomains as $domain) {
                    $results[] = [
                        'domain' => $domain,
                        'status' => 'error',
                        'message' => 'Lỗi xử lý zone: ' . $e->getMessage()
                    ];
                    
                    $cloudflareRules[] = [
                        'domain' => $domain,
                        'status' => 'error',
                        'error' => 'Lỗi xử lý zone: ' . $e->getMessage()
                    ];
                }
            }
        }
        
        return [
            'results' => $results,
            'cloudflare_rules' => $cloudflareRules
        ];
    }
}