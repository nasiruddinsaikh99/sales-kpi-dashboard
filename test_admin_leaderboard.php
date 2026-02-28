<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';
$_SESSION['user_name'] = 'Admin';

require_once __DIR__ . '/src/Models/RankingUpload.php';
require_once __DIR__ . '/src/Models/RankingRecord.php';

$uploadModel = new RankingUpload();
$recordModel = new RankingRecord();

// Get latest upload
$currentUpload = $uploadModel->findLatest();
echo "Current Upload ID: " . $currentUpload['id'] . "\n";
echo "Upload Date: " . $currentUpload['upload_date'] . "\n";

// Get all rankings
$rankings = $recordModel->findByUploadId($currentUpload['id']);
echo "Total Rankings Retrieved: " . count($rankings) . "\n\n";

// Check if it's really all there
echo "Checking data integrity:\n";
echo "First 5 employees:\n";
for ($i = 0; $i < min(5, count($rankings)); $i++) {
    echo "  #" . $rankings[$i]['overall_rank'] . " - " . $rankings[$i]['employee_name'] .
         " (" . $rankings[$i]['total_attainment_pct'] . "%)\n";
}

if (count($rankings) > 10) {
    echo "\nLast 5 employees:\n";
    for ($i = max(0, count($rankings) - 5); $i < count($rankings); $i++) {
        echo "  #" . $rankings[$i]['overall_rank'] . " - " . $rankings[$i]['employee_name'] .
             " (" . $rankings[$i]['total_attainment_pct'] . "%)\n";
    }
}

// Get stats
$stats = $recordModel->getLeaderboardStats($currentUpload['id']);
echo "\nLeaderboard Statistics:\n";
echo "  Total Agents: " . $stats['total_agents'] . "\n";
echo "  Average: " . number_format($stats['avg_attainment'], 2) . "%\n";

// Prepare the leaderboard data exactly as the controller does
$leaderboardData = [
    'upload' => $currentUpload,
    'rankings' => $rankings,
    'stats' => $stats,
    'currentUserRank' => null,
    'currentUserData' => null,
    'isAdmin' => true
];

echo "\nLeaderboard Data Array:\n";
echo "  Rankings count: " . count($leaderboardData['rankings']) . "\n";
echo "  isAdmin: " . ($leaderboardData['isAdmin'] ? 'true' : 'false') . "\n";

// Now let's check what happens in the view
echo "\n--- Simulating View Rendering ---\n";

// Check podium data
$podiumRanks = array_slice($rankings, 0, 3);
echo "Podium (top 3): " . count($podiumRanks) . " entries\n";

// Check if the full array is still intact
echo "Rankings array after podium slice: " . count($rankings) . " entries\n";

// Look for any filtering that might be happening
$displayed = 0;
foreach ($rankings as $rank) {
    $displayed++;
}
echo "Total that would be displayed in loop: $displayed\n";