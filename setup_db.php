<?php

require_once __DIR__ . '/config/database.php';

try {
    $pdo = Database::connect();
    echo "Connected to MySQL database.\n";

    // 1. Users Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'agent') NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");
    echo "Table 'users' created or exists.\n";

    // 2. Uploads Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS uploads (
        id INT AUTO_INCREMENT PRIMARY KEY,
        batch_name VARCHAR(255) NOT NULL,
        for_month DATE NOT NULL,
        uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        status ENUM('active', 'archived') DEFAULT 'active'
    ) ENGINE=InnoDB");
    echo "Table 'uploads' created or exists.\n";

    // 3. KPI Records Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS kpi_records (
        id INT AUTO_INCREMENT PRIMARY KEY,
        upload_id INT NOT NULL,
        user_id INT NOT NULL,
        agent_name_snapshot VARCHAR(255),
        gross_profit DECIMAL(10,2),
        chargeback DECIMAL(10,2),
        net_gp DECIMAL(10,2),
        gp_spiff_qualified_pct VARCHAR(50),
        total_gp_spiff_amt DECIMAL(10,2),
        gp_spiff_amt_accelerator DECIMAL(10,2),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (upload_id) REFERENCES uploads(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");
    echo "Table 'kpi_records' created or exists.\n";

    // 4. Settings Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) UNIQUE NOT NULL,
        setting_value TEXT NOT NULL
    ) ENGINE=InnoDB");
    echo "Table 'settings' created or exists.\n";

    // Create Initial Admin User
    $adminEmail = 'admin@example.com';
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$adminEmail]);
    if ($stmt->fetchColumn() == 0) {
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute(['Super Admin', $adminEmail, $password, 'admin']);
        echo "Default Admin user created (admin@example.com / admin123).\n";
    } else {
        echo "Admin user already exists.\n";
    }

    // Default Settings
    $pdo->exec("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('history_visibility_months', '3')");

    echo "Database setup completed successfully.\n";

} catch (PDOException $e) {
    echo "SQL Error: " . $e->getMessage() . "\n";
}
