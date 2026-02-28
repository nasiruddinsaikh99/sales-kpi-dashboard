<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Dashboard - Sales KPI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php include __DIR__ . '/../partials/theme_script.php'; ?>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .glass-panel {
            background: rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(0, 0, 0, 0.08);
            backdrop-filter: blur(12px);
            border-radius: 1rem;
        }
        .dark .glass-panel {
            background: rgba(30, 41, 59, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .stat-card { transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-2px); }
        .grid-header { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem; }
    </style>
</head>
<body class="min-h-screen pb-12 bg-slate-50 dark:bg-[#0B1120] text-slate-900 dark:text-white transition-colors duration-300">

    <!-- Header -->
    <header class="border-b border-gray-200 dark:border-gray-800 bg-white/80 dark:bg-gray-900/50 backdrop-blur sticky top-0 z-50 transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-6">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-blue-500 to-purple-500 flex items-center justify-center font-bold text-sm text-white">
                        <?= substr($_SESSION['user_name'], 0, 1) ?>
                    </div>
                    <span class="font-semibold text-gray-700 dark:text-gray-200"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                </div>

                <!-- Tab Navigation -->
                <nav class="flex gap-1">
                    <a href="/sales-kpi-dashboard/agent/dashboard"
                       class="px-4 py-2 rounded-lg text-sm font-medium bg-indigo-100 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 transition">
                        Dashboard
                    </a>
                    <a href="/sales-kpi-dashboard/agent/communications" id="communicationsTab"
                       class="px-4 py-2 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition relative">
                        Communications
                        <span id="unreadBadge" class="hidden absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold"></span>
                    </a>
                </nav>
            </div>

            <div class="flex items-center gap-4">
                <form method="GET" class="flex items-center gap-2">
                    <select name="month" onchange="this.form.submit()"
                        class="bg-gray-50 dark:bg-gray-800 border-gray-300 dark:border-gray-700 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 text-gray-900 dark:text-white transition-colors">
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
                <a href="/sales-kpi-dashboard/logout" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition">Logout</a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 transition-colors duration-300">
        
        <?php if (!$currentRecord): ?>
            <div class="text-center py-20 text-gray-500 dark:text-gray-400">
                <h3 class="text-xl">No data found for this period.</h3>
            </div>
        <?php else: ?>

        <!-- Row 1: Top Financial Cards (5 cards) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
            <div class="glass-panel p-6 stat-card border-l-4 border-blue-500 bg-white dark:bg-slate-800 shadow-sm flex flex-col">
                <p class="text-gray-500 dark:text-gray-400 text-sm font-medium uppercase tracking-wider min-h-[40px]">Gross Profit</p>
                <div class="mt-auto">
                    <span class="text-3xl font-bold text-gray-900 dark:text-white">$<?= number_format($currentRecord['gross_profit'], 2) ?></span>
                </div>
            </div>
            <div class="glass-panel p-6 stat-card border-l-4 border-indigo-500 bg-white dark:bg-slate-800 shadow-sm flex flex-col">
                <p class="text-gray-500 dark:text-gray-400 text-sm font-medium uppercase tracking-wider min-h-[40px]">Net GP After Chargeback</p>
                <div class="mt-auto">
                    <span class="text-3xl font-bold text-gray-900 dark:text-white">$<?= number_format($currentRecord['net_gp'], 2) ?></span>
                </div>
            </div>
            <div class="glass-panel p-6 stat-card border-l-4 border-purple-500 bg-white dark:bg-slate-800 shadow-sm flex flex-col">
                <p class="text-gray-500 dark:text-gray-400 text-sm font-medium uppercase tracking-wider min-h-[40px]">GP Commission</p>
                <div class="mt-auto">
                    <span class="text-3xl font-bold text-gray-900 dark:text-white">$<?= number_format($currentRecord['total_gp_spiff_amt'] ?? 0, 2) ?></span>
                </div>
            </div>
            <div class="glass-panel p-6 stat-card border-l-4 border-cyan-500 bg-white dark:bg-slate-800 shadow-sm flex flex-col">
                <p class="text-gray-500 dark:text-gray-400 text-sm font-medium uppercase tracking-wider min-h-[40px]">GP Spiff Amt for Accelerator</p>
                <div class="mt-auto">
                    <span class="text-3xl font-bold text-gray-900 dark:text-white">$<?= number_format($currentRecord['gp_spiff_amt_accelerator'], 2) ?></span>
                </div>
            </div>
            <div class="glass-panel p-6 stat-card border-l-4 border-emerald-500 bg-white dark:bg-slate-800 shadow-sm flex flex-col">
                <p class="text-gray-500 dark:text-gray-400 text-sm font-medium uppercase tracking-wider min-h-[40px]">Final Payout</p>
                <div class="mt-auto">
                    <span class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">$<?= number_format($currentRecord['final_payout'] ?? 0, 2) ?></span>
                </div>
            </div>
        </div>

        <!-- Row 2: Chart + Right Panels -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <!-- Performance Trend Chart (spans 2 columns) -->
            <div class="lg:col-span-2 glass-panel p-6 bg-white dark:bg-slate-800 shadow-sm">
                <h3 class="text-lg font-semibold mb-6 flex items-center gap-2 text-gray-900 dark:text-white">
                    <span class="w-2 h-6 bg-blue-500 rounded-full"></span>
                    Performance Trend
                </h3>
                <div class="h-[300px] w-full">
                    <canvas id="performanceChart"></canvas>
                </div>
            </div>

            <!-- Right Column: Stacked Panels -->
            <div class="flex flex-col gap-6">
                <!-- Deduction Summary -->
                <div class="glass-panel p-6 bg-white dark:bg-slate-800 shadow-sm">
                    <h3 class="text-lg font-semibold mb-6 text-gray-900 dark:text-white">Deduction Summary</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                            <span class="text-gray-600 dark:text-gray-400">Payout Chargeback</span>
                            <span class="text-red-500 dark:text-red-400 font-mono">$<?= number_format($currentRecord['chargeback'], 2) ?></span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                            <span class="text-gray-600 dark:text-gray-400">Lateness</span>
                            <span class="text-red-500 dark:text-red-400 font-mono">$<?= number_format($currentRecord['lateness'] ?? 0, 2) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Loyalty Reward -->
                <div class="glass-panel p-6 bg-white dark:bg-slate-800 shadow-sm">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Loyalty Reward</h3>
                    <div class="text-center py-4">
                        <span class="text-4xl font-bold text-emerald-600 dark:text-emerald-400">$<?= number_format($currentRecord['flavor_of_month'] ?? 0, 2) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 3: Spiff Qualifiers -->
        <div class="glass-panel p-6 bg-white dark:bg-slate-800 shadow-sm mb-8">
            <h3 class="text-lg font-semibold mb-6 flex items-center gap-2 text-gray-900 dark:text-white">
                <span class="w-2 h-6 bg-emerald-500 rounded-full"></span>
                Spiff Qualifiers and Percentages
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-gray-50 dark:bg-gray-800/30 p-3 rounded-lg">
                    <p class="text-xs text-gray-500 dark:text-gray-400">GP Spiff Qualified %</p>
                    <p class="font-bold text-lg text-gray-900 dark:text-white"><?= $currentRecord['gp_spiff_qualified_pct'] ?? '0%' ?></p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-800/30 p-3 rounded-lg">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Accelerator Qualifier</p>
                    <p class="font-bold text-lg text-gray-900 dark:text-white"><?= $currentRecord['total_accelerators_pct'] ?? '0%' ?></p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-800/30 p-3 rounded-lg">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Box Conversion</p>
                    <p class="font-bold text-lg text-gray-900 dark:text-white"><?= $currentRecord['box_conversion'] ?? '-' ?></p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-800/30 p-3 rounded-lg">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Ready Go%</p>
                    <p class="font-bold text-lg text-gray-900 dark:text-white"><?= $currentRecord['ready_go_setup_per_smt'] ?? '-' ?></p>
                </div>
            </div>
        </div>

        <!-- Row 4: Detailed Metrics -->
        <div class="glass-panel p-6 bg-white dark:bg-slate-800 shadow-sm">
            <h3 class="text-lg font-semibold mb-6 flex items-center gap-2 text-gray-900 dark:text-white">
                <span class="w-2 h-6 bg-purple-500 rounded-full"></span>
                Detailed Metrics
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Power Six Column 1 -->
                <div class="space-y-4">
                    <h4 class="grid-header text-gray-500 dark:text-gray-400">Power Six</h4>
                    <div class="bg-gray-50 dark:bg-gray-800/30 p-3 rounded-lg">
                        <p class="text-xs text-gray-500 dark:text-gray-400">High Priority Upgrade</p>
                        <div class="flex justify-between">
                            <span class="font-semibold text-gray-900 dark:text-white"><?= $currentRecord['priority_upgrade_pct'] ?? '0%' ?></span>
                            <span class="text-xs bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 px-2 py-0.5 rounded"><?= $currentRecord['priority_upgrade_accel_pct'] ?? '0%' ?></span>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800/30 p-3 rounded-lg">
                        <p class="text-xs text-gray-500 dark:text-gray-400">VHI Close Rate % (FWA/Fios)</p>
                        <div class="flex justify-between">
                            <span class="font-semibold text-gray-900 dark:text-white"><?= $currentRecord['vhi_close_rate_pct'] ?? '0%' ?></span>
                            <span class="text-xs bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 px-2 py-0.5 rounded"><?= $currentRecord['vhi_accel_pct'] ?? '0%' ?></span>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800/30 p-3 rounded-lg">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Upgrade Conversion</p>
                        <div class="flex justify-between">
                            <span class="font-semibold text-gray-900 dark:text-white"><?= $currentRecord['upgrade_conversion_pct'] ?? '0%' ?></span>
                            <span class="text-xs bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 px-2 py-0.5 rounded"><?= $currentRecord['upgrade_conversion_accel_pct'] ?? '0%' ?></span>
                        </div>
                    </div>
                </div>

                <!-- Power Six Column 2 -->
                <div class="space-y-4">
                    <h4 class="grid-header text-gray-500 dark:text-gray-400">&nbsp;</h4>
                    <div class="bg-gray-50 dark:bg-gray-800/30 p-3 rounded-lg">
                        <p class="text-xs text-gray-500 dark:text-gray-400">CSGA</p>
                        <div class="flex justify-between">
                            <span class="font-semibold text-gray-900 dark:text-white"><?= $currentRecord['consumer_smt_ga_conversion_pct'] ?? '0%' ?></span>
                            <span class="text-xs bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 px-2 py-0.5 rounded"><?= $currentRecord['consumer_smt_ga_conversion_accel_pct'] ?? '0%' ?></span>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800/30 p-3 rounded-lg">
                        <p class="text-xs text-gray-500 dark:text-gray-400">VZ Protection</p>
                        <div class="flex justify-between">
                            <span class="font-semibold text-gray-900 dark:text-white"><?= $currentRecord['vz_protect_pct'] ?? '0%' ?></span>
                            <span class="text-xs bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 px-2 py-0.5 rounded"><?= $currentRecord['vz_protect_accel_pct'] ?? '0%' ?></span>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800/30 p-3 rounded-lg">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Take Rate for Registered Perks</p>
                        <div class="flex justify-between">
                            <span class="font-semibold text-gray-900 dark:text-white"><?= $currentRecord['take_rate_registered_perks'] ?? '0' ?></span>
                            <span class="text-xs bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 px-2 py-0.5 rounded"><?= $currentRecord['take_rate_registered_perks_accel_pct'] ?? '0%' ?></span>
                        </div>
                    </div>
                </div>

                <!-- Swing Metrics -->
                <div class="space-y-4">
                    <h4 class="grid-header text-amber-600 dark:text-amber-400">Swing Metrics</h4>
                    <div class="bg-gray-50 dark:bg-gray-800/30 p-3 rounded-lg">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Premium Unlimited</p>
                        <div class="flex justify-between">
                            <span class="font-semibold text-gray-900 dark:text-white"><?= $currentRecord['premium_unlimited_pct'] ?? '0%' ?></span>
                            <span class="text-xs bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 px-2 py-0.5 rounded"><?= $currentRecord['premium_unlimited_accel_pct'] ?? '0%' ?></span>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800/30 p-3 rounded-lg">
                        <p class="text-xs text-gray-500 dark:text-gray-400">AAL (CSGA BYOD+, eSim, DPP)</p>
                        <div class="flex justify-between">
                            <span class="font-semibold text-gray-900 dark:text-white"><?= $currentRecord['smt_ga'] ?? '0' ?></span>
                            <span class="text-xs bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 px-2 py-0.5 rounded"><?= $currentRecord['smb_ga_accel_pct'] ?? '0%' ?></span>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800/30 p-3 rounded-lg">
                        <p class="text-xs text-gray-500 dark:text-gray-400">VHDP Protection</p>
                        <div class="flex justify-between">
                            <span class="font-semibold text-gray-900 dark:text-white"><?= $currentRecord['accounts_accessed_pct'] ?? '0%' ?></span>
                            <span class="text-xs bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 px-2 py-0.5 rounded"><?= $currentRecord['accounts_accessed_accel_pct'] ?? '0%' ?></span>
                        </div>
                    </div>
                </div>

                <!-- Device Spiff -->
                <div class="space-y-4">
                    <h4 class="grid-header text-purple-600 dark:text-purple-400">Device Spiff</h4>
                    <div class="bg-gray-50 dark:bg-gray-800/30 p-4 rounded-lg">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Total Device Spiff</p>
                        <p class="text-3xl font-bold text-purple-600 dark:text-purple-400">$<?= number_format($currentRecord['device_spiff'] ?? 0, 2) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <?php endif; ?>
    </main>

    <script>
        const ctx = document.getElementById('performanceChart').getContext('2d');
        
        function getThemeColors() {
            const isDark = document.documentElement.classList.contains('dark');
            return {
                grid: isDark ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.05)',
                tick: isDark ? '#9ca3af' : '#64748b'
            };
        }

        const colors = getThemeColors();
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(59, 130, 246, 0.5)');
        gradient.addColorStop(1, 'rgba(59, 130, 246, 0.0)');

        const myChart = new Chart(ctx, {
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
                        grid: { color: colors.grid },
                        ticks: { color: colors.tick }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: colors.tick }
                    }
                }
            }
        });

        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    const newColors = getThemeColors();
                    myChart.options.scales.y.grid.color = newColors.grid;
                    myChart.options.scales.y.ticks.color = newColors.tick;
                    myChart.options.scales.x.ticks.color = newColors.tick;
                    myChart.update();
                }
            });
        });
        observer.observe(document.documentElement, { attributes: true });

        // Fetch unread communications count
        function fetchUnreadCount() {
            fetch('/sales-kpi-dashboard/communications/unread-count')
                .then(response => response.json())
                .then(data => {
                    const badge = document.getElementById('unreadBadge');
                    if (data.count > 0) {
                        badge.textContent = data.count;
                        badge.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                    }
                })
                .catch(error => console.error('Error fetching unread count:', error));
        }

        // Fetch on page load
        fetchUnreadCount();

        // Optionally fetch every 60 seconds
        setInterval(fetchUnreadCount, 60000);
    </script>
</body>
</html>
