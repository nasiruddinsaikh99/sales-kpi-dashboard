<?php

require_once __DIR__ . '/../Models/Upload.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/KpiRecord.php';

class UploadController {
    
    public function index() {
        require __DIR__ . '/../Views/admin/upload.php';
    }

    public function upload() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
            $file = $_FILES['csv_file']['tmp_name'];
            $month = $_POST['month'] ?? date('Y-m-d');
            $batchName = $_POST['batch_name'] ?? 'Upload ' . date('Y-m-d H:i');

            if (($handle = fopen($file, "r")) !== FALSE) {
                $lines = [];
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $lines[] = $data;
                }
                fclose($handle);

                // --- 1. Create Upload Record ---
                $uploadModel = new Upload();
                $uploadId = $uploadModel->create([
                    'batch_name' => $batchName,
                    'for_month' => $batchName // Using batch name for display, logic uses $month if needed
                ]);
                // Correcting for_month usage if needed, schema says VARCHAR usually.
                // Re-updating uploads to actually use the date.
                // The current schema might treat 'for_month' as string. The form sends YYYY-MM. 
                // Let's stick strictly to what the user inputted.
                
                // Helper to clean values
                $clean = function($str) {
                    // Remove currency symbols, commas, quotes
                    // If it's a percentage, keep it as string "XX%" or decimal? 
                    // DB schema for percentages is VARCHAR(10) or DECIMAL.
                    // Implementation plan said VARCHAR(10) default '0%'.
                    // For money/decimal fields: remove '$' and ','
                    $str = trim($str);
                    if (preg_match('/^-?\$?[\d,]+(\.\d+)?$/', $str) || is_numeric($str)) {
                         return (float) preg_replace('/[^\d.-]/', '', $str);
                    }
                    return $str; 
                };

                // --- 2. Build Data Matrix ---
                // Row 0 is headers (Agent Names)
                // Row 1..N are metrics
                
                $agentNames = array_slice($lines[0], 1);
                $agentData = []; // [col_index => ['db_col' => val, ...]]

                // Initialize agent data arrays
                foreach ($agentNames as $idx => $name) {
                    if (empty($name)) continue;
                    $agentData[$idx] = [
                        'agent_name_snapshot' => $name,
                        'user_id' => null, // Will fetch/create later
                        'upload_id' => $uploadId
                    ];
                }

                // Row Mapping Definition
                $lastMetric = ''; // For state tracking of "Accel %"
                
