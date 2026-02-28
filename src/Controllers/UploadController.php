<?php

require_once __DIR__ . '/../Models/Upload.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/KpiRecord.php';

class UploadController {
    
    public function index() {
        require __DIR__ . '/../Views/admin/upload.php';
    }

    /**
     * Process CSV Upload - Standard Horizontal Format
     * 
     * Expected CSV Structure:
     * - Row 1: Headers (Individual Employee, GROSS PROFIT, Net GP after Chargebacks, ...)
     * - Row 2+: Agent data (Name, values...)
     */
    public function upload() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['csv_file'])) {
            header('Location: /sales-kpi-dashboard/admin/upload?error=No+file+uploaded');
            exit;
        }

        $file = $_FILES['csv_file']['tmp_name'];
        $forMonth = $_POST['for_month'] ?? date('Y-m-01');
        $batchName = $_POST['batch_name'] ?? 'Upload ' . date('Y-m-d H:i');

        // Convert YYYY-MM to YYYY-MM-01 for DATE column
        if (strlen($forMonth) === 7) {
            $forMonth = $forMonth . '-01';
        }

        $handle = fopen($file, 'r');
        if (!$handle) {
            header('Location: /sales-kpi-dashboard/admin/upload?error=Cannot+read+file');
            exit;
        }

        // Read all rows
        $rows = [];
        while (($data = fgetcsv($handle, 0, ',')) !== false) {
            $rows[] = $data;
        }
        fclose($handle);

        if (count($rows) < 2) {
            header('Location: /sales-kpi-dashboard/admin/upload?error=CSV+must+have+header+and+data+rows');
            exit;
        }

        // --- Column Header Mapping ---
        $headers = $rows[0];
        $columnMap = $this->buildColumnMap($headers);

        // --- Create Upload Record ---
        $uploadModel = new Upload();
        $uploadId = $uploadModel->create([
            'batch_name' => $batchName,
            'for_month' => $forMonth
        ]);

        // --- Process Data Rows ---
        $userModel = new User();
        $kpiModel = new KpiRecord();
        $imported = 0;
        $errors = [];

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            
            // Get agent name from first column
            $agentName = trim($row[$columnMap['agent_name'] ?? 0] ?? '');
            if (empty($agentName)) continue;

            // Find user by name (case-insensitive partial match)
            $user = $userModel->findByName($agentName);
            
            if (!$user) {
                $errors[] = "Agent not found: $agentName";
                continue;
            }

            // Build KPI record data
            $recordData = [
                'upload_id' => $uploadId,
                'user_id' => $user['id'],
                'agent_name_snapshot' => $agentName,
            ];

            // Map all columns to database fields
            foreach ($columnMap as $dbField => $colIndex) {
                if ($dbField === 'agent_name') continue;
                if ($colIndex === null || !isset($row[$colIndex])) continue;
                
                $value = $this->cleanValue($row[$colIndex], $dbField);
                $recordData[$dbField] = $value;
            }

            try {
                $kpiModel->create($recordData);
                $imported++;
            } catch (Exception $e) {
                $errors[] = "Error importing $agentName: " . $e->getMessage();
            }
        }

        // Redirect with status
        $msg = "Imported $imported records";
        if (count($errors) > 0) {
            $msg .= ". Errors: " . count($errors);
        }
        
        header('Location: /sales-kpi-dashboard/admin/dashboard?msg=' . urlencode($msg));
        exit;
    }

    /**
     * Build mapping from CSV column headers to database field names
     */
    private function buildColumnMap(array $headers): array {
        $map = [];
        
        // Normalize headers for matching
        $normalizedHeaders = array_map(function($h) {
            return strtolower(trim(preg_replace('/\s+/', ' ', $h)));
        }, $headers);

        // Define mappings: DB field => possible header variations
        // NOTE: Order matters - more specific patterns should come first
        // UPDATED: Added support for new data CSV format variations
        $fieldMappings = [
            'agent_name' => ['individual employee', 'employee', 'agent name', 'name'],
            'gross_profit' => ['gross profit (rq)', 'gross profit'],
            'net_gp' => ['net gp after chargebacks', 'net gp'],
            'chargeback' => ['chargeback', 'total chargeback', 'chargebacks'],
            'gp_spiff_qualified_pct' => ['gp spiff qualified %', 'gp spiff qualified'],
            'total_gp_spiff_amt' => ['total gp spiff amt', 'gp commission'],
            'gp_spiff_amt_accelerator' => ['gp spiff amt for accelerator'],
            'payout' => ['payout'],
            'payout_cb' => ['payout cb', 'payout chargeback'],
            'lateness' => ['lateness'],
            'final_payout' => ['final payout'],
            'qualifiers' => ['qualifiers'],
            'total_accelerators_pct' => ['total accelerators %', 'total accelerators'],
            'flavor_of_month' => ['flavor of month'],
            'priority_upgrade_pct' => ['high priority upgrade %', 'priority upgrade %'],
            'vhi_close_rate_pct' => ['20% vhi close rate', 'vhi close rate'],
            'fios_qty_sold' => ['fios qty sold'],
            'upgrade_quantity' => ['upgrade quantity'],
            'upgrade_conversion_pct' => ['upgrade conversion %'],
            'smt_ga' => ['csga'],
            'consumer_smt_ga_conversion_pct' => ['consumer smt ga conversion %'],
            'vz_protect_pct' => ['vz protect %'],
            'take_rate_registered_perks' => ['take rate for registered perks'],
            'premium_unlimited_pct' => ['premium unlimited %'],
            'aal_qualifier' => ['aal (csga byod, csga byod+, csga esim, csga dpp) 30% or above', 'aal qualifier'],
            'vhdp_protection' => ['vhdp protection 5% or above', 'vhdp protection'],
            'smb_ga' => ['smb ga'],
            'agpps_dp_only' => ['agpps on dp only'],
            'accounts_accessed_pct' => ['accounts accessed %'],
            'manual_leads_pct' => ['manual leads %'],
            'chatter_spot_opt_in_pct' => ['chatter spot opt in %'],
            'box_conversion' => ['box convertion', 'box conversion'],
            'ready_go_setup_per_smt' => ['ready go/setup per smt on dp', 'ready go/setup per smt'],
            'device_spiff' => ['device spiff'],
        ];

        // Find column index for each field - use exact match first, then contains
        foreach ($fieldMappings as $dbField => $possibleHeaders) {
            foreach ($possibleHeaders as $headerVariant) {
                foreach ($normalizedHeaders as $colIndex => $normalizedHeader) {
                    // Exact match (after trimming)
                    if (trim($normalizedHeader) === $headerVariant) {
                        $map[$dbField] = $colIndex;
                        break 2;
                    }
                }
            }
            // If no exact match, try contains match
            if (!isset($map[$dbField])) {
                foreach ($possibleHeaders as $headerVariant) {
                    foreach ($normalizedHeaders as $colIndex => $normalizedHeader) {
                        if (strpos($normalizedHeader, $headerVariant) !== false) {
                            $map[$dbField] = $colIndex;
                            break 2;
                        }
                    }
                }
            }
        }
        
        // VHI column needs special handling - it's a standalone "vhi" column
        foreach ($normalizedHeaders as $colIndex => $normalizedHeader) {
            if (trim($normalizedHeader) === 'vhi') {
                $map['vhi'] = $colIndex;
                break;
            }
        }

        // Handle Accel % columns - these appear multiple times, need context
        $this->mapAccelColumns($normalizedHeaders, $map);

        return $map;
    }

    /**
     * Map accelerator percentage columns based on their position after parent metrics
     */
    private function mapAccelColumns(array $headers, array &$map): void {
        $accelMappings = [
            'priority_upgrade_pct' => 'priority_upgrade_accel_pct',
            'vhi' => 'vhi_accel_pct',
            'upgrade_conversion_pct' => 'upgrade_conversion_accel_pct',
            'consumer_smt_ga_conversion_pct' => 'consumer_smt_ga_conversion_accel_pct',
            'vz_protect_pct' => 'vz_protect_accel_pct',
            'take_rate_registered_perks' => 'take_rate_registered_perks_accel_pct',
            'premium_unlimited_pct' => 'premium_unlimited_accel_pct',
            'smb_ga' => 'smb_ga_accel_pct',
            'agpps_dp_only' => 'agpps_dp_only_accel_pct',
            'accounts_accessed_pct' => 'accounts_accessed_accel_pct',
            'manual_leads_pct' => 'manual_leads_accel_pct',
            'chatter_spot_opt_in_pct' => 'chatter_spot_opt_in_accel_pct',
        ];

        foreach ($accelMappings as $parentField => $accelField) {
            if (!isset($map[$parentField])) continue;
            
            $parentIndex = $map[$parentField];
            
            // Look for "accel %" in the next column after parent
            for ($i = $parentIndex + 1; $i < min($parentIndex + 3, count($headers)); $i++) {
                if (strpos($headers[$i], 'accel %') !== false || $headers[$i] === 'accel %') {
                    $map[$accelField] = $i;
                    break;
                }
            }
        }
    }

    /**
     * Clean and convert values based on field type
     */
    private function cleanValue($value, string $field) {
        $value = trim($value);
        
        if ($value === '' || $value === '-' || $value === '$ -' || $value === '$ -   ') {
            return $this->isDecimalField($field) ? 0.00 : '';
        }

        // Decimal/money fields
        if ($this->isDecimalField($field)) {
            // Handle negative values in parentheses: $ (150.00)
            if (preg_match('/\([\d,.]+\)/', $value)) {
                $value = '-' . preg_replace('/[^\d.]/', '', $value);
            }
            // Remove currency symbols, commas, spaces
            $cleaned = preg_replace('/[^0-9.\-]/', '', $value);
            return floatval($cleaned);
        }

        // Integer fields
        if ($this->isIntegerField($field)) {
            return intval(preg_replace('/[^0-9\-]/', '', $value));
        }

        // Percentage fields - keep as string with %
        if (strpos($field, '_pct') !== false || strpos($field, 'accel') !== false) {
            // Already has % - keep it
            if (strpos($value, '%') !== false) {
                return $value;
            }
            // Add % if missing
            return $value . '%';
        }

        return $value;
    }

    private function isDecimalField(string $field): bool {
        $decimalFields = [
            'gross_profit', 'chargeback', 'net_gp', 'total_gp_spiff_amt',
            'gp_spiff_amt_accelerator', 'payout', 'payout_cb', 'lateness',
            'final_payout', 'flavor_of_month', 'take_rate_registered_perks',
            'agpps_dp_only', 'device_spiff'
        ];
        return in_array($field, $decimalFields);
    }

    private function isIntegerField(string $field): bool {
        $intFields = ['fios_qty_sold', 'vhi', 'upgrade_quantity', 'smt_ga', 'smb_ga'];
        return in_array($field, $intFields);
    }
}