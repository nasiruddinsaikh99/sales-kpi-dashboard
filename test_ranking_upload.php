<?php

require_once __DIR__ . '/src/Models/RankingUpload.php';
require_once __DIR__ . '/src/Models/RankingRecord.php';
require_once __DIR__ . '/src/Models/User.php';

echo "Testing Ranking Upload System\n";
echo "==============================\n\n";

// Read the sample CSV file
$csvFile = '/var/www/dev.syntrex.io/sales-kpi-dashboard/Employee Performance Ranker as of 02.16.2026.csv';

if (!file_exists($csvFile)) {
    echo "Error: Sample CSV file not found at $csvFile\n";
    exit(1);
}

$handle = fopen($csvFile, 'r');
if (!$handle) {
    echo "Error: Cannot open CSV file\n";
    exit(1);
}

// Read header row
$headers = fgetcsv($handle);
echo "CSV Headers found:\n";
foreach ($headers as $index => $header) {
    echo "  [$index] => $header\n";
}
echo "\n";

// Process and create upload
$uploadModel = new RankingUpload();
$recordModel = new RankingRecord();
$userModel = new User();

// Create upload record (use admin user ID 1 for testing)
$uploadDate = date('Y-m-d'); // Use today's date
$uploadId = $uploadModel->create($uploadDate, 1);
echo "Created upload record ID: $uploadId for date: $uploadDate\n\n";

// Process data rows
$importCount = 0;
$notFoundAgents = [];

while (($row = fgetcsv($handle)) !== FALSE) {
    if (count($row) < 2) continue;

    $employeeName = trim($row[0]);
    if (empty($employeeName) || stripos($employeeName, 'no commission') !== false) {
        continue;
    }

    // Try to match to a user
    $user = $userModel->findByName($employeeName);
    $userId = $user ? $user['id'] : null;

    if (!$userId) {
        $notFoundAgents[] = $employeeName;
    }

    // Create record
    $recordData = [
        'ranking_upload_id' => $uploadId,
        'user_id' => $userId,
        'employee_name' => $employeeName,
        'overall_rank' => intval($row[1]),
        'total_attainment_pct' => floatval(str_replace('%', '', $row[2])),
        'hpu_attainment_pct' => floatval(str_replace('%', '', $row[3])),
        'vhi_conv_attainment_pct' => floatval(str_replace('%', '', $row[4])),
        'upg_conv_attainment_pct' => floatval(str_replace('%', '', $row[5])),
        'csga_attainment_pct' => floatval(str_replace('%', '', $row[6])),
        'vmp_take_attainment_pct' => floatval(str_replace('%', '', $row[7])),
        'perks_attainment_pct' => floatval(str_replace('%', '', $row[8])),
        'traffic_gp_cust_attainment_pct' => floatval(str_replace('%', '', $row[9]))
    ];

    try {
        $recordModel->create($recordData);
        $importCount++;
        if ($importCount <= 5) {
            echo "Imported: {$employeeName} (Rank #{$recordData['overall_rank']}, Total: {$recordData['total_attainment_pct']}%)\n";
        }
    } catch (Exception $e) {
        echo "Error importing $employeeName: " . $e->getMessage() . "\n";
    }
}

fclose($handle);

echo "\n==============================\n";
echo "Import Complete!\n";
echo "Total records imported: $importCount\n";
echo "Agents not found in system: " . count($notFoundAgents) . "\n";

if (count($notFoundAgents) > 0) {
    echo "\nFirst 10 unmatched agents:\n";
    foreach (array_slice($notFoundAgents, 0, 10) as $name) {
        echo "  - $name\n";
    }
}

// Get and display leaderboard stats
$stats = $recordModel->getLeaderboardStats($uploadId);
echo "\n==============================\n";
echo "Leaderboard Statistics:\n";
echo "  Total Agents: " . $stats['total_agents'] . "\n";
echo "  Team Average: " . number_format($stats['avg_attainment'], 2) . "%\n";
echo "  Above 100%: " . $stats['above_hundred_count'] . " agents\n";
echo "  Top Score: " . number_format($stats['max_attainment'], 2) . "%\n";
echo "  Lowest Score: " . number_format($stats['min_attainment'], 2) . "%\n";

// Get top 3 performers
$topPerformers = $recordModel->findTopPerformers($uploadId, 3);
echo "\nTop 3 Performers:\n";
foreach ($topPerformers as $performer) {
    echo "  #{$performer['overall_rank']} - {$performer['employee_name']}: {$performer['total_attainment_pct']}%\n";
}

echo "\n✅ Ranking system test completed successfully!\n";
echo "You can now visit:\n";
echo "  Admin: http://your-domain/sales-kpi-dashboard/admin/rankings\n";
echo "  Admin Leaderboard: http://your-domain/sales-kpi-dashboard/admin/leaderboard\n";
echo "  Agent Leaderboard: http://your-domain/sales-kpi-dashboard/agent/leaderboard\n";