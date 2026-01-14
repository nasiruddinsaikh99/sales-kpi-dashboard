<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Dashboard - Sales KPI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background: #0B1120;
            color: white;
            font-family: 'Inter', sans-serif;
        }
        .glass-panel {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1rem;
        }
        .stat-card { transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-2px); }
        .grid-header { font-size: 0.75rem; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem; }
    </style>
</head>
<body class="min-h-screen pb-12">

    <!-- Header -->
    <header class="border-b border-gray-800 bg-gray-900/50 backdrop-blur sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-blue-500 to-purple-500 flex items-center justify-center font-bold text-sm">
                    <?= substr($_SESSION['user_name'], 0, 1) ?>
                </div>
                <span class="font-semibold text-gray-200"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
            </div>
            
            <div class="flex items-center gap-4">
                <form method="GET" class="flex items-center gap-2">
                    <select name="month" onchange="this.form.submit()" 
                        class="bg-gray-800 border-gray-700 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 text-white">
                        <?php foreach($history as $h): ?>
                             <?php 
                                $optVal = $h['month']; 
                                $optLabel = date('F Y', strtotime($h['month'])); 
                             ?>
                             <option value="<?= $optVal ?>" <?= $selectedMonth == $optVal ? 'selected' : '' ?>>
                                <?= $optLabel ?>
                             </option>
                        <?php endforeach; ?>
                        <?php if(empty($history)): ?>
                            <option>No Data Available</option>
                        <?php endif; ?>
                    </select>
                </form>
                <a href="/sales-kpi-dashboard/logout" class="text-sm text-gray-400 hover:text-white transition">Logout</a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <?php if (!$currentRecord): ?>
            <div class="text-center py-20 text-gray-500">
                <h3 class="text-xl">No data found for this period.</h3>
            </div>
        <?php else: ?>

        <!-- Key Metrics Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Net GP -->
            <div class="glass-panel p-6 stat-card border-l-4 border-blue-500">
                <p class="text-gray-400 text-sm font-medium uppercase tracking-wider">Net GP</p>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-3xl font-bold text-white">$<?= number_format($currentRecord['net_gp'], 2) ?></span>
                </div>
            </div>

            <!-- Gross Profit -->
            <div class="glass-panel p-6 stat-card border-l-4 border-emerald-500">
                <p class="text-gray-400 text-sm font-medium uppercase tracking-wider">Gross Profit</p>
                <div class="mt-2">
                    <span class="text-3xl font-bold text-white">$<?= number_format($currentRecord['gross_profit'], 2) ?></span>
                </div>
            </div>

            <!-- Total Spiff -->
            <div class="glass-panel p-6 stat-card border-l-4 border-purple-500">
                <p class="text-gray-400 text-sm font-medium uppercase tracking-wider">Total Commission</p>
                <div class="mt-2">
                    <span class="text-3xl font-bold text-white">$<?= number_format($currentRecord['total_gp_spiff_amt'], 2) ?></span>
                </div>
            </div>

            <!-- Final Payout -->
            <div class="glass-panel p-6 stat-card border-l-4 border-amber-500">
                <p class="text-gray-400 text-sm font-medium uppercase tracking-wider">Final Payout</p>
                <div class="mt-2">
                    <span class="text-3xl font-bold text-white">$<?= number_format($currentRecord['final_payout'] ?? 0, 2) ?></span>
                </div>
            </div>
        </div>

        <!-- Charts & Details -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            
            <!-- Main Chart -->
            <div class="lg:col-span-2 glass-panel p-6">
                <h3 class="text-lg font-semibold mb-6 flex items-center gap-2">
                    <span class="w-2 h-6 bg-blue-500 rounded-full"></span>
                    Performance Trend (Net GP)
                </h3>
                <div class="h-[300px] w-full">
                    <canvas id="performanceChart"></canvas>
                </div>
            </div>

            <!-- Financial Breakdown -->
            <div class="glass-panel p-6">
                <h3 class="text-lg font-semibold mb-6">Financial Summary</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center p-3 bg-gray-800/50 rounded-lg">
                        <span class="text-gray-400">Chargebacks</span>
                        <span class="text-red-400 font-mono">$<?= number_format($currentRecord['chargeback'], 2) ?></span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-gray-800/50 rounded-lg">
                        <span class="text-gray-400">Accelerator Amt</span>
                        <span class="text-emerald-400 font-mono">$<?= number_format($currentRecord['gp_spiff_amt_accelerator'], 2) ?></span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-gray-800/50 rounded-lg">
                        <span class="text-gray-400">Lateness</span>
                        <span class="text-red-400 font-mono">$<?= number_format($currentRecord['lateness'] ?? 0, 2) ?></span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-gray-800/50 rounded-lg">
                        <span class="text-gray-400">Device Spiff</span>
                        <span class="text-blue-400 font-mono">$<?= number_format($currentRecord['device_spiff'] ?? 0, 2) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed KPI Grid -->
        <div class="glass-panel p-6">
            <h3 class="text-lg font-semibold mb-6 flex items-center gap-2">
                <span class="w-2 h-6 bg-emerald-500 rounded-full"></span>
                Detailed Metrics & Accelerators
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Group 1: General Stats -->
                <div class="space-y-4">
                    <h4 class="grid-header">General Stats</h4>
                    <div class="bg-gray-800/30 p-3 rounded lg-card">
                        <p class="text-xs text-gray-400">Qualifiers</p>
                        <p class="font-semibold text-white"><?= htmlspecialchars($currentRecord['qualifiers'] ?? '-') ?></p>
                    </div>
                    <div class="bg-gray-800/30 p-3 rounded lg-card">
                        <p class="text-xs text-gray-400">Flavor of Month</p>
                        <p class="font-semibold text-emerald-400">$<?= number_format($currentRecord['flavor_of_month'] ?? 0, 2) ?></p>
                    </div>
                    <div class="bg-gray-800/30 p-3 rounded lg-card">
                        <p class="text-xs text-gray-400">Fios Qty Sold</p>
                        <p class="font-semibold text-white"><?= $currentRecord['fios_qty_sold'] ?? 0 ?></p>
                    </div>
                </div>

                <!-- Group 2: Upgrades -->
                <div class="space-y-4">
                    <h4 class="grid-header">Upgrades</h4>
                    <div class="bg-gray-800/30 p-3 rounded lg-card">
                        <p class="text-xs text-gray-400">Priority Upgrade %</p>
                        <div class="flex justify-between">
                            <span class="font-semibold"><?= $currentRecord['priority_upgrade_pct'] ?? '0%' ?></span>
                            <span class="text-xs bg-blue-500/20 text-blue-400 px-2 py-0.5 rounded">Accel: <?= $currentRecord['priority_upgrade_accel_pct'] ?? '0%' ?></span>
                        </div>
                    </div>
                    <div class="bg-gray-800/30 p-3 rounded lg-card">
                        <p class="text-xs text-gray-400">Upgrade Conversion</p>
                        <div class="flex justify-between">
                            <span class="font-semibold"><?= $currentRecord['upgrade_conversion_pct'] ?? '0%' ?></span>
                            <span class="text-xs bg-blue-500/20 text-blue-400 px-2 py-0.5 rounded">Accel: <?= $currentRecord['upgrade_conversion_accel_pct'] ?? '0%' ?></span>
                        </div>
                    </div>
                    <div class="bg-gray-800/30 p-3 rounded lg-card">
                        <p class="text-xs text-gray-400">Upgrade Quantity</p>
                        <p class="font-semibold"><?= $currentRecord['upgrade_quantity'] ?? 0 ?></p>
                    </div>
                </div>

                <!-- Group 3: SMT & VZ -->
                <div class="space-y-4">
                    <h4 class="grid-header">SMT & Protection</h4>
                    <div class="bg-gray-800/30 p-3 rounded lg-card">
                        <p class="text-xs text-gray-400">Cons. SMT GA Conv.</p>
                        <div class="flex justify-between">
                            <span class="font-semibold"><?= $currentRecord['consumer_smt_ga_conversion_pct'] ?? '0%' ?></span>
                            <span class="text-xs bg-blue-500/20 text-blue-400 px-2 py-0.5 rounded">Accel: <?= $currentRecord['consumer_smt_ga_conversion_accel_pct'] ?? '0%' ?></span>
                        </div>
                    </div>
                    <div class="bg-gray-800/30 p-3 rounded lg-card">
                        <p class="text-xs text-gray-400">VZ Protect %</p>
                        <div class="flex justify-between">
                            <span class="font-semibold"><?= $currentRecord['vz_protect_pct'] ?? '0%' ?></span>
                            <span class="text-xs bg-blue-500/20 text-blue-400 px-2 py-0.5 rounded">Accel: <?= $currentRecord['vz_protect_accel_pct'] ?? '0%' ?></span>
                        </div>
                    </div>
                    <div class="bg-gray-800/30 p-3 rounded lg-card">
                        <p class="text-xs text-gray-400">Premium Unlimited %</p>
                        <div class="flex justify-between">
                            <span class="font-semibold"><?= $currentRecord['premium_unlimited_pct'] ?? '0%' ?></span>
                            <span class="text-xs bg-blue-500/20 text-blue-400 px-2 py-0.5 rounded">Accel: <?= $currentRecord['premium_unlimited_accel_pct'] ?? '0%' ?></span>
                        </div>
                    </div>
                </div>

                <!-- Group 4: Other KPIs -->
                <div class="space-y-4">
                    <h4 class="grid-header">Other KPIs</h4>
                    <div class="bg-gray-800/30 p-3 rounded lg-card">
                        <p class="text-xs text-gray-400">VHI</p>
                        <div class="flex justify-between">
                            <span class="font-semibold"><?= $currentRecord['vhi'] ?? 0 ?></span>
                            <span class="text-xs bg-blue-500/20 text-blue-400 px-2 py-0.5 rounded">Accel: <?= $currentRecord['vhi_accel_pct'] ?? '0%' ?></span>
                        </div>
                    </div>
                    <div class="bg-gray-800/30 p-3 rounded lg-card">
                        <p class="text-xs text-gray-400">SMB GA</p>
                        <div class="flex justify-between">
                            <span class="font-semibold"><?= $currentRecord['smb_ga'] ?? 0 ?></span>
                            <span class="text-xs bg-blue-500/20 text-blue-400 px-2 py-0.5 rounded">Accel: <?= $currentRecord['smb_ga_accel_pct'] ?? '0%' ?></span>
                        </div>
                    </div>
                    <div class="bg-gray-800/30 p-3 rounded lg-card">
                        <p class="text-xs text-gray-400">Total Accel %</p>
                        <p class="font-bold text-lg text-emerald-400"><?= $currentRecord['total_accelerators_pct'] ?? '0%' ?></p>
                    </div>
                </div>
            </div>
        </div>

        <?php endif; ?>
    </main>

    <script>
        const ctx = document.getElementById('performanceChart').getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(59, 130, 246, 0.5)'); // Blue start
        gradient.addColorStop(1, 'rgba(59, 130, 246, 0.0)'); // Transparent end

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($chartLabels) ?>,
                datasets: [{
                    label: 'Net GP',
                    data: <?= json_encode($chartData) ?>,
                    borderColor: '#3b82f6',
                    backgroundColor: gradient,
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#1d4ed8'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(255, 255, 255, 0.05)' },
                        ticks: { color: '#9ca3af' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#9ca3af' }
                    }
                }
            }
        });
    </script>
</body>
</html>
