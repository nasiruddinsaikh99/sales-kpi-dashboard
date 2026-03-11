<?php

/**
 * Complete system test for the updated leaderboard with new CSV format
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/Models/RankingUpload.php';
require_once __DIR__ . '/src/Models/RankingRecord.php';

echo "=== Testing Complete Leaderboard System ===\n\n";

// 1. Check database structure
echo "1. Checking Database Structure:\n";
$pdo = Database::connect();
$stmt = $pdo->query("DESCRIBE ranking_records");
$columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "   Total columns in ranking_records: " . count($columns) . "\n";

$expectedColumns = [
    'hpu_actual_pct', 'hpu_goal_pct', 'hpu_attainment_pct',
    'vhi_conv_actual_pct', 'vhi_conv_goal_pct', 'vhi_conv_attainment_pct',
    'csga_actual_pct', 'csga_goal_pct', 'csga_attainment_pct',
    'vmp_take_actual', 'vmp_take_goal', 'vmp_take_attainment_pct',
    'vz_perks_actual', 'vz_perks_goal', 'vz_perks_attainment_pct',
    'traffic_gp_actual', 'traffic_gp_goal', 'traffic_gp_cust_attainment_pct'
];

$missingColumns = array_diff($expectedColumns, $columns);
if (empty($missingColumns)) {
    echo "   ✅ All expected columns are present\n";
} else {
    echo "   ❌ Missing columns: " . implode(', ', $missingColumns) . "\n";
}

// 2. Check for latest upload
echo "\n2. Checking Latest Upload:\n";
$uploadModel = new RankingUpload();
$latestUpload = $uploadModel->findLatest();

if ($latestUpload) {
    echo "   ✅ Found latest upload ID: " . $latestUpload['id'] . "\n";
    echo "   Upload date: " . $latestUpload['upload_date'] . "\n";

    // 3. Check records in latest upload
    echo "\n3. Checking Records in Latest Upload:\n";
    $recordModel = new RankingRecord();
    $records = $recordModel->findByUploadId($latestUpload['id']);

    echo "   Total records: " . count($records) . "\n";

    if (count($records) > 0) {
        // Check a sample record for new data
        $sample = $records[0];
        echo "\n   Sample Record (Rank #1):\n";
        echo "   Name: " . $sample['employee_name'] . "\n";
        echo "   Total Attainment: " . $sample['total_attainment_pct'] . "%\n";

        // Check if new fields have data
        $hasNewData = (
            !is_null($sample['hpu_actual_pct']) &&
            !is_null($sample['hpu_goal_pct']) &&
            !is_null($sample['traffic_gp_actual']) &&
            !is_null($sample['traffic_gp_goal'])
        );

        if ($hasNewData) {
            echo "\n   ✅ New fields have data:\n";
            echo "   - HPU: " . $sample['hpu_actual_pct'] . "% actual / " . $sample['hpu_goal_pct'] . "% goal = " . $sample['hpu_attainment_pct'] . "% attainment\n";
            echo "   - VHI Conv: " . $sample['vhi_conv_actual_pct'] . "% actual / " . $sample['vhi_conv_goal_pct'] . "% goal\n";
            echo "   - CSGA: " . $sample['csga_actual_pct'] . "% actual / " . $sample['csga_goal_pct'] . "% goal\n";
            echo "   - VMP Take: " . $sample['vmp_take_actual'] . " actual / " . $sample['vmp_take_goal'] . " goal\n";
            echo "   - VZ Perks: " . $sample['vz_perks_actual'] . " actual / " . $sample['vz_perks_goal'] . " goal\n";
            echo "   - Traffic GP: $" . $sample['traffic_gp_actual'] . " actual / $" . $sample['traffic_gp_goal'] . " goal\n";
        } else {
            echo "\n   ⚠️  New fields are empty - may need to re-upload CSV\n";
        }

        // 4. Check statistics
        echo "\n4. Checking Leaderboard Statistics:\n";
        $stats = $recordModel->getLeaderboardStats($latestUpload['id']);
        echo "   Average attainment: " . number_format($stats['avg_attainment'], 2) . "%\n";
        echo "   Agents above 100%: " . $stats['above_hundred_count'] . "\n";
        echo "   Max attainment: " . number_format($stats['max_attainment'], 2) . "%\n";
        echo "   Min attainment: " . number_format($stats['min_attainment'], 2) . "%\n";

        // 5. Check top performers
        echo "\n5. Top 3 Performers:\n";
        $topPerformers = $recordModel->findTopPerformers($latestUpload['id'], 3);
        foreach ($topPerformers as $idx => $performer) {
            echo "   #" . ($idx + 1) . ": " . $performer['employee_name'] . " - " . $performer['total_attainment_pct'] . "%\n";
        }

        echo "\n✅ System test completed successfully!\n";
        echo "\nTo view the leaderboard:\n";
        echo "- Admin: https://dev.syntrex.io/sales-kpi-dashboard/admin/leaderboard\n";
        echo "- Agent: https://dev.syntrex.io/sales-kpi-dashboard/agent/leaderboard\n";

    } else {
        echo "   ❌ No records found in latest upload\n";
    }
} else {
    echo "   ❌ No uploads found in database\n";
    echo "   Please upload a CSV file first\n";
}

echo "\n";