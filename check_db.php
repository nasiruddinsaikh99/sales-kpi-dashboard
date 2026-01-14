<?php
$creds = [
    ['root', ''],
    ['root', 'root'],
    ['root', 'password'],
    ['admin', 'admin'],
    ['ubuntu', ''],
    ['phpmyadmin', 'Vynex@001']
];

foreach ($creds as $c) {
    try {
        $pdo = new PDO("mysql:host=localhost", $c[0], $c[1]);
        echo "SUCCESS: User: '{$c[0]}', Pass: '{$c[1]}'\n";
        exit(0);
    } catch (PDOException $e) {
        // echo "Failed: " . $e->getMessage() . "\n";
    }
}
echo "ALL FAILED\n";
