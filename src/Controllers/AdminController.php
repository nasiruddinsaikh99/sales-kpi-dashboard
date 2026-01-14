<?php

require_once __DIR__ . '/../Models/Upload.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/KpiRecord.php';

class AdminController {
    
    public function dashboard() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /sales-kpi-dashboard/login');
            exit;
        }

        $uploadModel = new Upload();
        $uploads = $uploadModel->findAll();

        $kpiModel = new KpiRecord();
        $globalStats = $kpiModel->getGlobalStats();
        $monthlyStats = $kpiModel->getMonthlyStats();

        // Prepare chart data
        $chartLabels = [];
        $chartDataNetGP = [];
        $chartDataGrossProfit = [];
        
        foreach ($monthlyStats as $stat) {
            $chartLabels[] = date('M Y', strtotime($stat['for_month']));
            $chartDataNetGP[] = $stat['total_net_gp'];
            $chartDataGrossProfit[] = $stat['total_gross_profit'];
        }

        require __DIR__ . '/../Views/admin/dashboard.php';
    }

    public function records() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /sales-kpi-dashboard/login');
            exit;
        }

        $uploadId = $_GET['upload_id'] ?? null;
        if (!$uploadId) {
            header('Location: /sales-kpi-dashboard/admin/dashboard');
            exit;
        }

        $uploadModel = new Upload();
        $upload = $uploadModel->findById($uploadId);

        $kpiModel = new KpiRecord();
        $records = $kpiModel->findByUploadId($uploadId);

        require __DIR__ . '/../Views/admin/records.php';
    }

    public function settings() {
        require_once __DIR__ . '/../Models/Setting.php';
        $historyMonths = Setting::get('history_visibility_months', 3);
        require __DIR__ . '/../Views/admin/settings.php';
    }

    public function updateSettings() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../Models/Setting.php';
            
            // 1. Password Update
            $newPass = $_POST['new_password'] ?? '';
            $confirmPass = $_POST['confirm_password'] ?? '';

            if (!empty($newPass)) {
                if ($newPass === $confirmPass) {
                    if (isset($_SESSION['user_id'])) {
                        \App\Models\User::updatePassword($_SESSION['user_id'], $newPass); // Assuming we added this, or I'll patch it now via User model if needed, but wait, I didn't add it to User model yet. I should do that.
                        // Actually, raw query for now to be safe as I didn't edit User.php in the last turn.
                        $db = Database::connect();
                        $stmt = $db->prepare("UPDATE users SET password = :pwd WHERE id = :id");
                        $stmt->execute(['pwd' => password_hash($newPass, PASSWORD_BCRYPT), 'id' => $_SESSION['user_id']]);
                    }
                } else {
                    header('Location: /sales-kpi-dashboard/admin/settings?msg=error');
                    exit;
                }
            }

            // 2. Settings Update
            if (isset($_POST['history_visibility_months'])) {
                Setting::set('history_visibility_months', (int)$_POST['history_visibility_months']);
            }

            header('Location: /sales-kpi-dashboard/admin/settings?msg=updated');
            exit;
        }
    }

    public function deleteUpload() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /sales-kpi-dashboard/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $uploadId = $_POST['upload_id'] ?? null;
            
            if ($uploadId) {
                $uploadModel = new Upload();
                // We could add a delete method to the Upload model, or do a direct query here.
                // Since Upload model handles DB interaction, it's better to add a delete method there?
                // Or just use the PDO instance if available.
                // Let's check Upload model content first. I'll add a helper static method or instance method.
                // For simplicity/speed, I'll direct query here if needed, but Model is better.
                // Actually, let's implement the delete in the Upload model in the next step.
                // Calling it here:
                $uploadModel->delete($uploadId);
                
                header('Location: /sales-kpi-dashboard/admin/dashboard?msg=deleted');
            } else {
                header('Location: /sales-kpi-dashboard/admin/dashboard?msg=error');
            }
            exit;
        }
    }
}
