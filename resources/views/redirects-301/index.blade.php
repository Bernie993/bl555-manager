<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Hardcoded API token
const CLOUDFLARE_API_TOKEN = '85-ep1gsNUIO2hPNqxKIVaYQh7XfM_OHlhxi0wU8';

function makeCloudflareRequest($url, $method = 'GET', $data = null) {
    $headers = [
        'Authorization: Bearer ' . CLOUDFLARE_API_TOKEN,
        'Content-Type: application/json'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    } elseif ($method === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return [
            'success' => false,
            'data' => ['error' => $error],
            'http_code' => 0
        ];
    }
    
    return [
        'success' => $httpCode >= 200 && $httpCode < 300,
        'data' => json_decode($response, true),
        'http_code' => $httpCode
    ];
}

function getZoneIdFromDomain($domain) {
    // Remove www if present to get root domain
    $rootDomain = preg_replace('/^www\./', '', $domain);
    
    $url = "https://api.cloudflare.com/client/v4/zones?name=" . urlencode($rootDomain);
    $response = makeCloudflareRequest($url);
    
    if ($response['success'] && isset($response['data']['result']) && count($response['data']['result']) > 0) {
        return $response['data']['result'][0]['id'];
    }
    
    return null;
}

function deleteExisting301Rules($zoneId) {
    $url = "https://api.cloudflare.com/client/v4/zones/{$zoneId}/pagerules";
    $response = makeCloudflareRequest($url);
    
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
                    
                    $deleteUrl = "https://api.cloudflare.com/client/v4/zones/{$zoneId}/pagerules/{$rule['id']}";
                    makeCloudflareRequest($deleteUrl, 'DELETE');
                    $deletedCount++;
                    break;
                }
            }
        }
    }
    
    return $deletedCount;
}

function createRedirectRule($zoneId, $domain, $destinationUrl) {
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
    
    $url = "https://api.cloudflare.com/client/v4/zones/{$zoneId}/pagerules";
    $response = makeCloudflareRequest($url, 'POST', $ruleData);
    
    return $response;
}

