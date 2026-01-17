<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Records - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <?php include __DIR__ . '/../partials/theme_script.php'; ?>
    <style>
        /* Ultra wide table scroll */
        .table-container { overflow-x: auto; max-width: 100vw; }
        th, td { white-space: nowrap; padding: 0.5rem 0.75rem; font-size: 0.75rem; }
        .sticky-col { position: sticky; left: 0; z-index: 10; }
        
        /* Resizable columns - applied after JS initializes */
        table.resizable { table-layout: fixed; }
        th { position: relative; }
        th .resizer {
            position: absolute;
            top: 0;
            right: -2px;
            width: 6px;
            height: 100%;
            cursor: col-resize;
            user-select: none;
            background: transparent;
            z-index: 100;
        }
        th .resizer:hover,
        th .resizer.resizing {
            background: rgba(99, 102, 241, 0.6);
        }
        
        /* Custom Scrollbar for better UI */
        .custom-scrollbar::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent; 
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(156, 163, 175, 0.5);
            border-radius: 5px;
        }
        
        /* Hide table until widths are applied to prevent bounce */
        table.loading-widths { visibility: hidden; }
    </style>
    
    <!-- CRITICAL: Apply saved column widths IMMEDIATELY to prevent bounce -->
    <script>
    (function() {
        const STORAGE_KEY = 'kpi_records_column_widths';
        try {
            const savedWidths = JSON.parse(localStorage.getItem(STORAGE_KEY));
            if (savedWidths && Object.keys(savedWidths).length > 0) {
                // Create CSS rules for each column width - apply to table directly
                let css = 'table { table-layout: fixed !important; } ';
                for (const [index, width] of Object.entries(savedWidths)) {
                    css += `table th:nth-child(${parseInt(index) + 1}) { width: ${width}px !important; } `;
                }
                document.write('<style id="instant-column-widths">' + css + '</style>');
            }
        } catch (e) {}
    })();
    </script>
