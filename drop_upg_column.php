<?php
require_once __DIR__ . '/config/database.php';

try {
    $pdo = Database::connect();
    echo "Connected to database.\n";

    // Check if column exists first
    $stmt = $pdo->query("DESCRIBE ranking_records");
    $columns = array_column($stmt->fetchAll(), 'Field');

    if (in_array('upg_conv_attainment_pct', $columns)) {
        $pdo->exec("ALTER TABLE ranking_records DROP COLUMN upg_conv_attainment_pct");
        echo "✓ Dropped upg_conv_attainment_pct column (not in new CSV format)\n";
    } else {
        echo "→ Column upg_conv_attainment_pct doesn't exist or already dropped\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}