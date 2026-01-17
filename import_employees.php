<?php
/**
 * Import Active Employees with Random Unique Passwords
 * Creates a markdown file with credentials
 */

require_once __DIR__ . '/config/database.php';

$pdo = Database::connect();
$csvFile = __DIR__ . '/data/Sales KPI documents - 2026.01.15 Active Employee EML List.csv';

if (!file_exists($csvFile)) {
    die("CSV file not found: $csvFile\n");
}

// Function to generate random 8-char password (uppercase + numbers only)
function generatePassword($length = 8) {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $password;
}

$handle = fopen($csvFile, 'r');
if (!$handle) {
    die("Could not open CSV file.\n");
}

// Skip malformed header rows
fgets($handle);
fgets($handle);
fgets($handle);

$users = [];
$inserted = 0;

echo "Starting employee import with unique passwords...\n\n";

while (($line = fgetcsv($handle)) !== false) {
    if (count($line) < 3) continue;
    
    $firstName = trim($line[0]);
    $lastName = trim($line[1]);
    $email = strtolower(trim($line[2]));
    
    if (empty($email)) continue;
    
    $fullName = $firstName . ' ' . $lastName;
    $plainPassword = generatePassword(8);
    $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);
    
    // Check if user already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        echo "SKIP: $email (already exists)\n";
        continue;
    }
    
    // Insert new user
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'agent')");
    $stmt->execute([$fullName, $email, $hashedPassword]);
    
    $users[] = [
        'name' => $fullName,
        'email' => $email,
        'password' => $plainPassword
    ];
    
    echo "ADD:  $fullName <$email> | Password: $plainPassword\n";
    $inserted++;
}

fclose($handle);

// Create markdown file with credentials
$mdContent = "# Employee Credentials\n\n";
$mdContent .= "> **Important**: This document contains sensitive login credentials. Handle with care.\n\n";
$mdContent .= "| # | Name | Email | Password |\n";
$mdContent .= "|---|------|-------|----------|\n";

$i = 1;
foreach ($users as $user) {
    $mdContent .= "| $i | {$user['name']} | {$user['email']} | `{$user['password']}` |\n";
    $i++;
}

$mdContent .= "\n---\n*Generated: " . date('Y-m-d H:i:s') . "*\n";

$mdFile = __DIR__ . '/data/employee_credentials.md';
file_put_contents($mdFile, $mdContent);

echo "\n========================================\n";
echo "Import Complete!\n";
echo "  Inserted: $inserted agents\n";
echo "  Credentials saved to: data/employee_credentials.md\n";
echo "========================================\n";