</head>
<body class="h-screen bg-slate-50 dark:bg-[#0f172a] text-slate-900 dark:text-[#e2e8f0] flex overflow-hidden">

    <!-- Sidebar -->
    <?php include __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col md:ml-64 transition-all duration-300 w-full h-full overflow-hidden">
        
        <!-- Mobile Header -->
        <header class="md:hidden bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 p-4 flex justify-between items-center flex-shrink-0">
            <h1 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-emerald-500">Syntrex Admin</h1>
            <button id="openSidebar" class="text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
        </header>

        <main class="flex-grow p-4 md:p-6 w-full h-full overflow-hidden flex flex-col min-h-0">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4 flex-shrink-0">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($upload['batch_name']) ?></h2>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                        Month: <span class="text-indigo-600 dark:text-indigo-300"><?= htmlspecialchars($upload['for_month']) ?></span> 
                        &bull; Records: <?= count($records) ?>
                    </p>
                </div>
            <div class="flex gap-3 items-center">
                    <a href="/sales-kpi-dashboard/admin/dashboard" class="px-4 py-2 border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg text-sm text-gray-600 dark:text-gray-300 transition">Back to List</a>
                    
                    <!-- Help Button -->
                    <div class="relative">
                        <button id="helpBtn" class="p-2 border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg text-gray-500 dark:text-gray-400 transition" title="Help">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </button>
                        
                        <!-- Help Tooltip -->
                        <div id="helpTooltip" class="hidden absolute right-0 top-full mt-2 w-80 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-2xl z-50 p-4">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Table Tips
                                </h3>
                                <button id="closeHelp" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
                            
                            <div class="space-y-2">
                                <!-- Tip 1: Resize Columns -->
                                <div class="flex gap-3 p-2.5 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                    <div class="flex-shrink-0 w-7 h-7 bg-indigo-100 dark:bg-indigo-500/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path></svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-900 dark:text-white">Resize Columns</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Drag between column headers to resize</p>
                                    </div>
                                </div>
                                
                                <!-- Tip 2: Double-click to auto-fit -->
                                <div class="flex gap-3 p-2.5 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                    <div class="flex-shrink-0 w-7 h-7 bg-purple-100 dark:bg-purple-500/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-3.5 h-3.5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7 5l-4 4"></path></svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-900 dark:text-white">Auto-fit Column</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Double-click column edge to fit content</p>
                                    </div>
                                </div>
                                
                                <!-- Tip 3: Sizes Remembered -->
                                <div class="flex gap-3 p-2.5 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                    <div class="flex-shrink-0 w-7 h-7 bg-amber-100 dark:bg-amber-500/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-900 dark:text-white">Sizes Remembered</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Column widths are saved automatically</p>
                                    </div>
                                </div>
                                
                                <!-- Tip 4: Horizontal Scroll -->
                                <div class="flex gap-3 p-2.5 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                    <div class="flex-shrink-0 w-7 h-7 bg-emerald-100 dark:bg-emerald-500/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-900 dark:text-white">Scroll Horizontally</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Hold <kbd class="px-1 py-0.5 bg-gray-200 dark:bg-gray-600 rounded text-xs font-mono">Shift</kbd> + scroll</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Reset Button -->
                            <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                                <button id="resetColumnsBtn" class="w-full flex items-center justify-center gap-2 px-3 py-2 bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-100 dark:hover:bg-red-500/20 transition text-sm font-medium">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                    Reset All Column Widths
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-xl table-container flex-grow border border-gray-200 dark:border-gray-700 overflow-auto relative custom-scrollbar min-h-0">
                <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-400 uppercase font-bold sticky top-0 z-40 shadow-sm">
                    <tr>
                        <!-- Sticky Agent Name Column -->
                        <th class="sticky left-0 z-50 bg-gray-50 dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 shadow-[2px_0_5px_rgba(0,0,0,0.1)] dark:shadow-[2px_0_5px_rgba(0,0,0,0.3)] min-w-[180px]">Individual Employee</th>
                        
                        <!-- Financial Columns (Matching Client CSV Exactly) -->
                        <th class="text-right bg-gray-50 dark:bg-gray-800">GROSS PROFIT</th>
                        <th class="text-right bg-gray-50 dark:bg-gray-800">Net GP after Chargebacks</th>
                        <th class="text-center bg-gray-50 dark:bg-gray-800">GP Spiff Qualified %</th>
                        <th class="text-right bg-gray-50 dark:bg-gray-800">GP Commission</th>
                        <th class="text-right bg-gray-50 dark:bg-gray-800">GP Spiff Amt for Accelerator</th>
                        <th class="text-right bg-gray-50 dark:bg-gray-800">Payout</th>
                        <th class="text-right bg-gray-50 dark:bg-gray-800">Payout Chargeback</th>
                        <th class="text-right bg-gray-50 dark:bg-gray-800">Lateness</th>
                        <th class="text-right bg-emerald-100 dark:bg-emerald-900/20">Final Payout</th>
                        <th class="text-center bg-gray-50 dark:bg-gray-800">Qualifiers</th>
                        <th class="text-center bg-gray-50 dark:bg-gray-800">Total Accelerators %</th>
                        <th class="text-right bg-gray-50 dark:bg-gray-800">Flavor of Month</th>
                        
                        <!-- Performance Metrics -->
                        <th class="text-center bg-gray-50 dark:bg-gray-800">High Priority Upgrade %</th>
                        <th class="text-center bg-blue-50 dark:bg-blue-900/10">Accel %</th>
                        <th class="text-center bg-gray-50 dark:bg-gray-800">20% VHI Close Rate</th>
                        <th class="text-right bg-gray-50 dark:bg-gray-800">FIOS Qty Sold</th>
                        <th class="text-right bg-gray-50 dark:bg-gray-800">VHI</th>
                        <th class="text-center bg-blue-50 dark:bg-blue-900/10">Accel %</th>
                        <th class="text-right bg-gray-50 dark:bg-gray-800">Upgrade Quantity</th>
                        <th class="text-center bg-gray-50 dark:bg-gray-800">Upgrade Conversion %</th>
                        <th class="text-center bg-blue-50 dark:bg-blue-900/10">Accel %</th>
                        <th class="text-right bg-gray-50 dark:bg-gray-800">CSGA</th>
                        <th class="text-center bg-gray-50 dark:bg-gray-800">Consumer SMT GA Conversion %</th>
                        <th class="text-center bg-blue-50 dark:bg-blue-900/10">Accel %</th>
                        <th class="text-center bg-gray-50 dark:bg-gray-800">VZ Protect %</th>
                        <th class="text-center bg-blue-50 dark:bg-blue-900/10">Accel %</th>
                        <th class="text-right bg-gray-50 dark:bg-gray-800">Take Rate for Registered Perks</th>
                        <th class="text-center bg-blue-50 dark:bg-blue-900/10">Accel %</th>
                        <th class="text-center bg-gray-50 dark:bg-gray-800">Premium Unlimited %</th>
                        <th class="text-center bg-blue-50 dark:bg-blue-900/10">Accel %</th>
                        <th class="text-right bg-gray-50 dark:bg-gray-800">SMB GA</th>
                        <th class="text-center bg-blue-50 dark:bg-blue-900/10">Accel %</th>
                        <th class="text-right bg-gray-50 dark:bg-gray-800">AGPPS on DP only</th>
                        <th class="text-center bg-blue-50 dark:bg-blue-900/10">Accel %</th>
                        <th class="text-center bg-gray-50 dark:bg-gray-800">Accounts Accessed %</th>
                        <th class="text-center bg-blue-50 dark:bg-blue-900/10">Accel %</th>
                        <th class="text-center bg-gray-50 dark:bg-gray-800">Manual Leads %</th>
                        <th class="text-center bg-blue-50 dark:bg-blue-900/10">Accel %</th>
                        <th class="text-center bg-gray-50 dark:bg-gray-800">Chatter Spot Opt In %</th>
                        <th class="text-center bg-blue-50 dark:bg-blue-900/10">Accel %</th>
                        <th class="text-center bg-gray-50 dark:bg-gray-800">Box Convertion</th>
                        <th class="text-center bg-gray-50 dark:bg-gray-800">Ready Go/Setup per SMT on DP</th>
                        <th class="text-right bg-gray-50 dark:bg-gray-800">Device Spiff</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 font-medium">
                    <?php foreach ($records as $r): ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition group">
                        <!-- Sticky Agent Name -->
                        <td class="sticky left-0 z-30 bg-white dark:bg-gray-900 group-hover:bg-gray-50 dark:group-hover:bg-gray-800/50 font-semibold text-gray-900 dark:text-white border-r border-gray-200 dark:border-gray-700 shadow-[2px_0_5px_rgba(0,0,0,0.1)] dark:shadow-[2px_0_5px_rgba(0,0,0,0.3)] min-w-[180px]">
                            <?= htmlspecialchars($r['agent_name_snapshot']) ?>
                        </td>
                        
                        <!-- Financial Data -->
                        <td class="text-right text-gray-600 dark:text-gray-300 bg-white/50 dark:bg-gray-900/50">$<?= number_format($r['gross_profit'] ?? 0, 2) ?></td>
                        <td class="text-right font-bold text-gray-900 dark:text-white bg-white/50 dark:bg-gray-900/50">$<?= number_format($r['net_gp'] ?? 0, 2) ?></td>
                        <td class="text-center text-gray-600 dark:text-gray-300 bg-white/50 dark:bg-gray-900/50"><?= htmlspecialchars($r['gp_spiff_qualified_pct'] ?? '-') ?></td>
                        <td class="text-right text-emerald-600 dark:text-emerald-400 bg-white/50 dark:bg-gray-900/50">$<?= number_format($r['total_gp_spiff_amt'] ?? 0, 2) ?></td>
                        <td class="text-right text-gray-500 dark:text-gray-400 bg-white/50 dark:bg-gray-900/50">$<?= number_format($r['gp_spiff_amt_accelerator'] ?? 0, 2) ?></td>
                        <td class="text-right text-gray-600 dark:text-gray-300 bg-white/50 dark:bg-gray-900/50">$<?= number_format($r['payout'] ?? 0, 2) ?></td>
                        <td class="text-right text-red-500 dark:text-red-400 bg-white/50 dark:bg-gray-900/50"><?= ($r['payout_cb'] ?? 0) != 0 ? '$'.number_format($r['payout_cb'], 2) : '-' ?></td>
                        <td class="text-right text-red-500 dark:text-red-400 bg-white/50 dark:bg-gray-900/50"><?= ($r['lateness'] ?? 0) != 0 ? '$'.number_format($r['lateness'], 2) : '-' ?></td>
                        <td class="text-right font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/10">$<?= number_format($r['final_payout'] ?? 0, 2) ?></td>
                        <td class="text-center bg-white/50 dark:bg-gray-900/50"><span class="px-2 py-0.5 rounded text-xs <?= ($r['qualifiers'] ?? 'No') === 'Yes' ? 'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-400' : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400' ?>"><?= htmlspecialchars($r['qualifiers'] ?? '-') ?></span></td>
                        <td class="text-center font-bold text-indigo-600 dark:text-indigo-400 bg-white/50 dark:bg-gray-900/50"><?= htmlspecialchars($r['total_accelerators_pct'] ?? '-') ?></td>
                        <td class="text-right text-gray-500 dark:text-gray-400 bg-white/50 dark:bg-gray-900/50">$<?= number_format($r['flavor_of_month'] ?? 0, 2) ?></td>
                        
                        <!-- Performance Metrics with Accelerators -->
                        <td class="text-center text-gray-600 dark:text-gray-300 bg-white/50 dark:bg-gray-900/50"><?= htmlspecialchars($r['priority_upgrade_pct'] ?? '-') ?></td>
                        <td class="text-center text-blue-600 dark:text-blue-400 bg-blue-50/50 dark:bg-blue-900/5"><?= htmlspecialchars($r['priority_upgrade_accel_pct'] ?? '-') ?></td>
                        <td class="text-center text-gray-600 dark:text-gray-300 bg-white/50 dark:bg-gray-900/50"><?= htmlspecialchars($r['vhi_close_rate_pct'] ?? '-') ?></td>
                        <td class="text-right text-gray-900 dark:text-white bg-white/50 dark:bg-gray-900/50"><?= $r['fios_qty_sold'] ?? 0 ?></td>
                        <td class="text-right text-gray-900 dark:text-white bg-white/50 dark:bg-gray-900/50"><?= $r['vhi'] ?? 0 ?></td>
                        <td class="text-center text-blue-600 dark:text-blue-400 bg-blue-50/50 dark:bg-blue-900/5"><?= htmlspecialchars($r['vhi_accel_pct'] ?? '-') ?></td>
                        <td class="text-right text-gray-900 dark:text-white bg-white/50 dark:bg-gray-900/50"><?= $r['upgrade_quantity'] ?? 0 ?></td>
                        <td class="text-center text-gray-600 dark:text-gray-300 bg-white/50 dark:bg-gray-900/50"><?= htmlspecialchars($r['upgrade_conversion_pct'] ?? '-') ?></td>
                        <td class="text-center text-blue-600 dark:text-blue-400 bg-blue-50/50 dark:bg-blue-900/5"><?= htmlspecialchars($r['upgrade_conversion_accel_pct'] ?? '-') ?></td>
                        <td class="text-right text-gray-900 dark:text-white bg-white/50 dark:bg-gray-900/50"><?= $r['smt_ga'] ?? 0 ?></td>
                        <td class="text-center text-gray-600 dark:text-gray-300 bg-white/50 dark:bg-gray-900/50"><?= htmlspecialchars($r['consumer_smt_ga_conversion_pct'] ?? '-') ?></td>
                        <td class="text-center text-blue-600 dark:text-blue-400 bg-blue-50/50 dark:bg-blue-900/5"><?= htmlspecialchars($r['consumer_smt_ga_conversion_accel_pct'] ?? '-') ?></td>
                        <td class="text-center text-gray-600 dark:text-gray-300 bg-white/50 dark:bg-gray-900/50"><?= htmlspecialchars($r['vz_protect_pct'] ?? '-') ?></td>
                        <td class="text-center text-blue-600 dark:text-blue-400 bg-blue-50/50 dark:bg-blue-900/5"><?= htmlspecialchars($r['vz_protect_accel_pct'] ?? '-') ?></td>
                        <td class="text-right text-gray-600 dark:text-gray-300 bg-white/50 dark:bg-gray-900/50"><?= number_format($r['take_rate_registered_perks'] ?? 0, 2) ?></td>
                        <td class="text-center text-blue-600 dark:text-blue-400 bg-blue-50/50 dark:bg-blue-900/5"><?= htmlspecialchars($r['take_rate_registered_perks_accel_pct'] ?? '-') ?></td>
                        <td class="text-center text-gray-600 dark:text-gray-300 bg-white/50 dark:bg-gray-900/50"><?= htmlspecialchars($r['premium_unlimited_pct'] ?? '-') ?></td>
                        <td class="text-center text-blue-600 dark:text-blue-400 bg-blue-50/50 dark:bg-blue-900/5"><?= htmlspecialchars($r['premium_unlimited_accel_pct'] ?? '-') ?></td>
                        <td class="text-right text-gray-900 dark:text-white bg-white/50 dark:bg-gray-900/50"><?= $r['smb_ga'] ?? 0 ?></td>
                        <td class="text-center text-blue-600 dark:text-blue-400 bg-blue-50/50 dark:bg-blue-900/5"><?= htmlspecialchars($r['smb_ga_accel_pct'] ?? '-') ?></td>
                        <td class="text-right text-gray-600 dark:text-gray-300 bg-white/50 dark:bg-gray-900/50"><?= number_format($r['agpps_dp_only'] ?? 0, 2) ?></td>
                        <td class="text-center text-blue-600 dark:text-blue-400 bg-blue-50/50 dark:bg-blue-900/5"><?= htmlspecialchars($r['agpps_dp_only_accel_pct'] ?? '-') ?></td>
                        <td class="text-center text-gray-600 dark:text-gray-300 bg-white/50 dark:bg-gray-900/50"><?= htmlspecialchars($r['accounts_accessed_pct'] ?? '-') ?></td>
                        <td class="text-center text-blue-600 dark:text-blue-400 bg-blue-50/50 dark:bg-blue-900/5"><?= htmlspecialchars($r['accounts_accessed_accel_pct'] ?? '-') ?></td>
                        <td class="text-center text-gray-600 dark:text-gray-300 bg-white/50 dark:bg-gray-900/50"><?= htmlspecialchars($r['manual_leads_pct'] ?? '-') ?></td>
                        <td class="text-center text-blue-600 dark:text-blue-400 bg-blue-50/50 dark:bg-blue-900/5"><?= htmlspecialchars($r['manual_leads_accel_pct'] ?? '-') ?></td>
                        <td class="text-center text-gray-600 dark:text-gray-300 bg-white/50 dark:bg-gray-900/50"><?= htmlspecialchars($r['chatter_spot_opt_in_pct'] ?? '-') ?></td>
                        <td class="text-center text-blue-600 dark:text-blue-400 bg-blue-50/50 dark:bg-blue-900/5"><?= htmlspecialchars($r['chatter_spot_opt_in_accel_pct'] ?? '-') ?></td>
                        <td class="text-center text-gray-600 dark:text-gray-300 bg-white/50 dark:bg-gray-900/50"><?= htmlspecialchars($r['box_conversion'] ?? '-') ?></td>
                        <td class="text-center text-gray-600 dark:text-gray-300 bg-white/50 dark:bg-gray-900/50"><?= htmlspecialchars($r['ready_go_setup_per_smt'] ?? '-') ?></td>
                        <td class="text-right text-amber-600 dark:text-amber-400 bg-white/50 dark:bg-gray-900/50">$<?= number_format($r['device_spiff'] ?? 0, 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const openBtn = document.getElementById('openSidebar');

        function toggleSidebar() {
            sidebar.classList.toggle('-translate-x-full');
        }

        if (openBtn) openBtn.addEventListener('click', toggleSidebar);

        // === Resizable Columns with localStorage persistence ===
        document.addEventListener('DOMContentLoaded', function() {
            const table = document.querySelector('table');
            const headers = table.querySelectorAll('th');
            const STORAGE_KEY = 'kpi_records_column_widths';
            
            // Load saved widths from localStorage
            let savedWidths = {};
            try {
                savedWidths = JSON.parse(localStorage.getItem(STORAGE_KEY)) || {};
            } catch (e) {
                savedWidths = {};
            }
            
            // Remove the instant CSS now that we're taking over with inline styles
            const instantStyle = document.getElementById('instant-column-widths');
            if (instantStyle) {
                instantStyle.remove();
            }
            
            // Step 1: Capture natural widths BEFORE applying fixed layout
            const widths = [];
            headers.forEach((header, index) => {
                // Use saved width if available, otherwise use natural width
                if (savedWidths[index]) {
                    widths.push(savedWidths[index]);
                } else {
                    widths.push(header.offsetWidth);
                }
            });
            
            // Function to save all widths to localStorage
            function saveWidths() {
                const currentWidths = {};
                headers.forEach((header, index) => {
                    currentWidths[index] = header.offsetWidth;
                });
                localStorage.setItem(STORAGE_KEY, JSON.stringify(currentWidths));
            }
            
            // Step 2: Apply widths and enable fixed layout for resizing
            headers.forEach((header, index) => {
                header.style.width = widths[index] + 'px';
                
                // Create resize handle
                const resizer = document.createElement('div');
                resizer.className = 'resizer';
                header.appendChild(resizer);
                
                let startX, startWidth;
                
                resizer.addEventListener('mousedown', function(e) {
                    startX = e.pageX;
                    startWidth = header.offsetWidth;
                    resizer.classList.add('resizing');
                    document.body.style.cursor = 'col-resize';
                    document.body.style.userSelect = 'none';
                    
                    document.addEventListener('mousemove', onMouseMove);
                    document.addEventListener('mouseup', onMouseUp);
                    e.preventDefault();
                    e.stopPropagation();
                });
                
                function onMouseMove(e) {
                    const width = startWidth + (e.pageX - startX);
                    if (width >= 40) {
                        header.style.width = width + 'px';
                    }
                }
                
                function onMouseUp() {
                    resizer.classList.remove('resizing');
                    document.body.style.cursor = '';
                    document.body.style.userSelect = '';
                    document.removeEventListener('mousemove', onMouseMove);
                    document.removeEventListener('mouseup', onMouseUp);
                    // Save widths after resize
                    saveWidths();
                }
                
                // Double-click to auto-fit
                resizer.addEventListener('dblclick', function(e) {
                    // Temporarily remove fixed width to measure content
                    header.style.width = 'auto';
                    const autoWidth = header.scrollWidth + 20;
                    header.style.width = autoWidth + 'px';
                    e.stopPropagation();
                    // Save after auto-fit
                    saveWidths();
                });
            });
            
            // Step 3: Apply fixed table layout
            table.classList.add('resizable');
        });
        
        // === Help Tooltip Toggle ===
        const helpBtn = document.getElementById('helpBtn');
        const helpTooltip = document.getElementById('helpTooltip');
        const closeHelp = document.getElementById('closeHelp');
        
        if (helpBtn && helpTooltip) {
            helpBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                helpTooltip.classList.toggle('hidden');
            });
            
            if (closeHelp) {
                closeHelp.addEventListener('click', function() {
                    helpTooltip.classList.add('hidden');
                });
            }
            
            // Close when clicking outside
            document.addEventListener('click', function(e) {
                if (!helpTooltip.contains(e.target) && e.target !== helpBtn) {
                    helpTooltip.classList.add('hidden');
                }
            });
        }
        
        // === Reset Columns Button ===
        const resetColumnsBtn = document.getElementById('resetColumnsBtn');
        if (resetColumnsBtn) {
            resetColumnsBtn.addEventListener('click', function() {
                // Clear saved column widths from localStorage
                localStorage.removeItem('kpi_records_column_widths');
                // Reload page to apply default widths
                window.location.reload();
            });
        }
    </script>
</body>
</html>
