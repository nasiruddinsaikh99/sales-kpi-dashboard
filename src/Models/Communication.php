<?php

require_once __DIR__ . '/../../config/database.php';

class Communication {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    /**
     * Create a new communication message
     */
    public function create($title, $message, $createdByUserId) {
        $stmt = $this->db->prepare(
            "INSERT INTO communications (title, message, created_by_user_id, created_at)
             VALUES (?, ?, ?, NOW())"
        );
        $stmt->execute([$title, $message, $createdByUserId]);
        return $this->db->lastInsertId();
    }

    /**
     * Get all communications ordered by created_at ASC (oldest to newest)
     */
    public function findAll() {
        $stmt = $this->db->query(
            "SELECT c.*, u.name as author_name
             FROM communications c
             LEFT JOIN users u ON c.created_by_user_id = u.id
             ORDER BY c.created_at ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get a single communication by ID
     */
    public function findById($id) {
        $stmt = $this->db->prepare(
            "SELECT c.*, u.name as author_name
             FROM communications c
             LEFT JOIN users u ON c.created_by_user_id = u.id
             WHERE c.id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Delete a communication
     */
    public function delete($id) {
        // Files will be cascade deleted by database
        // But we need to delete physical files from filesystem
        $files = $this->getFiles($id);
        foreach ($files as $file) {
            if (file_exists($file['filepath'])) {
                unlink($file['filepath']);
            }
        }

        $stmt = $this->db->prepare("DELETE FROM communications WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Attach a file to a communication
     */
    public function attachFile($communicationId, $filename, $originalFilename, $filepath, $filesize) {
        $stmt = $this->db->prepare(
            "INSERT INTO communication_files (communication_id, filename, original_filename, filepath, filesize, uploaded_at)
             VALUES (?, ?, ?, ?, ?, NOW())"
        );
        return $stmt->execute([$communicationId, $filename, $originalFilename, $filepath, $filesize]);
    }

    /**
     * Get all files for a communication
     */
    public function getFiles($communicationId) {
        $stmt = $this->db->prepare(
            "SELECT * FROM communication_files WHERE communication_id = ? ORDER BY uploaded_at ASC"
        );
        $stmt->execute([$communicationId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all files across all communications (for sidebar)
     */
    public function getAllFiles() {
        $stmt = $this->db->query(
            "SELECT cf.*, c.title as communication_title
             FROM communication_files cf
             LEFT JOIN communications c ON cf.communication_id = c.id
             ORDER BY cf.uploaded_at DESC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get a single file by ID
     */
    public function getFileById($fileId) {
        $stmt = $this->db->prepare("SELECT * FROM communication_files WHERE id = ?");
        $stmt->execute([$fileId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Mark a communication as read by a user
     */
    public function markAsRead($communicationId, $userId) {
        $stmt = $this->db->prepare(
            "INSERT IGNORE INTO communication_reads (communication_id, user_id, read_at)
             VALUES (?, ?, NOW())"
        );
        return $stmt->execute([$communicationId, $userId]);
    }

    /**
     * Mark all communications as read for a user
     */
    public function markAllAsRead($userId) {
        // Get all communication IDs
        $stmt = $this->db->query("SELECT id FROM communications");
        $commIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($commIds as $commId) {
            $this->markAsRead($commId, $userId);
        }
        return true;
    }

    /**
     * Get unread count for a user
     */
    public function getUnreadCount($userId) {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM communications c
             WHERE NOT EXISTS (
                 SELECT 1 FROM communication_reads cr
                 WHERE cr.communication_id = c.id AND cr.user_id = ?
             )"
        );
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Check if a communication is read by a user
     */
    public function isReadBy($communicationId, $userId) {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM communication_reads WHERE communication_id = ? AND user_id = ?"
        );
        $stmt->execute([$communicationId, $userId]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Get communications with file count
     */
    public function findAllWithFileCounts() {
        $stmt = $this->db->query(
            "SELECT c.*, u.name as author_name,
                    (SELECT COUNT(*) FROM communication_files cf WHERE cf.communication_id = c.id) as file_count
             FROM communications c
             LEFT JOIN users u ON c.created_by_user_id = u.id
             ORDER BY c.created_at ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
