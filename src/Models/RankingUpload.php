<?php

require_once __DIR__ . '/../../config/database.php';

class RankingUpload {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::connect();
    }

    public function create($uploadDate, $uploadedByUserId) {
        $stmt = $this->pdo->prepare("
            INSERT INTO ranking_uploads (upload_date, uploaded_by_user_id)
            VALUES (?, ?)
        ");
        $stmt->execute([$uploadDate, $uploadedByUserId]);
        return $this->pdo->lastInsertId();
    }

    public function findAll() {
        $stmt = $this->pdo->query("
            SELECT ru.*, u.name as uploaded_by_name,
                   COUNT(rr.id) as record_count
            FROM ranking_uploads ru
            LEFT JOIN users u ON ru.uploaded_by_user_id = u.id
            LEFT JOIN ranking_records rr ON ru.id = rr.ranking_upload_id
            GROUP BY ru.id
            ORDER BY ru.upload_date DESC, ru.uploaded_at DESC
        ");
        return $stmt->fetchAll();
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("
            SELECT ru.*, u.name as uploaded_by_name
            FROM ranking_uploads ru
            LEFT JOIN users u ON ru.uploaded_by_user_id = u.id
            WHERE ru.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findLatest() {
        $stmt = $this->pdo->query("
            SELECT ru.*, u.name as uploaded_by_name
            FROM ranking_uploads ru
            LEFT JOIN users u ON ru.uploaded_by_user_id = u.id
            ORDER BY ru.upload_date DESC, ru.uploaded_at DESC
            LIMIT 1
        ");
        return $stmt->fetch();
    }

    public function findByDate($date) {
        $stmt = $this->pdo->prepare("
            SELECT ru.*, u.name as uploaded_by_name
            FROM ranking_uploads ru
            LEFT JOIN users u ON ru.uploaded_by_user_id = u.id
            WHERE ru.upload_date = ?
            ORDER BY ru.uploaded_at DESC
            LIMIT 1
        ");
        $stmt->execute([$date]);
        return $stmt->fetch();
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM ranking_uploads WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function existsForDate($date) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM ranking_uploads WHERE upload_date = ?
        ");
        $stmt->execute([$date]);
        return $stmt->fetchColumn() > 0;
    }
}