<?php

/**
 * Test script to upload the new CSV format with 21 columns
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/Models/RankingUpload.php';
require_once __DIR__ . '/src/Models/RankingRecord.php';
require_once __DIR__ . '/src/Models/User.php';

// Simulate admin user session
$adminUserId = 1;

// CSV file path
$csvFile = __DIR__ . '/leaderboard-sample-data.csv';

if (!file_exists($csvFile)) {
    die("Error: CSV file not found: $csvFile\n");
}

// Parse CSV
$handle = fopen($csvFile, 'r');
if (!$handle) {
    die("Error: Cannot open CSV file\n");
}

// Read header row
$headers = fgetcsv($handle);
echo "Headers found: " . count($headers) . " columns\n";
echo "Headers: " . implode(', ', array_slice($headers, 0, 10)) . "...\n\n";

// Build column map using same logic as controller
$normalizedHeaders = array_map(function($h) {
    return strtolower(trim($h));
}, $headers);

$columnMap = [];

// Basic columns
$columnMap['employee_name'] = array_search('individual employee', $normalizedHeaders);
$columnMap['overall_rank'] = array_search('overall rank', $normalizedHeaders);
$columnMap['total_attainment_pct'] = array_search('total attainment', $normalizedHeaders);

// Find KPI triplets by position
// Premium Unlimited (HPU) - columns 3,4,5
$columnMap['hpu_actual_pct'] = 3;
$columnMap['hpu_goal_pct'] = 4;
$columnMap['hpu_attainment_pct'] = 5;

// VZ VHI Conv - columns 6,7,8
$columnMap['vhi_conv_actual_pct'] = 6;
$columnMap['vhi_conv_goal_pct'] = 7;
$columnMap['vhi_conv_attainment_pct'] = 8;

// CSGA - columns 9,10,11
$columnMap['csga_actual_pct'] = 9;
$columnMap['csga_goal_pct'] = 10;
$columnMap['csga_attainment_pct'] = 11;

// VMP Take Rate - columns 12,13,14
$columnMap['vmp_take_actual'] = 12;
$columnMap['vmp_take_goal'] = 13;
$columnMap['vmp_take_attainment_pct'] = 14;

// VZ Perks Rate - columns 15,16,17
$columnMap['vz_perks_actual'] = 15;
$columnMap['vz_perks_goal'] = 16;
$columnMap['vz_perks_attainment_pct'] = 17;

// Traffic GP/Cust - columns 18,19,20
$columnMap['traffic_gp_actual'] = 18;
$columnMap['traffic_gp_goal'] = 19;
$columnMap['traffic_gp_cust_attainment_pct'] = 20;

echo "Column mapping created:\n";
foreach ($columnMap as $field => $index) {
    echo "  $field => column $index (" . ($headers[$index] ?? 'N/A') . ")\n";
}
echo "\n";

// Helper functions
function cleanPercentage($value) {
    $value = str_replace(['%', ' '], '', $value);
    if (preg_match('/\(([0-9.]+)\)/', $value, $matches)) {
        $value = '-' . $matches[1];
    }
    return floatval($value);
}

function cleanDecimal($value) {
    $value = trim($value);
    if (preg_match('/\(([0-9.]+)\)/', $value, $matches)) {
        $value = '-' . $matches[1];
    }
    return floatval($value);
}

function cleanCurrency($value) {
    $value = str_replace(['$', ',', ' '], '', $value);
    if (preg_match('/\(([0-9.]+)\)/', $value, $matches)) {
        $value = '-' . $matches[1];
    }
    return floatval($value);
}

function cleanInteger($value) {
    return intval(preg_replace('/[^0-9]/', '', $value));
}

// Check for existing upload
$uploadDate = '2026-03-11'; // Today's date
$uploadModel = new RankingUpload();

// Delete existing upload for today if it exists
$existing = $uploadModel->findByDate($uploadDate);
if ($existing) {
    echo "Deleting existing upload for $uploadDate...\n";
    $uploadModel->delete($existing['id']);
}

// Create new upload
echo "Creating upload record for $uploadDate...\n";
$uploadId = $uploadModel->create($uploadDate, $adminUserId);
echo "Upload ID: $uploadId\n\n";

// Process data rows
$recordModel = new RankingRecord();
$userModel = new User();
$importCount = 0;
$sampleRecords = [];

while (($row = fgetcsv($handle)) !== FALSE) {
    if (count($row) < 2) continue;

    $employeeName = trim($row[$columnMap['employee_name']] ?? '');
    if (empty($employeeName)) continue;

    // Skip special rows
    if (stripos($employeeName, 'no commission') !== false) continue;

    // Try to match to a user
    $user = $userModel->findByName($employeeName);
    $userId = $user ? $user['id'] : null;

    // Prepare record data
    $recordData = [
        'ranking_upload_id' => $uploadId,
        'user_id' => $userId,
        'employee_name' => $employeeName,
        'overall_rank' => cleanInteger($row[$columnMap['overall_rank']] ?? '0'),
        'total_attainment_pct' => cleanPercentage($row[$columnMap['total_attainment_pct']] ?? '0'),

        // HPU triplet
        'hpu_actual_pct' => cleanPercentage($row[$columnMap['hpu_actual_pct']] ?? '0'),
        'hpu_goal_pct' => cleanPercentage($row[$columnMap['hpu_goal_pct']] ?? '0'),
        'hpu_attainment_pct' => cleanPercentage($row[$columnMap['hpu_attainment_pct']] ?? '0'),

        // VHI Conv triplet
        'vhi_conv_actual_pct' => cleanPercentage($row[$columnMap['vhi_conv_actual_pct']] ?? '0'),
        'vhi_conv_goal_pct' => cleanPercentage($row[$columnMap['vhi_conv_goal_pct']] ?? '0'),
        'vhi_conv_attainment_pct' => cleanPercentage($row[$columnMap['vhi_conv_attainment_pct']] ?? '0'),

        // CSGA triplet
        'csga_actual_pct' => cleanPercentage($row[$columnMap['csga_actual_pct']] ?? '0'),
        'csga_goal_pct' => cleanPercentage($row[$columnMap['csga_goal_pct']] ?? '0'),
        'csga_attainment_pct' => cleanPercentage($row[$columnMap['csga_attainment_pct']] ?? '0'),

        // VMP Take triplet
        'vmp_take_actual' => cleanDecimal($row[$columnMap['vmp_take_actual']] ?? '0'),
        'vmp_take_goal' => cleanDecimal($row[$columnMap['vmp_take_goal']] ?? '0'),
        'vmp_take_attainment_pct' => cleanPercentage($row[$columnMap['vmp_take_attainment_pct']] ?? '0'),

        // VZ Perks triplet
        'vz_perks_actual' => cleanDecimal($row[$columnMap['vz_perks_actual']] ?? '0'),
        'vz_perks_goal' => cleanDecimal($row[$columnMap['vz_perks_goal']] ?? '0'),
        'vz_perks_attainment_pct' => cleanPercentage($row[$columnMap['vz_perks_attainment_pct']] ?? '0'),

        // Traffic GP triplet
        'traffic_gp_actual' => cleanCurrency($row[$columnMap['traffic_gp_actual']] ?? '0'),
        'traffic_gp_goal' => cleanCurrency($row[$columnMap['traffic_gp_goal']] ?? '0'),
        'traffic_gp_cust_attainment_pct' => cleanPercentage($row[$columnMap['traffic_gp_cust_attainment_pct']] ?? '0')
    ];

    // Store sample for display
    if ($importCount < 3) {
        $sampleRecords[] = $recordData;
    }

    try {
        $recordModel->create($recordData);
        $importCount++;

        if ($importCount % 10 == 0) {
            echo ".";
        }
    } catch (Exception $e) {
        echo "\nError importing $employeeName: " . $e->getMessage() . "\n";
    }
}

fclose($handle);

echo "\n\n✅ Import completed!\n";
echo "Imported $importCount records.\n\n";

// Display sample records
echo "Sample records:\n";
foreach ($sampleRecords as $idx => $record) {
    echo "\nRecord " . ($idx + 1) . ": " . $record['employee_name'] . "\n";
    echo "  Rank: #" . $record['overall_rank'] . "\n";
    echo "  Total: " . $record['total_attainment_pct'] . "%\n";
    echo "  HPU: " . $record['hpu_actual_pct'] . "% (Goal: " . $record['hpu_goal_pct'] . "%) = " . $record['hpu_attainment_pct'] . "% attainment\n";
    echo "  Traffic GP: $" . $record['traffic_gp_actual'] . " (Goal: $" . $record['traffic_gp_goal'] . ") = " . $record['traffic_gp_cust_attainment_pct'] . "% attainment\n";
}

// Verify data in database
$records = $recordModel->findByUploadId($uploadId);
echo "\n\nDatabase verification:\n";
echo "Total records in database: " . count($records) . "\n";

if (count($records) > 0) {
    $firstRecord = $records[0];
    echo "\nFirst record in database:\n";
    echo "  Name: " . $firstRecord['employee_name'] . "\n";
    echo "  Rank: #" . $firstRecord['overall_rank'] . "\n";
    echo "  Total Attainment: " . $firstRecord['total_attainment_pct'] . "%\n";
    echo "  HPU Actual: " . $firstRecord['hpu_actual_pct'] . "%\n";
    echo "  HPU Goal: " . $firstRecord['hpu_goal_pct'] . "%\n";
    echo "  HPU Attainment: " . $firstRecord['hpu_attainment_pct'] . "%\n";
}

echo "\n✅ Test completed successfully!\n";