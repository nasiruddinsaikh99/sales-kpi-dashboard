<?php
/**
 * Seed Demo Data for Charts
 * This script populates the database with realistic demo data for Nov 2025 and Dec 2025.
 */

require_once __DIR__ . '/config/database.php';

$pdo = Database::connect();

// Agent IDs to seed (skip admin ID 1)
$agentIds = [2, 3, 4, 5];

// Define months to seed
$months = [
    ['batch_name' => 'November 2025 Data', 'for_month' => '2025-11-01'],
    ['batch_name' => 'December 2025 Data', 'for_month' => '2025-12-01'],
];

// Agent financial profiles (base values, will be varied per month)
$agentProfiles = [
    2 => ['name' => 'Meenakshi Kaur', 'gp' => 12500, 'payout' => 3800],
    3 => ['name' => 'Shubham Bhandari', 'gp' => 9800, 'payout' => 2900],
    4 => ['name' => 'Antonio Mason', 'gp' => 15200, 'payout' => 4500],
    5 => ['name' => 'Matthew Wiseman', 'gp' => 11000, 'payout' => 3200],
];

echo "Starting demo data seed...\n";

foreach ($months as $index => $monthData) {
    // Check if upload already exists
    $stmt = $pdo->prepare("SELECT id FROM uploads WHERE for_month = ?");
    $stmt->execute([$monthData['for_month']]);
    $existingUpload = $stmt->fetch();

    if ($existingUpload) {
        echo "Data for {$monthData['for_month']} already exists. Skipping.\n";
        continue;
    }

    // Create upload record
    $stmt = $pdo->prepare("INSERT INTO uploads (batch_name, for_month, status) VALUES (?, ?, 'active')");
    $stmt->execute([$monthData['batch_name'], $monthData['for_month']]);
    $uploadId = $pdo->lastInsertId();
    echo "Created upload: {$monthData['batch_name']} (ID: $uploadId)\n";

    // Variation multiplier based on month (Nov slightly lower, Dec higher)
    $multiplier = ($index === 0) ? 0.92 : 1.08;

    foreach ($agentProfiles as $userId => $profile) {
        // Add some randomness (+/- 10%)
        $randomFactor = 0.9 + (mt_rand(0, 20) / 100);
        
        $grossProfit = $profile['gp'] * $multiplier * $randomFactor;
        $chargeback = $grossProfit * (mt_rand(5, 15) / 100); // 5-15% chargeback
        $netGp = $grossProfit - $chargeback;
        $totalSpiff = $netGp * 0.25; // 25% spiff
        $finalPayout = $profile['payout'] * $multiplier * $randomFactor;
        $flavorOfMonth = (mt_rand(0, 100) > 70) ? mt_rand(50, 200) : 0;

        $data = [
            'upload_id' => $uploadId,
            'user_id' => $userId,
            'agent_name_snapshot' => $profile['name'],
            'gross_profit' => round($grossProfit, 2),
            'chargeback' => round($chargeback, 2),
            'net_gp' => round($netGp, 2),
            'gp_spiff_qualified_pct' => mt_rand(60, 100) . '%',
            'total_gp_spiff_amt' => round($totalSpiff, 2),
            'gp_spiff_amt_accelerator' => round($totalSpiff * 0.1, 2),
            'payout' => round($finalPayout * 0.9, 2),
            'payout_cb' => round($chargeback * 0.5, 2),
            'final_payout' => round($finalPayout, 2),
            'qualifiers' => (mt_rand(0, 100) > 50) ? 'Yes' : 'No',
            'total_accelerators_pct' => mt_rand(5, 25) . '%',
            'flavor_of_month' => round($flavorOfMonth, 2),
            'fios_qty_sold' => mt_rand(5, 25),
            'vhi' => mt_rand(3, 15),
            'upgrade_quantity' => mt_rand(2, 12),
            'smt_ga' => mt_rand(10, 50),
            'smb_ga' => mt_rand(2, 10),
            'device_spiff' => round(mt_rand(100, 500) * $multiplier, 2),
        ];

        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO kpi_records ($columns) VALUES ($placeholders)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($data);
        echo "  - Inserted record for {$profile['name']}\n";
    }
}

echo "\n✅ Demo data seeding complete!\n";
