<?php
/**
 * Test CSV Import with Client Data
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/Models/Upload.php';
require_once __DIR__ . '/src/Models/User.php';
require_once __DIR__ . '/src/Models/KpiRecord.php';
require_once __DIR__ . '/src/Controllers/UploadController.php';

echo "=== Testing CSV Import ===\n\n";

$csvFile = __DIR__ . '/data/Sales KPI documents - INDIVIDUAL.csv';

if (!file_exists($csvFile)) {
    die("CSV file not found: $csvFile\n");
}

// Read CSV
$handle = fopen($csvFile, 'r');
$rows = [];
while (($data = fgetcsv($handle, 0, ',')) !== false) {
    $rows[] = $data;
}
fclose($handle);

echo "CSV loaded: " . count($rows) . " rows\n";
echo "Headers: " . count($rows[0]) . " columns\n\n";

// Test column mapping
$controller = new UploadController();
$reflection = new ReflectionClass($controller);
$method = $reflection->getMethod('buildColumnMap');
$method->setAccessible(true);

$columnMap = $method->invoke($controller, $rows[0]);

echo "=== Column Mapping ===\n";
foreach ($columnMap as $dbField => $colIndex) {
    $header = $rows[0][$colIndex] ?? 'MISSING';
    echo sprintf("  %-40s => col[%d] = %s\n", $dbField, $colIndex, substr($header, 0, 35));
}

echo "\n=== Data Row Test ===\n";
if (count($rows) > 1) {
    $dataRow = $rows[1];
    $agentName = $dataRow[$columnMap['agent_name'] ?? 0] ?? 'N/A';
    echo "Agent Name: $agentName\n";
    
    // Check if user exists
    $userModel = new User();
    $user = $userModel->findByName($agentName);
    
    if ($user) {
        echo "User Found: ID={$user['id']}, Email={$user['email']}\n";
    } else {
        echo "USER NOT FOUND in database!\n";
    }
    
    echo "\nSample values:\n";
    $sampleFields = ['gross_profit', 'net_gp', 'final_payout', 'qualifiers', 'vhi'];
    foreach ($sampleFields as $field) {
        if (isset($columnMap[$field])) {
            $val = $dataRow[$columnMap[$field]] ?? 'N/A';
            echo "  $field: $val\n";
        }
    }
}

echo "\n=== Test Complete ===\n";
