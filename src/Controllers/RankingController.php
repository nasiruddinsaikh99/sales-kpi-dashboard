<?php

require_once __DIR__ . '/../Models/RankingUpload.php';
require_once __DIR__ . '/../Models/RankingRecord.php';
require_once __DIR__ . '/../Models/User.php';

class RankingController {

    public function __construct() {
        session_start();

        // Check if user is logged in and is admin
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header("Location: /sales-kpi-dashboard/login");
            exit;
        }
    }

    public function index() {
        $uploadModel = new RankingUpload();
        $uploads = $uploadModel->findAll();

        // Check for success message
        $successMessage = null;
        if (isset($_GET['success'])) {
            if ($_GET['success'] == 'uploaded') {
                $successMessage = "Rankings uploaded successfully!";
            } elseif ($_GET['success'] == 'deleted') {
                $successMessage = "Ranking data deleted successfully!";
            }
        }

        require __DIR__ . '/../Views/admin/rankings/index.php';
    }

    public function uploadForm() {
        require __DIR__ . '/../Views/admin/rankings/upload.php';
    }

    public function processUpload() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /sales-kpi-dashboard/admin/rankings");
            exit;
        }

        // Check if file was uploaded
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] != 0) {
            header("Location: /sales-kpi-dashboard/admin/rankings/upload?error=no_file");
            exit;
        }

        $file = $_FILES['csv_file']['tmp_name'];
        $uploadDate = $_POST['upload_date'] ?? date('Y-m-d');

        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $uploadDate)) {
            header("Location: /sales-kpi-dashboard/admin/rankings/upload?error=invalid_date");
            exit;
        }

        // Check if data for this date already exists
        $uploadModel = new RankingUpload();
        if ($uploadModel->existsForDate($uploadDate)) {
            header("Location: /sales-kpi-dashboard/admin/rankings/upload?error=duplicate_date");
            exit;
        }

        // Parse CSV
        $handle = fopen($file, 'r');
        if (!$handle) {
            header("Location: /sales-kpi-dashboard/admin/rankings/upload?error=file_read");
            exit;
        }

        // Read header row
        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            header("Location: /sales-kpi-dashboard/admin/rankings/upload?error=invalid_csv");
            exit;
        }

        // Build column map
        $columnMap = $this->buildColumnMap($headers);

        // Verify required columns exist
        if (!isset($columnMap['employee_name']) || !isset($columnMap['overall_rank'])) {
            fclose($handle);
            header("Location: /sales-kpi-dashboard/admin/rankings/upload?error=missing_columns");
            exit;
        }

        // Create upload record
        $uploadId = $uploadModel->create($uploadDate, $_SESSION['user_id']);

        // Process data rows
        $recordModel = new RankingRecord();
        $userModel = new User();
        $importCount = 0;
        $errors = [];

        while (($row = fgetcsv($handle)) !== FALSE) {
            if (count($row) < 2) continue; // Skip empty rows

            // Extract employee name
            $employeeName = trim($row[$columnMap['employee_name']] ?? '');
            if (empty($employeeName)) continue;

            // Skip if it's a special row (like "No commission" entries)
            if (stripos($employeeName, 'no commission') !== false) continue;

            // Try to match to a user
            $user = $userModel->findByName($employeeName);
            $userId = $user ? $user['id'] : null;

            // Prepare record data
            $recordData = [
                'ranking_upload_id' => $uploadId,
                'user_id' => $userId,
                'employee_name' => $employeeName,
                'overall_rank' => $this->cleanInteger($row[$columnMap['overall_rank']] ?? '0'),
                'total_attainment_pct' => $this->cleanPercentage($row[$columnMap['total_attainment_pct']] ?? '0'),
                'hpu_attainment_pct' => $this->cleanPercentage($row[$columnMap['hpu_attainment_pct']] ?? '0'),
                'vhi_conv_attainment_pct' => $this->cleanPercentage($row[$columnMap['vhi_conv_attainment_pct']] ?? '0'),
                'upg_conv_attainment_pct' => $this->cleanPercentage($row[$columnMap['upg_conv_attainment_pct']] ?? '0'),
                'csga_attainment_pct' => $this->cleanPercentage($row[$columnMap['csga_attainment_pct']] ?? '0'),
                'vmp_take_attainment_pct' => $this->cleanPercentage($row[$columnMap['vmp_take_attainment_pct']] ?? '0'),
                'perks_attainment_pct' => $this->cleanPercentage($row[$columnMap['perks_attainment_pct']] ?? '0'),
                'traffic_gp_cust_attainment_pct' => $this->cleanPercentage($row[$columnMap['traffic_gp_cust_attainment_pct']] ?? '0')
            ];

            try {
                $recordModel->create($recordData);
                $importCount++;
            } catch (Exception $e) {
                $errors[] = "Error importing: $employeeName";
            }
        }

        fclose($handle);

        // Redirect with success message
        if (count($errors) > 0) {
            $errorString = implode(', ', array_slice($errors, 0, 3));
            header("Location: /sales-kpi-dashboard/admin/rankings?success=uploaded&warning=" . urlencode("Imported $importCount records. Some errors: $errorString"));
        } else {
            header("Location: /sales-kpi-dashboard/admin/rankings?success=uploaded");
        }
        exit;
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /sales-kpi-dashboard/admin/rankings");
            exit;
        }

        $uploadId = $_POST['upload_id'] ?? null;
        if (!$uploadId) {
            header("Location: /sales-kpi-dashboard/admin/rankings?error=invalid_id");
            exit;
        }

        $uploadModel = new RankingUpload();
        $uploadModel->delete($uploadId);

        header("Location: /sales-kpi-dashboard/admin/rankings?success=deleted");
        exit;
    }

    private function buildColumnMap($headers) {
        $map = [];

        // Normalize headers for matching
        $normalizedHeaders = array_map(function($h) {
            return strtolower(trim($h));
        }, $headers);

        // Define possible column names
        $columnMappings = [
            'employee_name' => ['individual employee', 'employee', 'agent name', 'name'],
            'overall_rank' => ['overall rank', 'rank', 'position'],
            'total_attainment_pct' => ['total attainment %', 'total attainment', 'overall attainment'],
            'hpu_attainment_pct' => ['hpu attainment %', 'hpu attainment', 'hpu %'],
            'vhi_conv_attainment_pct' => ['vhi conv attainment %', 'vhi conversion attainment', 'vhi conv %'],
            'upg_conv_attainment_pct' => ['upg conv attainment %', 'upgrade conversion attainment', 'upg conv %'],
            'csga_attainment_pct' => ['csga attainment %', 'csga attainment', 'csga %'],
            'vmp_take_attainment_pct' => ['vmp take attainment %', 'vmp take', 'vmp %'],
            'perks_attainment_pct' => ['perks attainment %', 'perks attainment', 'perks %'],
            'traffic_gp_cust_attainment_pct' => ['traffic gp/cust attainment %', 'traffic gp/cust', 'traffic gp', 'gp/cust attainment']
        ];

        foreach ($columnMappings as $field => $possibleNames) {
            foreach ($possibleNames as $possibleName) {
                $index = array_search($possibleName, $normalizedHeaders);
                if ($index !== false) {
                    $map[$field] = $index;
                    break;
                }
            }

            // If not found by exact match, try contains
            if (!isset($map[$field])) {
                foreach ($normalizedHeaders as $index => $header) {
                    foreach ($possibleNames as $possibleName) {
                        if (strpos($header, $possibleName) !== false) {
                            $map[$field] = $index;
                            break 2;
                        }
                    }
                }
            }
        }

        return $map;
    }

    private function cleanPercentage($value) {
        // Remove % sign and any spaces
        $value = str_replace(['%', ' '], '', $value);

        // Handle negative values in parentheses
        if (preg_match('/\(([0-9.]+)\)/', $value, $matches)) {
            $value = '-' . $matches[1];
        }

        // Convert to float
        $value = floatval($value);

        return $value;
    }

    private function cleanInteger($value) {
        return intval(preg_replace('/[^0-9]/', '', $value));
    }
}