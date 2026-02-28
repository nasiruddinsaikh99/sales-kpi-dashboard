<?php

require_once __DIR__ . '/../Models/Communication.php';
require_once __DIR__ . '/../Models/User.php';

class CommunicationController {

    /**
     * Admin: View all communications
     */
    public function adminIndex() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /sales-kpi-dashboard/login');
            exit;
        }

        $commModel = new Communication();
        $communications = $commModel->findAllWithFileCounts();
        $allFiles = $commModel->getAllFiles();

        // Get files for each communication
        $communicationsWithFiles = [];
        foreach ($communications as $comm) {
            $comm['files'] = $commModel->getFiles($comm['id']);
            $communicationsWithFiles[] = $comm;
        }

        require __DIR__ . '/../Views/admin/communications.php';
    }

    /**
     * Admin: Store new communication
     */
    public function store() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /sales-kpi-dashboard/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /sales-kpi-dashboard/admin/communications');
            exit;
        }

        $title = trim($_POST['title'] ?? '');
        $message = $_POST['message'] ?? '';

        if (empty($title)) {
            header('Location: /sales-kpi-dashboard/admin/communications?error=missing_title');
            exit;
        }

        // Sanitize HTML from Quill editor
        // Message is optional, can be empty or just whitespace
        $message = trim($message) === '' ? '' : $this->sanitizeHtml($message);

        $commModel = new Communication();
        $communicationId = $commModel->create($title, $message, $_SESSION['user_id']);

        // Handle file uploads
        if (isset($_FILES['files']) && !empty($_FILES['files']['name'][0])) {
            $this->handleFileUploads($communicationId, $_FILES['files']);
        }

        header('Location: /sales-kpi-dashboard/admin/communications?success=created');
        exit;
    }

    /**
     * Admin: Delete a communication
     */
    public function delete() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /sales-kpi-dashboard/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /sales-kpi-dashboard/admin/communications');
            exit;
        }

        $commId = $_POST['communication_id'] ?? null;

        if ($commId) {
            $commModel = new Communication();
            $commModel->delete($commId);
            header('Location: /sales-kpi-dashboard/admin/communications?success=deleted');
        } else {
            header('Location: /sales-kpi-dashboard/admin/communications?error=invalid_id');
        }
        exit;
    }

    /**
     * Agent: View all communications (read-only)
     */
    public function agentIndex() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] === 'admin') {
            header('Location: /sales-kpi-dashboard/login');
            exit;
        }

        $commModel = new Communication();
        $communications = $commModel->findAllWithFileCounts();
        $allFiles = $commModel->getAllFiles();

        // Get files for each communication
        $communicationsWithFiles = [];
        foreach ($communications as $comm) {
            $comm['files'] = $commModel->getFiles($comm['id']);
            $communicationsWithFiles[] = $comm;
        }

        // Mark all as read when agent visits the page
        $commModel->markAllAsRead($_SESSION['user_id']);

        require __DIR__ . '/../Views/agent/communications.php';
    }

    /**
     * Download a file
     */
    public function downloadFile() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /sales-kpi-dashboard/login');
            exit;
        }

        $fileId = $_GET['file_id'] ?? null;

        if (!$fileId) {
            http_response_code(404);
            echo "File not found.";
            exit;
        }

        $commModel = new Communication();
        $file = $commModel->getFileById($fileId);

        if (!$file || !file_exists($file['filepath'])) {
            http_response_code(404);
            echo "File not found.";
            exit;
        }

        // Serve the file
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file['original_filename'] . '"');
        header('Content-Length: ' . filesize($file['filepath']));
        readfile($file['filepath']);
        exit;
    }

    /**
     * Handle multiple file uploads
     */
    private function handleFileUploads($communicationId, $files) {
        $uploadDir = __DIR__ . '/../../uploads/communications/';

        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Ensure directory is writable
        if (!is_writable($uploadDir)) {
            chmod($uploadDir, 0777);
        }

        $fileCount = count($files['name']);

        for ($i = 0; $i < $fileCount; $i++) {
            // Skip if no file uploaded
            if (empty($files['name'][$i])) {
                continue;
            }

            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $originalFilename = basename($files['name'][$i]);
                $tmpName = $files['tmp_name'][$i];
                $filesize = $files['size'][$i];

                // Validate file size (max 10MB)
                if ($filesize > 10 * 1024 * 1024) {
                    continue; // Skip files larger than 10MB
                }

                // Generate unique filename
                $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
                $filename = uniqid() . '_' . time() . '.' . $extension;
                $filepath = $uploadDir . $filename;

                // Move uploaded file
                if (move_uploaded_file($tmpName, $filepath)) {
                    $commModel = new Communication();
                    $commModel->attachFile($communicationId, $filename, $originalFilename, $filepath, $filesize);
                } else {
                    // Log error for debugging
                    error_log("Failed to move uploaded file: " . $originalFilename . " to " . $filepath);
                }
            } else {
                // Log upload error
                error_log("File upload error for " . $files['name'][$i] . ": " . $files['error'][$i]);
            }
        }
    }

    /**
     * Sanitize HTML content from Quill editor
     * Allows only safe tags used by Quill: p, strong, em, s, ol, ul, li, br
     */
    private function sanitizeHtml($html) {
        // First, strip all tags except allowed ones
        $allowedTags = '<p><strong><em><s><ol><ul><li><br>';
        $html = strip_tags($html, $allowedTags);

        // Remove all attributes (strip_tags doesn't remove attributes)
        // This prevents XSS through event handlers like onclick, onerror, etc.
        $html = preg_replace('/<(\w+)\s+[^>]*>/', '<$1>', $html);

        return $html;
    }

    /**
     * Get unread count (for API/AJAX calls)
     */
    public function getUnreadCount() {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['count' => 0]);
            exit;
        }

        $commModel = new Communication();
        $count = $commModel->getUnreadCount($_SESSION['user_id']);

        header('Content-Type: application/json');
        echo json_encode(['count' => $count]);
        exit;
    }
}