function purgeCloudflareCache($zoneId, $domain) {
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
    
    $url = "https://api.cloudflare.com/client/v4/zones/{$zoneId}/purge_cache";
    $response = makeCloudflareRequest($url, 'POST', $purgeData);
    
    // If URL purge fails, try host-based purge
    if (!$response['success']) {
        $purgeData = [
            'hosts' => [
                $domain,
                'www.' . $domain
            ]
        ];
        
        $response = makeCloudflareRequest($url, 'POST', $purgeData);
    }
    
    // If both fail, try purge everything as last resort
    if (!$response['success']) {
        $purgeData = [
            'purge_everything' => true
        ];
        
        $response = makeCloudflareRequest($url, 'POST', $purgeData);
        
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
function startsWith($haystack, $needle) {
    return substr($haystack, 0, strlen($needle)) === $needle;
}

// Function to clean and extract domain from any input
function cleanDomain($input) {
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
function normalizeDestinationUrl($url, $includeWww = false) {
    // Clean the domain first
    $cleanedDomain = cleanDomain($url);
    
    // Add www if requested and not already present
    if ($includeWww && !startsWith($cleanedDomain, 'www.')) {
        $cleanedDomain = 'www.' . $cleanedDomain;
    }
    
    // Add https:// prefix
    return 'https://' . $cleanedDomain;
}

// Main processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['domains']) || !isset($input['destinationUrl'])) {
        echo json_encode([
            'success' => false,
            'error' => 'Missing required fields'
        ]);
        exit;
    }
    
    $rawDomains = $input['domains'];
    $includeWww = $input['includeWww'] ?? false;
    $destinationUrl = normalizeDestinationUrl($input['destinationUrl'], $includeWww);
    
    $results = [];
    
    // Clean and process domains
    $cleanedDomains = [];
    foreach ($rawDomains as $rawDomain) {
        $cleanedDomain = cleanDomain($rawDomain);
        if (!empty($cleanedDomain) && !in_array($cleanedDomain, $cleanedDomains)) {
            $cleanedDomains[] = $cleanedDomain;
        }
    }
    
    if (empty($cleanedDomains)) {
        echo json_encode([
            'success' => false,
            'error' => 'No valid domains found after cleaning'
        ]);
        exit;
    }
    
    // Group domains by zone
    $domainsByZone = [];
    
    foreach ($cleanedDomains as $domain) {
        $zoneId = getZoneIdFromDomain($domain);
        
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
            $deletedCount = deleteExisting301Rules($zoneId);
            
            // Create new rules for each domain
            foreach ($zoneDomains as $domain) {
                $response = createRedirectRule($zoneId, $domain, $destinationUrl);
                
                if ($response['success'] && isset($response['data']['success']) && $response['data']['success']) {
                    // Purge cache for this domain
                    $cacheResponse = purgeCloudflareCache($zoneId, $domain);
                    
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
                }
            }
        } catch (Exception $e) {
            // If there's an error processing the zone, mark all domains in that zone as failed
            foreach ($zoneDomains as $domain) {
                $results[] = [
                    'domain' => $domain,
                    'status' => 'error',
                    'message' => 'Lỗi xử lý zone: ' . $e->getMessage()
                ];
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'results' => $results,
        'destination_url' => $destinationUrl,
        'cleaned_domains' => $cleanedDomains,
        'original_domains' => $rawDomains
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed'
    ]);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cloudflare 301 Redirect Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .loading { display: none; }
        .result-success { background-color: #f0f9ff; border-color: #22c55e; }
        .result-error { background-color: #fef2f2; border-color: #ef4444; }
        .result-pending { background-color: #f0f9ff; border-color: #3b82f6; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen py-8">
    <div class="container mx-auto px-4 max-w-4xl">
        <div class="bg-white rounded-lg shadow-lg">
            <div class="p-6 border-b border-gray-200">
                <h1 class="text-2xl font-bold text-gray-900">Cloudflare 301 Redirect Manager</h1>
                <p class="text-gray-600 mt-2">Quản lý nhiều domain 301 redirect trong Cloudflare. Tool sẽ xóa các rule 301 cũ và tạo rule mới.</p>
            </div>
            
            <div class="p-6">
                <form id="redirectForm" class="space-y-6">
                    <div>
                        <label for="domains" class="block text-sm font-medium text-gray-700 mb-2">
                            Source Domains (mỗi domain một dòng)
                        </label>
                        <textarea 
                            id="domains" 
                            name="domains" 
                            rows="6" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="domaina.com&#10;domainb.com&#10;domainc.com"
                            required
                        ></textarea>
                        <p class="text-sm text-gray-500 mt-1">Nhập mỗi domain trên một dòng. Có thể bao gồm http/https, www, path - sẽ tự động làm sạch</p>
                    </div>

                    <div>
                        <label for="destinationUrl" class="block text-sm font-medium text-gray-700 mb-2">
                            Destination URL
                        </label>
                        <input 
                            type="text" 
                            id="destinationUrl" 
                            name="destinationUrl" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="example.com hoặc subdomain.example.com"
                            required
                        />
                        <p class="text-sm text-gray-500 mt-1">Nhập domain đích (có thể bao gồm http/https, www - sẽ tự động làm sạch và thêm https://)</p>
                    </div>

                    <div class="flex items-center">
                        <input 
                            type="checkbox" 
                            id="includeWww" 
                            name="includeWww" 
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        />
                        <label for="includeWww" class="ml-2 block text-sm text-gray-700">
                            Thêm www vào destination URL (ví dụ: example.com → www.example.com)
                        </label>
                    </div>

                    <button 
                        type="submit" 
                        class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                        id="submitBtn"
                    >
                        <span class="normal-text">Tạo 301 Redirects</span>
                        <span class="loading">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Đang xử lý...
                        </span>
                    </button>
                </form>

                <div id="results" class="mt-8 hidden">
                    <h3 class="text-lg font-semibold mb-4">Kết quả</h3>
                    <div id="resultsList" class="space-y-2"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById("redirectForm").addEventListener("submit", async (e) => {
  e.preventDefault()

  const submitBtn = document.getElementById("submitBtn")
  const normalText = submitBtn.querySelector(".normal-text")
  const loadingText = submitBtn.querySelector(".loading")
  const resultsDiv = document.getElementById("results")
  const resultsList = document.getElementById("resultsList")

  // Show loading state
  submitBtn.disabled = true
  normalText.style.display = "none"
  loadingText.style.display = "inline"

  // Clear previous results
  resultsDiv.classList.add("hidden")
  resultsList.innerHTML = ""

  // Get form data
  const domains = document
    .getElementById("domains")
    .value.split("\n")
    .map((domain) => domain.trim())
    .filter((domain) => domain.length > 0)

  const destinationUrl = document.getElementById("destinationUrl").value.trim()
  const includeWww = document.getElementById("includeWww").checked

  // Validate input
  if (domains.length === 0) {
    showError("Vui lòng nhập ít nhất một domain")
    resetButton()
    return
  }

  if (!destinationUrl) {
    showError("Vui lòng nhập URL đích")
    resetButton()
    return
  }

  console.log("Sending data:", { domains, destinationUrl, includeWww })

  try {
    const response = await fetch("api.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        domains: domains,
        destinationUrl: destinationUrl,
        includeWww: includeWww,
      }),
    })

    console.log("Response status:", response.status)

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }

    const data = await response.json()
    console.log("Response data:", data)

    if (data.success) {
      displayResults(data.results, data.destination_url, data.cleaned_domains, data.original_domains)
    } else {
      showError(data.error || "Có lỗi xảy ra")
    }
  } catch (error) {
    console.error("Error:", error)
    showError(`Lỗi kết nối: ${error.message}`)
  } finally {
    resetButton()
  }

  function resetButton() {
    submitBtn.disabled = false
    normalText.style.display = "inline"
    loadingText.style.display = "none"
  }
})

function displayResults(results, destinationUrl, cleanedDomains, originalDomains) {
  const resultsDiv = document.getElementById("results")
  const resultsList = document.getElementById("resultsList")

  resultsList.innerHTML = ""

  // Add summary
  const successCount = results.filter(r => r.status === 'success').length
  const errorCount = results.filter(r => r.status === 'error').length
  
  const summaryDiv = document.createElement("div")
  summaryDiv.className = "mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg"
  summaryDiv.innerHTML = `
    <div class="flex items-center gap-2 mb-2">
      <i class="fas fa-info-circle text-blue-500"></i>
      <strong>Tóm tắt kết quả:</strong>
    </div>
    <div class="text-sm space-y-1">
      <div>✅ Thành công: ${successCount} domains</div>
      <div>❌ Lỗi: ${errorCount} domains</div>
      <div>🎯 URL đích: ${destinationUrl}</div>
      <div>🧹 Domains đã làm sạch: ${cleanedDomains.join(', ')}</div>
    </div>
  `
  resultsList.appendChild(summaryDiv)

  // Show cleaned domains if different from original
  const hasChanges = originalDomains.some((orig, index) => {
    const cleaned = cleanedDomains[index]
    return cleaned && orig.toLowerCase().replace(/^https?:\/\/(www\.)?/, '').replace(/\/.*$/, '') !== cleaned
  })

  if (hasChanges) {
    const cleaningDiv = document.createElement("div")
    cleaningDiv.className = "mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg"
    cleaningDiv.innerHTML = `
      <div class="flex items-center gap-2 mb-2">
        <i class="fas fa-broom text-yellow-500"></i>
        <strong>Domains đã được làm sạch:</strong>
      </div>
      <div class="text-sm space-y-1">
        ${originalDomains.map((orig, index) => {
          const cleaned = cleanedDomains[index]
          if (cleaned && orig !== cleaned) {
            return `<div><span class="text-gray-500">${orig}</span> → <span class="text-green-600 font-medium">${cleaned}</span></div>`
          }
          return ''
        }).filter(Boolean).join('')}
      </div>
    `
    resultsList.appendChild(cleaningDiv)
  }

  // Add individual results
  results.forEach((result) => {
    const resultItem = document.createElement("div")
    resultItem.className = `p-4 rounded-lg border-2 result-${result.status}`

    const icon = getStatusIcon(result.status)

    resultItem.innerHTML = `
            <div class="flex items-center gap-2">
                ${icon}
                <div>
                    <strong>${result.domain}</strong>: ${result.message}
                </div>
            </div>
        `

    resultsList.appendChild(resultItem)
  })

  resultsDiv.classList.remove("hidden")
}

function getStatusIcon(status) {
  switch (status) {
    case "success":
      return '<i class="fas fa-check-circle text-green-500"></i>'
    case "error":
      return '<i class="fas fa-times-circle text-red-500"></i>'
    case "pending":
      return '<i class="fas fa-spinner fa-spin text-blue-500"></i>'
    default:
      return '<i class="fas fa-exclamation-circle text-yellow-500"></i>'
  }
}

function showError(message) {
  const resultsDiv = document.getElementById("results")
  const resultsList = document.getElementById("resultsList")

  resultsList.innerHTML = `
        <div class="p-4 rounded-lg border-2 result-error">
            <div class="flex items-center gap-2">
                <i class="fas fa-times-circle text-red-500"></i>
                <div><strong>Lỗi:</strong> ${message}</div>
            </div>
        </div>
    `

  resultsDiv.classList.remove("hidden")
}
    </script>
</body>
</html>