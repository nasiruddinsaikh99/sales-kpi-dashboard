<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Records - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: #0f172a; color: #e2e8f0; }
        .card { background: #1e293b; border: 1px solid #334155; }
        /* Ultra wide table scroll */
        .table-container { overflow-x: auto; max-width: 100vw; }
        th, td { white-space: nowrap; padding: 0.75rem 1.5rem; }
        .sticky-col { position: sticky; left: 0; background: #1f2937; z-index: 10; border-right: 1px solid #374151; }
        .head-grp { border-bottom: 2px solid #4b5563; text-align: center; }
        
        /* Custom Scrollbar for better UI */
        .custom-scrollbar::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #1e293b; 
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #475569; 
            border-radius: 5px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #64748b; 
        }
    </style>
</head>
<body class="h-screen bg-[#0f172a] text-[#e2e8f0] flex overflow-hidden">

    <!-- Sidebar -->
    <?php include __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col md:ml-64 transition-all duration-300 w-full h-full overflow-hidden">
        
        <!-- Mobile Header -->
        <header class="md:hidden bg-gray-900 border-b border-gray-800 p-4 flex justify-between items-center flex-shrink-0">
            <h1 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-400 to-emerald-400">Syntrex Admin</h1>
            <button id="openSidebar" class="text-gray-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
        </header>

        <main class="flex-grow p-4 md:p-6 w-full h-full overflow-hidden flex flex-col min-h-0">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4 flex-shrink-0">
                <div>
                    <h2 class="text-2xl font-bold text-white"><?= htmlspecialchars($upload['batch_name']) ?></h2>
                    <p class="text-gray-400 text-sm">Month: <span class="text-indigo-300"><?= htmlspecialchars($upload['for_month']) ?></span> &bull; Status: Active</p>
                </div>
                <div class="flex gap-3">
                    <a href="/sales-kpi-dashboard/admin/dashboard" class="px-4 py-2 border border-gray-600 hover:bg-gray-800 rounded-lg text-sm text-gray-300 transition">Back to List</a>
                    <!-- Placeholder for Export Button -->
                    <button class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-sm shadow-lg flex items-center gap-2 transition opacity-50 cursor-not-allowed" disabled title="Coming Soon">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Export CSV
                    </button>
                </div>
            </div>

            <div class="card rounded-xl shadow-xl table-container flex-grow border border-gray-700 overflow-auto relative bg-gray-900 custom-scrollbar min-h-0">
                <table class="w-full text-left text-sm border-collapse">
                <thead class="bg-gray-800 text-gray-400 uppercase font-bold sticky top-0 z-40 shadow-sm">
                    <tr>
                        <th class="sticky left-0 z-50 bg-gray-800 border-r border-gray-700 shadow-[2px_0_5px_rgba(0,0,0,0.3)]">Agent Name</th>
                        
                        <!-- Financials -->
                        <th class="bg-gray-800">Type</th>
                        <th class="text-right bg-gray-800">Gross Profit</th>
                        <th class="text-right bg-gray-800">Chargeback</th>
                        <th class="text-right bg-gray-800">Net GP</th>
                        <th class="text-right bg-gray-800">Total Comm.</th>
                        <th class="text-right bg-gray-800">Final Payout</th>
                        <th class="text-right bg-gray-800">Payout CB</th>
                        <th class="text-right bg-gray-800">Lateness</th>
                        
                        <!-- Core Stats -->
                        <th class="bg-gray-800">Qualifiers</th>
                        <th class="text-right bg-gray-800">Flavor of Month</th>
                        <th class="text-right bg-gray-800">Device Spiff</th>
                        <th class="text-right bg-gray-800">Fios Sold</th>
                        <th class="text-right bg-gray-800">VHI</th>
                        <th class="text-right bg-gray-800">Upgrade Qty</th>
                        <th class="text-right bg-gray-800">SMT GA</th>
                        <th class="text-right bg-gray-800">SMB GA</th>
                        
                        <!-- Percentages & Accelerators -->
                        <th class="text-center bg-gray-800">Priority Upg %</th>
                        <th class="text-center bg-gray-800">Upg Conv %</th>
                        <th class="text-center bg-gray-800">Cons. SMT Conv %</th>
                        <th class="text-center bg-gray-800">VZ Protect %</th>
                        <th class="text-center bg-gray-800">Premium Unl. %</th>
                        <th class="text-center bg-gray-800">Total Accel %</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700 font-medium">
                    <?php foreach ($records as $r): ?>
                    <tr class="hover:bg-gray-800/50 transition opacity-90 hover:opacity-100">
                        <td class="sticky left-0 z-30 bg-gray-900 font-medium text-white border-r border-gray-700 shadow-[2px_0_5px_rgba(0,0,0,0.3)] min-w-[200px] whitespace-nowrap">
                            <?= htmlspecialchars($r['agent_name_snapshot']) ?>
                        </td>
                        
                        <td class="bg-gray-900/50"><span class="px-2 py-0.5 rounded bg-blue-500/10 text-blue-400 text-xs border border-blue-500/20">Agent</span></td>
                        
                        <td class="text-right text-gray-300 bg-gray-900/50">$<?= number_format($r['gross_profit'], 2) ?></td>
                        <td class="text-right text-red-400 bg-gray-900/50">$<?= number_format($r['chargeback'], 2) ?></td>
                        <td class="text-right font-bold text-white bg-gray-900/50">$<?= number_format($r['net_gp'], 2) ?></td>
                        <td class="text-right text-emerald-400 font-bold bg-gray-900/50">$<?= number_format($r['total_gp_spiff_amt'], 2) ?></td>
                        <td class="text-right text-amber-400 font-bold border-l border-gray-700 pl-4 bg-gray-900/50">$<?= number_format($r['final_payout'] ?? 0, 2) ?></td>
                        <td class="text-center text-gray-500 bg-gray-900/50"><?= $r['payout_cb'] ? '$'.number_format($r['payout_cb'],2) : '-' ?></td>
                        <td class="text-center text-red-400 bg-gray-900/50"><?= $r['lateness'] ? '$'.number_format($r['lateness'],2) : '-' ?></td>
                        
                        <td class="text-center font-mono text-gray-300 bg-gray-900/50"><?= htmlspecialchars($r['qualifiers'] ?? '-') ?></td>
                        <td class="text-right text-gray-400 bg-gray-900/50">$<?= number_format($r['flavor_of_month'] ?? 0, 2) ?></td>
                        <td class="text-right text-gray-400 bg-gray-900/50">$<?= number_format($r['device_spiff'] ?? 0, 2) ?></td>
                        <td class="text-right text-white bg-gray-900/50"><?= $r['fios_qty_sold'] ?? 0 ?></td>
                        <td class="text-right text-white bg-gray-900/50"><?= $r['vhi'] ?? 0 ?> <span class="text-xs text-gray-500">(<?= $r['vhi_accel_pct'] ?? '0%' ?>)</span></td>
                        <td class="text-right text-white bg-gray-900/50"><?= $r['upgrade_quantity'] ?? 0 ?></td>
                        <td class="text-right text-white bg-gray-900/50"><?= $r['smt_ga'] ?? 0 ?></td>
                        <td class="text-right text-white bg-gray-900/50"><?= $r['smb_ga'] ?? 0 ?> <span class="text-xs text-gray-500 ml-1">(<?= $r['smb_ga_accel_pct'] ?? '0%' ?>)</span></td>
                        
                        <td class="text-center text-sm bg-gray-900/50"><?= $r['priority_upgrade_pct'] ?? '-' ?> <span class="block text-[10px] text-gray-500">Ac:<?= $r['priority_upgrade_accel_pct'] ?? '-' ?></span></td>
                        <td class="text-center text-sm bg-gray-900/50"><?= $r['upgrade_conversion_pct'] ?? '-' ?> <span class="block text-[10px] text-gray-500">Ac:<?= $r['upgrade_conversion_accel_pct'] ?? '-' ?></span></td>
                        <td class="text-center text-sm bg-gray-900/50"><?= $r['consumer_smt_ga_conversion_pct'] ?? '-' ?> <span class="block text-[10px] text-gray-500">Ac:<?= $r['consumer_smt_ga_conversion_accel_pct'] ?? '-' ?></span></td>
                        <td class="text-center text-sm bg-gray-900/50"><?= $r['vz_protect_pct'] ?? '-' ?> <span class="block text-[10px] text-gray-500">Ac:<?= $r['vz_protect_accel_pct'] ?? '-' ?></span></td>
                        <td class="text-center text-sm bg-gray-900/50"><?= $r['premium_unlimited_pct'] ?? '-' ?> <span class="block text-[10px] text-gray-500">Ac:<?= $r['premium_unlimited_accel_pct'] ?? '-' ?></span></td>
                        <td class="text-center font-bold text-emerald-400 bg-emerald-900/10 border-l border-r border-gray-700"><?= $r['total_accelerators_pct'] ?? '-' ?></td>
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
        const closeBtn = document.getElementById('closeSidebar');
        // No overlay needed for full width scrolling table usually, but consistent script
        
        function toggleSidebar() {
            sidebar.classList.toggle('-translate-x-full');
        }

        if (openBtn) openBtn.addEventListener('click', toggleSidebar);
        if (closeBtn) closeBtn.addEventListener('click', toggleSidebar);
    </script>
</body>
</html>
