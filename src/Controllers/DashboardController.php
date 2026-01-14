<?php

require_once __DIR__ . '/../Models/KpiRecord.php';
require_once __DIR__ . '/../Models/Upload.php';

class DashboardController {
    
    public function index() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] === 'admin') {
            header('Location: /sales-kpi-dashboard/login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        
        $kpiModel = new KpiRecord();
        $uploadModel = new Upload();
        
        // Fetch raw data
        $records = $kpiModel->findByUserId($userId);
        $allUploads = $uploadModel->findAll();
        
        // Map Uploads
        $uploadMap = [];
        foreach ($allUploads as $up) {
            $uploadMap[$up['id']] = $up;
        }

        // Enrich Records
        $history = [];
        foreach ($records as $r) {
            if (isset($uploadMap[$r['upload_id']])) {
                $upload = $uploadMap[$r['upload_id']];
                $r['month'] = $upload['for_month'];
                $r['batch_name'] = $upload['batch_name'];
                $history[] = $r;
            }
        }

        // Sort by Month Desc
        usort($history, function($a, $b) {
            return strtotime($b['month']) - strtotime($a['month']);
        });

        // Filter / Select Month
        $selectedMonth = $_GET['month'] ?? ($history[0]['month'] ?? date('Y-m-d'));

        // --- VISIBILITY FILTER ---
        require_once __DIR__ . '/../Models/Setting.php';
        $limitRows = Setting::get('history_visibility_months', 3);
        
        if ($limitRows > 0) {
            // Get unique months first to limit by "Time", not just rows
            $uniqueMonths = [];
            foreach ($history as $h) {
                $uniqueMonths[$h['month']] = true;
            }
            // Sort keys descending
            krsort($uniqueMonths);
            // Slice allowed months
            $allowedMonths = array_slice(array_keys($uniqueMonths), 0, $limitRows);
            
            // Filter history
            $history = array_filter($history, function($h) use ($allowedMonths) {
                return in_array($h['month'], $allowedMonths);
            });
            // Re-index
            $history = array_values($history);
        }
        // -------------------------
        
        // Current Record
        $currentRecord = null;
        foreach ($history as $h) {
            if ($h['month'] == $selectedMonth) {
                // If multiple uploads for same month, take the latest (which should be first due to sort order logic if we refined it, but here just take first found)
                if (!$currentRecord || $h['id'] > $currentRecord['id']) {
                    $currentRecord = $h;
                }
            }
        }

        // Chart Data (Last 6 Months)
        $chartLabels = [];
        $chartData = [];
        
        // Group by month to handle duplicates (take latest)
        $monthlyMap = [];
        foreach ($history as $h) {
            $m = date('Y-m', strtotime($h['month']));
            if (!isset($monthlyMap[$m])) {
                $monthlyMap[$m] = $h;
            }
        }
        
        // Take last 6 keys
        $res = array_reverse(array_slice($monthlyMap, 0, 6)); 
        foreach ($res as $h) {
            $chartLabels[] = date('M Y', strtotime($h['month']));
            $chartData[] = $h['net_gp']; // Plotting Net GP
        }


        require __DIR__ . '/../Views/agent/dashboard.php';
    }
}
