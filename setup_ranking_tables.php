<?php

require_once __DIR__ . '/config/database.php';

try {
    $pdo = Database::connect();
    echo "Connected to MySQL database.\n";

    // 1. Ranking Uploads Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS ranking_uploads (
        id INT AUTO_INCREMENT PRIMARY KEY,
        upload_date DATE NOT NULL,
        uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        uploaded_by_user_id INT NOT NULL,
        FOREIGN KEY (uploaded_by_user_id) REFERENCES users(id),
        INDEX idx_upload_date (upload_date DESC)
    ) ENGINE=InnoDB");
    echo "Table 'ranking_uploads' created or exists.\n";

    // 2. Ranking Records Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS ranking_records (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ranking_upload_id INT NOT NULL,
        user_id INT NULL,
        employee_name VARCHAR(255) NOT NULL,
        overall_rank INT NOT NULL,
        total_attainment_pct DECIMAL(6,2),
        hpu_attainment_pct DECIMAL(6,2),
        vhi_conv_attainment_pct DECIMAL(6,2),
        upg_conv_attainment_pct DECIMAL(6,2),
        csga_attainment_pct DECIMAL(6,2),
        vmp_take_attainment_pct DECIMAL(6,2),
        perks_attainment_pct DECIMAL(6,2),
        traffic_gp_cust_attainment_pct DECIMAL(6,2),
        FOREIGN KEY (ranking_upload_id) REFERENCES ranking_uploads(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
        INDEX idx_rank (ranking_upload_id, overall_rank),
        INDEX idx_user (user_id)
    ) ENGINE=InnoDB");
    echo "Table 'ranking_records' created or exists.\n";

    echo "\nRanking database setup completed successfully.\n";
    echo "Run this script to create the leaderboard tables.\n";

} catch (PDOException $e) {
    echo "SQL Error: " . $e->getMessage() . "\n";
}