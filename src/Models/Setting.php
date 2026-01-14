<?php

class Setting {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::connect();
    }

    public static function get($key, $default = null) {
        $instance = new self();
        $stmt = $instance->pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = :key");
        $stmt->execute(['key' => $key]);
        $value = $stmt->fetchColumn();
        return $value !== false ? $value : $default;
    }

    public static function set($key, $value) {
        $instance = new self();
        // Insert or Update (Upsert)
        $sql = "INSERT INTO settings (setting_key, setting_value) VALUES (:key, :value)
                ON DUPLICATE KEY UPDATE setting_value = :value_update";
        $stmt = $instance->pdo->prepare($sql);
        $stmt->execute([
            'key' => $key, 
            'value' => $value,
            'value_update' => $value
        ]);
    }
}
