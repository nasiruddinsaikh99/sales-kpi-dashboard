<?php

class Database {
    private static $pdo = null;

    // CONFIGURATION
    // Please update these values with your MySQL credentials
    private const DB_HOST = 'localhost';
    private const DB_NAME = 'sales_kpi';
    private const DB_USER = 'phpmyadmin'; 
    private const DB_PASS = 'Vynex@001';

    public static function connect() {
        if (self::$pdo === null) {
            try {
                $dsn = "mysql:host=" . self::DB_HOST . ";dbname=" . self::DB_NAME . ";charset=utf8mb4";
                self::$pdo = new PDO($dsn, self::DB_USER, self::DB_PASS);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                // If connection fails, check if it's because DB doesn't exist and try to create it (Local Dev convenience)
                if (strpos($e->getMessage(), "Unknown database") !== false) {
                    try {
                        $dsnNoDb = "mysql:host=" . self::DB_HOST . ";charset=utf8mb4";
                        $tempPdo = new PDO($dsnNoDb, self::DB_USER, self::DB_PASS);
                        $tempPdo->exec("CREATE DATABASE IF NOT EXISTS " . self::DB_NAME);
                        // Retry connection
                        $dsn = "mysql:host=" . self::DB_HOST . ";dbname=" . self::DB_NAME . ";charset=utf8mb4";
                        self::$pdo = new PDO($dsn, self::DB_USER, self::DB_PASS);
                        self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                        return self::$pdo;
                    } catch (PDOException $ex) {
                        die("Database connection failed. Please check config/database.php credentials. Error: " . $ex->getMessage());
                    }
                }
                die("Database connection failed. Please check config/database.php credentials. Error: " . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}