                // Rows starting from index 1
                for ($rowIdx = 1; $rowIdx < count($lines); $rowIdx++) {
                    $row = $lines[$rowIdx];
                    $label = trim($row[0]);
                    
                    if (empty($label)) continue;

                    // Determine DB column
                    $dbCol = null;

                    // Normalize label for matching (remove special chars, lowercase)
                    $normLabel = strtolower(trim(str_replace(["\n", "\r"], " ", $label)));

                    if (str_contains($normLabel, 'gross profit (rq)')) $dbCol = 'gross_profit';
                    elseif ($normLabel === 'chargeback') $dbCol = 'chargeback';
                    elseif (str_contains($normLabel, 'net gp')) $dbCol = 'net_gp';
                    elseif (str_contains($normLabel, 'gp spiff qualified')) $dbCol = 'gp_spiff_qualified_pct';
                    elseif (str_contains($normLabel, 'total gp spiff amt')) $dbCol = 'total_gp_spiff_amt';
                    elseif (str_contains($normLabel, 'spiff amt for accelerator')) $dbCol = 'gp_spiff_amt_accelerator';
                    elseif ($normLabel === 'payout') $dbCol = 'payout';
                    elseif ($normLabel === 'payout cb') $dbCol = 'payout_cb';
                    elseif ($normLabel === 'lateness') $dbCol = 'lateness';
                    elseif ($normLabel === 'final payout') $dbCol = 'final_payout';
                    elseif ($normLabel === 'qualifiers') $dbCol = 'qualifiers';
                    elseif (str_contains($normLabel, 'total accelerators')) $dbCol = 'total_accelerators_pct';
                    elseif (str_contains($normLabel, 'flavor of month')) $dbCol = 'flavor_of_month';
                    elseif (str_contains($normLabel, 'priority upgrade %')) $dbCol = 'priority_upgrade_pct';
                    elseif (str_contains($normLabel, '20% vhi close rate')) $dbCol = 'vhi_close_rate_pct';
                    elseif (str_contains($normLabel, 'fios qty sold')) $dbCol = 'fios_qty_sold';
                    elseif ($normLabel === 'vhi') $dbCol = 'vhi';
                    elseif (str_contains($normLabel, 'upgrade quantity')) $dbCol = 'upgrade_quantity';
                    elseif (str_contains($normLabel, 'upgrade conversion %')) $dbCol = 'upgrade_conversion_pct';
                    elseif ($normLabel === 'smt ga') $dbCol = 'smt_ga';
                    elseif (str_contains($normLabel, 'consumer smt ga')) $dbCol = 'consumer_smt_ga_conversion_pct';
                    elseif (str_contains($normLabel, 'vz protect %')) $dbCol = 'vz_protect_pct';
                    elseif (str_contains($normLabel, 'take rate for registered')) $dbCol = 'take_rate_registered_perks';
                    elseif (str_contains($normLabel, 'premium unlimited')) $dbCol = 'premium_unlimited_pct';
                    elseif ($normLabel === 'smb ga') $dbCol = 'smb_ga';
                    elseif (str_contains($normLabel, 'agpps on dp only')) $dbCol = 'agpps_dp_only';
                    elseif (str_contains($normLabel, 'accounts accessed %')) $dbCol = 'accounts_accessed_pct';
                    elseif (str_contains($normLabel, 'manual leads %')) $dbCol = 'manual_leads_pct';
                    elseif (str_contains($normLabel, 'chatter spot opt in')) $dbCol = 'chatter_spot_opt_in_pct';
                    elseif (str_contains($normLabel, 'box convertion') || str_contains($normLabel, 'box conversion')) $dbCol = 'box_conversion';
                    elseif (str_contains($normLabel, 'ready go/setup')) $dbCol = 'ready_go_setup_per_smt';
                    elseif (str_contains($normLabel, 'device spiff')) $dbCol = 'device_spiff';
                    
                    // Handle Accel % Context
                    elseif (str_contains($normLabel, 'accel %')) {
                        switch ($lastMetric) {
                            case 'priority_upgrade_pct': $dbCol = 'priority_upgrade_accel_pct'; break;
                            case 'vhi': $dbCol = 'vhi_accel_pct'; break;
                            case 'upgrade_conversion_pct': $dbCol = 'upgrade_conversion_accel_pct'; break;
                            case 'consumer_smt_ga_conversion_pct': $dbCol = 'consumer_smt_ga_conversion_accel_pct'; break;
                            case 'vz_protect_pct': $dbCol = 'vz_protect_accel_pct'; break;
                            case 'take_rate_registered_perks': $dbCol = 'take_rate_registered_perks_accel_pct'; break;
                            case 'premium_unlimited_pct': $dbCol = 'premium_unlimited_accel_pct'; break;
                            case 'smb_ga': $dbCol = 'smb_ga_accel_pct'; break;
                            case 'agpps_dp_only': $dbCol = 'agpps_dp_only_accel_pct'; break;
                            case 'accounts_accessed_pct': $dbCol = 'accounts_accessed_accel_pct'; break;
                            case 'manual_leads_pct': $dbCol = 'manual_leads_accel_pct'; break;
                            case 'chatter_spot_opt_in_pct': $dbCol = 'chatter_spot_opt_in_accel_pct'; break;
                        }
                    }

                    if ($dbCol) {
                        // Remember this metric for the next iteration (if next is Accel %)
                        // But don't update lastMetric if it IS Accel %, because Accel doesn't have child Accel
                        if (!str_contains($normLabel, 'accel %')) {
                            $lastMetric = $dbCol;
                        }

                        // Distribute values to agents
                        foreach ($agentData as $idx => &$agentRecord) {
                            // CSV col index = $idx + 1 (since 0 is label)
                            $val = $row[$idx + 1] ?? null;
                            $agentRecord[$dbCol] = $clean($val);
                        }
                    }
                }

                // --- 3. Save to DB ---
                $userModel = new User();
                $kpiModel = new KpiRecord();

                foreach ($agentData as $record) {
                    $name = $record['agent_name_snapshot'];
                    
                    // Generate/Find User
                    $emailName = strtolower(str_replace(' ', '.', $name)) . '@syntrex.placeholder';
                    $user = $userModel->findByEmail($emailName);
                    
                    if (!$user) {
                        $userId = $userModel->create([
                            'name' => $name,
                            'email' => $emailName,
                            'password' => 'Welcome123',
                            'role' => 'agent'
                        ]);
                    } else {
                        $userId = $user['id'];
                    }
                    
                    $record['user_id'] = $userId;
                    
                    // Insert
                    $kpiModel->create($record);
                }

                header('Location: /sales-kpi-dashboard/admin/dashboard?msg=Import+Success');
                exit;

            } else {
                echo "Error reading file";
            }
        }
    }
}
