<?php

class Upload {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::connect();
    }

    public function create($data) {
        $sql = "INSERT INTO uploads (batch_name, for_month, status) VALUES (:batch_name, :for_month, 'active')";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'batch_name' => $data['batch_name'],
            'for_month' => $data['for_month']
        ]);
        return $this->pdo->lastInsertId();
    }

    public function findAll() {
        $sql = "SELECT * FROM uploads ORDER BY uploaded_at DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }
    
    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM uploads WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    public function delete($id) {
        // ON DELETE CASCADE takes care of records
        $stmt = $this->pdo->prepare("DELETE FROM uploads WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }
}
