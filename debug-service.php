<?php

// Script debug để kiểm tra dữ liệu Service
// Chạy: php debug-service.php

require_once 'vendor/autoload.php';

use App\Models\Service;

// Load Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DEBUG SERVICE DATA ===\n";

// Lấy service ID 10 (như trong hình)
$service = Service::find(10);

if (!$service) {
    echo "Service ID 10 không tồn tại!\n";
    exit;
}

echo "Service ID: " . $service->id . "\n";
echo "Service Name: " . $service->name . "\n";
echo "Service Type: " . ($service->type ?? 'NULL') . "\n";
echo "Service Type Display: " . $service->getTypeDisplayName() . "\n";
echo "Is Active: " . ($service->is_active ? 'true' : 'false') . "\n";
echo "Approval Status: " . ($service->approval_status ?? 'NULL') . "\n";
echo "Approval Status Display: " . $service->getApprovalStatusDisplayName() . "\n";
echo "Traffic: " . ($service->traffic ?? 'NULL') . "\n";
echo "Keywords: " . ($service->keywords_string ?? 'NULL') . "\n";
echo "Keywords Array: " . json_encode($service->keywords) . "\n";

echo "\n=== ALL SERVICES ===\n";
$services = Service::all();
foreach ($services as $s) {
    echo "ID: {$s->id}, Name: {$s->name}, Type: " . ($s->type ?? 'NULL') . ", Active: " . ($s->is_active ? 'true' : 'false') . "\n";
}
