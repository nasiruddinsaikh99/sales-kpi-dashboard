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

            // Prepare record data with new fields
            $recordData = [
                'ranking_upload_id' => $uploadId,
                'user_id' => $userId,
                'employee_name' => $employeeName,
                'overall_rank' => $this->cleanInteger($row[$columnMap['overall_rank']] ?? '0'),
                'total_attainment_pct' => $this->cleanPercentage($row[$columnMap['total_attainment_pct']] ?? '0'),

                // Premium Unlimited (HPU) - triplet
                'hpu_actual_pct' => isset($columnMap['hpu_actual_pct']) ? $this->cleanPercentage($row[$columnMap['hpu_actual_pct']] ?? '0') : null,
                'hpu_goal_pct' => isset($columnMap['hpu_goal_pct']) ? $this->cleanPercentage($row[$columnMap['hpu_goal_pct']] ?? '0') : null,
                'hpu_attainment_pct' => isset($columnMap['hpu_attainment_pct']) ? $this->cleanPercentage($row[$columnMap['hpu_attainment_pct']] ?? '0') : null,

                // VZ VHI Conv - triplet
                'vhi_conv_actual_pct' => isset($columnMap['vhi_conv_actual_pct']) ? $this->cleanPercentage($row[$columnMap['vhi_conv_actual_pct']] ?? '0') : null,
                'vhi_conv_goal_pct' => isset($columnMap['vhi_conv_goal_pct']) ? $this->cleanPercentage($row[$columnMap['vhi_conv_goal_pct']] ?? '0') : null,
                'vhi_conv_attainment_pct' => isset($columnMap['vhi_conv_attainment_pct']) ? $this->cleanPercentage($row[$columnMap['vhi_conv_attainment_pct']] ?? '0') : null,

                // CSGA - triplet
                'csga_actual_pct' => isset($columnMap['csga_actual_pct']) ? $this->cleanPercentage($row[$columnMap['csga_actual_pct']] ?? '0') : null,
                'csga_goal_pct' => isset($columnMap['csga_goal_pct']) ? $this->cleanPercentage($row[$columnMap['csga_goal_pct']] ?? '0') : null,
                'csga_attainment_pct' => isset($columnMap['csga_attainment_pct']) ? $this->cleanPercentage($row[$columnMap['csga_attainment_pct']] ?? '0') : null,

                // VMP Take Rate - triplet (decimal values, not percentages)
                'vmp_take_actual' => isset($columnMap['vmp_take_actual']) ? $this->cleanDecimal($row[$columnMap['vmp_take_actual']] ?? '0') : null,
                'vmp_take_goal' => isset($columnMap['vmp_take_goal']) ? $this->cleanDecimal($row[$columnMap['vmp_take_goal']] ?? '0') : null,
                'vmp_take_attainment_pct' => isset($columnMap['vmp_take_attainment_pct']) ? $this->cleanPercentage($row[$columnMap['vmp_take_attainment_pct']] ?? '0') : null,

                // VZ Perks Rate - triplet (decimal values, not percentages)
                'vz_perks_actual' => isset($columnMap['vz_perks_actual']) ? $this->cleanDecimal($row[$columnMap['vz_perks_actual']] ?? '0') : null,
                'vz_perks_goal' => isset($columnMap['vz_perks_goal']) ? $this->cleanDecimal($row[$columnMap['vz_perks_goal']] ?? '0') : null,
                'vz_perks_attainment_pct' => isset($columnMap['vz_perks_attainment_pct']) ? $this->cleanPercentage($row[$columnMap['vz_perks_attainment_pct']] ?? '0') : null,

                // Traffic GP/Cust - triplet (currency values)
                'traffic_gp_actual' => isset($columnMap['traffic_gp_actual']) ? $this->cleanCurrency($row[$columnMap['traffic_gp_actual']] ?? '0') : null,
                'traffic_gp_goal' => isset($columnMap['traffic_gp_goal']) ? $this->cleanCurrency($row[$columnMap['traffic_gp_goal']] ?? '0') : null,
                'traffic_gp_cust_attainment_pct' => isset($columnMap['traffic_gp_cust_attainment_pct']) ? $this->cleanPercentage($row[$columnMap['traffic_gp_cust_attainment_pct']] ?? '0') : null
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

        // For the new CSV format with triplets (Actual, Goal, Attained %)
        // The pattern is: KPI Name, KPI Goal, Attained %

        // Basic columns
        $map['employee_name'] = array_search('individual employee', $normalizedHeaders);
        $map['overall_rank'] = array_search('overall rank', $normalizedHeaders);
        $map['total_attainment_pct'] = array_search('total attainment', $normalizedHeaders);

        // Find KPI triplets by looking for patterns
        // The CSV has a pattern where "Attained %" appears multiple times
        $attainedIndices = [];
        foreach ($normalizedHeaders as $index => $header) {
            if ($header === 'attained %') {
                $attainedIndices[] = $index;
            }
        }

        // Map the triplets based on the order they appear
        // Premium Unlimited (HPU) - columns 3,4,5
        if (count($attainedIndices) >= 1) {
            $map['hpu_actual_pct'] = 3;  // Premium Unlimited %
            $map['hpu_goal_pct'] = 4;    // Premium Unlimited Goal
            $map['hpu_attainment_pct'] = 5; // First "Attained %"
        }

        // VZ VHI Conv - columns 6,7,8
        if (count($attainedIndices) >= 2) {
            $map['vhi_conv_actual_pct'] = 6;  // VZ VHI Conv %
            $map['vhi_conv_goal_pct'] = 7;    // VZ VHI GA Goal
            $map['vhi_conv_attainment_pct'] = 8; // Second "Attained %"
        }

        // CSGA - columns 9,10,11
        if (count($attainedIndices) >= 3) {
            $map['csga_actual_pct'] = 9;   // CSGA %
            $map['csga_goal_pct'] = 10;    // CSGA Goal
            $map['csga_attainment_pct'] = 11; // Third "Attained %"
        }

        // VMP Take Rate - columns 12,13,14
        if (count($attainedIndices) >= 4) {
            $map['vmp_take_actual'] = 12;   // VMP Take Rate
            $map['vmp_take_goal'] = 13;     // VMP Goal
            $map['vmp_take_attainment_pct'] = 14; // Fourth "Attained %"
        }

        // VZ Perks Rate - columns 15,16,17
        if (count($attainedIndices) >= 5) {
            $map['vz_perks_actual'] = 15;    // VZ Perks Rate
            $map['vz_perks_goal'] = 16;      // VZ Perks Goal
            $map['vz_perks_attainment_pct'] = 17; // Fifth "Attained %"
        }

        // Traffic GP/Cust - columns 18,19,20
        if (count($attainedIndices) >= 6) {
            $map['traffic_gp_actual'] = 18;   // Traffic GP/Cust (dollar value)
            $map['traffic_gp_goal'] = 19;     // Traffic GP/Cust Goal
            $map['traffic_gp_cust_attainment_pct'] = 20; // Sixth "Attained %"
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

    private function cleanDecimal($value) {
        // Remove any spaces
        $value = trim($value);

        // Handle negative values in parentheses
        if (preg_match('/\(([0-9.]+)\)/', $value, $matches)) {
            $value = '-' . $matches[1];
        }

        // Convert to float
        return floatval($value);
    }

    private function cleanCurrency($value) {
        // Remove currency symbols and spaces
        $value = str_replace(['$', ',', ' '], '', $value);

        // Handle negative values in parentheses
        if (preg_match('/\(([0-9.]+)\)/', $value, $matches)) {
            $value = '-' . $matches[1];
        }

        // Convert to float
        return floatval($value);
    }
}