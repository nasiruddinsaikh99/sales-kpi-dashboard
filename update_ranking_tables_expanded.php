<?php

/**
 * Database migration script to update ranking tables for expanded CSV format (21 columns)
 * Run this script to add new columns for actual values and goals
 */

require_once __DIR__ . '/config/database.php';

try {
    $pdo = Database::connect();
    echo "Connected to MySQL database.\n";

    // First, check which columns exist
    $stmt = $pdo->query("DESCRIBE ranking_records");
    $existingColumns = array_column($stmt->fetchAll(), 'Field');

    // Add new columns to ranking_records table (without IF NOT EXISTS)
    $alterStatements = [];

    // Premium Unlimited (HPU) - percentages
    if (!in_array('hpu_actual_pct', $existingColumns)) {
        $alterStatements[] = "ALTER TABLE ranking_records ADD COLUMN hpu_actual_pct DECIMAL(6,2) NULL AFTER total_attainment_pct";
    }
    if (!in_array('hpu_goal_pct', $existingColumns)) {
        $alterStatements[] = "ALTER TABLE ranking_records ADD COLUMN hpu_goal_pct DECIMAL(6,2) NULL AFTER hpu_actual_pct";
    }

    // VZ VHI Conv - percentages
    if (!in_array('vhi_conv_actual_pct', $existingColumns)) {
        $alterStatements[] = "ALTER TABLE ranking_records ADD COLUMN vhi_conv_actual_pct DECIMAL(6,2) NULL AFTER hpu_attainment_pct";
    }
    if (!in_array('vhi_conv_goal_pct', $existingColumns)) {
        $alterStatements[] = "ALTER TABLE ranking_records ADD COLUMN vhi_conv_goal_pct DECIMAL(6,2) NULL AFTER vhi_conv_actual_pct";
    }

    // CSGA - percentages
    if (!in_array('csga_actual_pct', $existingColumns)) {
        $alterStatements[] = "ALTER TABLE ranking_records ADD COLUMN csga_actual_pct DECIMAL(6,2) NULL AFTER vhi_conv_attainment_pct";
    }
    if (!in_array('csga_goal_pct', $existingColumns)) {
        $alterStatements[] = "ALTER TABLE ranking_records ADD COLUMN csga_goal_pct DECIMAL(6,2) NULL AFTER csga_actual_pct";
    }

    // VMP Take Rate - decimal values (not percentages)
    if (!in_array('vmp_take_actual', $existingColumns)) {
        $alterStatements[] = "ALTER TABLE ranking_records ADD COLUMN vmp_take_actual DECIMAL(8,2) NULL AFTER csga_attainment_pct";
    }
    if (!in_array('vmp_take_goal', $existingColumns)) {
        $alterStatements[] = "ALTER TABLE ranking_records ADD COLUMN vmp_take_goal DECIMAL(8,2) NULL AFTER vmp_take_actual";
    }

    // VZ Perks Rate - decimal values (not percentages)
    if (!in_array('vz_perks_actual', $existingColumns)) {
        $alterStatements[] = "ALTER TABLE ranking_records ADD COLUMN vz_perks_actual DECIMAL(8,2) NULL AFTER vmp_take_attainment_pct";
    }
    if (!in_array('vz_perks_goal', $existingColumns)) {
        $alterStatements[] = "ALTER TABLE ranking_records ADD COLUMN vz_perks_goal DECIMAL(8,2) NULL AFTER vz_perks_actual";
    }

    // Traffic GP/Cust - currency values
    if (!in_array('traffic_gp_actual', $existingColumns)) {
        $alterStatements[] = "ALTER TABLE ranking_records ADD COLUMN traffic_gp_actual DECIMAL(10,2) NULL AFTER vz_perks_attainment_pct";
    }
    if (!in_array('traffic_gp_goal', $existingColumns)) {
        $alterStatements[] = "ALTER TABLE ranking_records ADD COLUMN traffic_gp_goal DECIMAL(10,2) NULL AFTER traffic_gp_actual";
    }

    // Rename perks_attainment_pct to vz_perks_attainment_pct if needed
    if (in_array('perks_attainment_pct', $existingColumns) && !in_array('vz_perks_attainment_pct', $existingColumns)) {
        $alterStatements[] = "ALTER TABLE ranking_records CHANGE COLUMN perks_attainment_pct vz_perks_attainment_pct DECIMAL(6,2) NULL";
    }

    foreach ($alterStatements as $sql) {
        try {
            $pdo->exec($sql);
            echo "✓ Executed: " . substr($sql, 0, 80) . "...\n";
        } catch (PDOException $e) {
            // Ignore "Duplicate column" errors
            if (strpos($e->getMessage(), 'Duplicate column') === false) {
                echo "✗ Error executing: " . substr($sql, 0, 80) . "...\n";
                echo "  Error: " . $e->getMessage() . "\n";
            } else {
                echo "→ Column already exists, skipping...\n";
            }
        }
    }

    // Drop the unused upg_conv columns (Upgrade Conversion not in new format)
    $dropStatements = [
        "ALTER TABLE ranking_records DROP COLUMN IF EXISTS upg_conv_attainment_pct"
    ];

    foreach ($dropStatements as $sql) {
        try {
            $pdo->exec($sql);
            echo "✓ Dropped unused column\n";
        } catch (PDOException $e) {
            echo "→ Column doesn't exist or already dropped\n";
        }
    }

    // Verify the new table structure
    $stmt = $pdo->query("DESCRIBE ranking_records");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "\n=== Updated Table Structure ===\n";
    echo "ranking_records table now has " . count($columns) . " columns:\n";
    foreach ($columns as $column) {
        echo "  - $column\n";
    }

    echo "\n✅ Database migration completed successfully!\n";
    echo "The ranking_records table has been updated to support the new 21-column CSV format.\n";

} catch (PDOException $e) {
    echo "❌ Migration Error: " . $e->getMessage() . "\n";
    exit(1);
}