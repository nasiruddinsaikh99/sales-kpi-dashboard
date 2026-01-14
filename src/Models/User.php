<?php

class User {
    private $pdo;

    public static function createAgentIfNotExists($name) {
        $db = Database::connect();
        
        // Check if exists
        $stmt = $db->prepare("SELECT id FROM users WHERE name = :name LIMIT 1");
        $stmt->execute(['name' => $name]);
        $user = $stmt->fetch();

        if ($user) return $user['id'];

        // Create
        $email = strtolower(str_replace(' ', '.', $name)) . '@syntrex.placeholder';
        $hashed = password_hash('Welcome123', PASSWORD_BCRYPT); // Default password
        
        $stmt = $db->prepare("INSERT INTO users (name, email, password, role, created_at) VALUES (:name, :email, :pass, 'agent', NOW())");
        $stmt->execute(['name' => $name, 'email' => $email, 'pass' => $hashed]);
        
        return $db->lastInsertId();
    }

    public static function updatePassword($userId, $newPlainPassword) {
        $db = Database::connect();
        $hashed = password_hash($newPlainPassword, PASSWORD_BCRYPT);
        $stmt = $db->prepare("UPDATE users SET password = :pass WHERE id = :id");
        $stmt->execute(['pass' => $hashed, 'id' => $userId]);
    }

    public function __construct() {
        $this->pdo = Database::connect();
    }

    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    public function create($data) {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)";
        $stmt = $this->pdo->prepare($sql);
        
        $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role']
        ]);
        
        return $this->pdo->lastInsertId();
    }
    
    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
}
