<?php
require_once 'config/database.php';

$pdo = Database::connect();
$userId = 372;

// Get existing record for this user
$stmt = $pdo->prepare('
    SELECT kr.*, u.for_month 
    FROM kpi_records kr 
    JOIN uploads u ON kr.upload_id = u.id 
    WHERE kr.user_id = ? 
    ORDER BY u.for_month DESC 
    LIMIT 1
');
$stmt->execute([$userId]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    echo "Found existing record for user 372\n";
    
    // Create 4 more months of data
    $months = [
        ['2025-12-01', 'December 2025 Data'],
        ['2025-11-01', 'November 2025 Data'],
        ['2025-10-01', 'October 2025 Data'],
        ['2025-09-01', 'September 2025 Data']
    ];
    
    foreach ($months as [$month, $batchName]) {
        // Check if upload for this month exists
        $check = $pdo->prepare('SELECT id FROM uploads WHERE for_month = ?');
        $check->execute([$month]);
        $uploadRow = $check->fetch();
        
        if ($uploadRow) {
            $uploadId = $uploadRow['id'];
            echo "Upload for $month already exists (ID: $uploadId)\n";
        } else {
            // Create upload entry
            $stmt = $pdo->prepare('INSERT INTO uploads (batch_name, for_month) VALUES (?, ?)');
            $stmt->execute([$batchName, $month]);
            $uploadId = $pdo->lastInsertId();
            echo "Created upload for $month (ID: $uploadId)\n";
        }
        
        // Check if kpi_record exists for this user and upload
        $check = $pdo->prepare('SELECT id FROM kpi_records WHERE user_id = ? AND upload_id = ?');
        $check->execute([$userId, $uploadId]);
        if ($check->fetch()) {
            echo "  KPI record for user $userId in $month already exists, skipping\n";
            continue;
        }
        
        // Generate with some variation (70-130% of existing values)
        $variation = (rand(70, 130) / 100);
        
        $sql = "INSERT INTO kpi_records (
            user_id, upload_id, agent_name_snapshot, gross_profit, net_gp, 
            gp_spiff_amt_accelerator, final_payout, chargeback, lateness, 
            gp_spiff_qualified_pct, total_accelerators_pct, qualifiers,
            priority_upgrade_pct, vhi_close_rate_pct, upgrade_conversion_pct,
            consumer_smt_ga_conversion_pct, vz_protect_pct, premium_unlimited_pct,
            device_spiff, flavor_of_month, take_rate_registered_perks,
            box_conversion, ready_go_setup_per_smt, total_gp_spiff_amt, payout
        ) VALUES (
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?,
            ?, ?, ?,
            ?, ?, ?,
            ?, ?, ?,
            ?, ?, ?, ?
        )";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $userId,
            $uploadId,
            $existing['agent_name_snapshot'] ?? 'User 372',
            round($existing['gross_profit'] * $variation, 2),
            round($existing['net_gp'] * $variation, 2),
            round($existing['gp_spiff_amt_accelerator'] * $variation, 2),
            round(($existing['final_payout'] ?? 0) * $variation, 2),
            round($existing['chargeback'] * (rand(0, 50) / 100), 2),
            round(($existing['lateness'] ?? -150) * (rand(50, 150) / 100), 2),
            $existing['gp_spiff_qualified_pct'] ?? '11%',
            rand(1, 5) . '.00%',
            $existing['qualifiers'] ?? 'Yes',
            rand(5, 15) . '.00%',
            rand(30, 60) . '.00%',
            rand(10, 20) . '.00%',
            rand(20, 35) . '.00%',
            rand(40, 60) . '.00%',
            rand(15, 25) . '.00%',
            rand(25, 75),
            rand(0, 100),
            rand(40, 70) / 100,
            rand(40, 60) . '.00%',
            rand(70, 90) . '.00%',
            round($existing['total_gp_spiff_amt'] * $variation, 2),
            round(($existing['payout'] ?? 0) * $variation, 2)
        ]);
        
        echo "  Inserted KPI record for user $userId in $month\n";
    }
    
    echo "\nDone! Inserted test data for user 372\n";
    
    // Show summary
    $stmt = $pdo->prepare('
        SELECT u.for_month, kr.gross_profit, kr.net_gp, kr.final_payout 
        FROM kpi_records kr 
        JOIN uploads u ON kr.upload_id = u.id 
        WHERE kr.user_id = ? 
        ORDER BY u.for_month DESC
    ');
    $stmt->execute([$userId]);
    echo "\nData for user 372:\n";
    while ($row = $stmt->fetch()) {
        echo "  {$row['for_month']}: GP={$row['gross_profit']}, Net={$row['net_gp']}, Payout={$row['final_payout']}\n";
    }
    
} else {
    echo "No existing record found for user 372\n";
}
