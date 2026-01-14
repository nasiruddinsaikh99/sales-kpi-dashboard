<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php include __DIR__ . '/../partials/theme_script.php'; ?>
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: rgba(156, 163, 175, 0.5);
            border-radius: 20px;
        }
    </style>
</head>
<body class="min-h-screen bg-slate-50 dark:bg-[#0f172a] text-slate-900 dark:text-[#e2e8f0] flex">

    <!-- Sidebar -->
    <?php include __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col md:ml-64 transition-all duration-300">
        
        <!-- Mobile Header -->
        <header class="md:hidden bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 p-4 flex justify-between items-center sticky top-0 z-20">
            <h1 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-emerald-500">Syntrex Admin</h1>
            <button id="openSidebar" class="text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
        </header>

        <main class="flex-grow p-4 md:p-8 max-w-7xl w-full mx-auto">
            <!-- Global Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-lg">
                    <p class="text-gray-500 dark:text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Total Net GP</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">$<?= number_format($globalStats['total_net_gp'] ?? 0, 2) ?></p>
                    <p class="text-blue-500 dark:text-blue-400 text-xs mt-1">Lifetime Earnings</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-lg">
                    <p class="text-gray-500 dark:text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Total Gross Profit</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">$<?= number_format($globalStats['total_gross_profit'] ?? 0, 2) ?></p>
                    <p class="text-emerald-500 dark:text-emerald-400 text-xs mt-1">Total Revenue</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-lg">
                    <p class="text-gray-500 dark:text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Total Payouts</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">$<?= number_format($globalStats['total_payout'] ?? 0, 2) ?></p>
                    <p class="text-amber-500 dark:text-amber-400 text-xs mt-1">Commission Paid</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-lg">
                    <p class="text-gray-500 dark:text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Total Records</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white"><?= number_format($globalStats['total_records'] ?? 0) ?></p>
                    <p class="text-gray-500 text-xs mt-1">Individual Rows</p>
                </div>
            </div>

            <!-- Analytics Chart -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-lg mb-8">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Performance Trajectory</h3>
                <div class="h-[300px] w-full">
                    <canvas id="adminGlobalChart"></canvas>
                </div>
            </div>

            <div class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Import History</h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">Manage monthly data uploads and records.</p>
                </div>
                <div class="flex gap-3">
                    <!-- Placeholder for Export Button -->
                    <button class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-sm shadow-lg flex items-center gap-2 transition opacity-50 cursor-not-allowed" disabled title="Coming Soon">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Export All
                    </button>
                    <a href="/sales-kpi-dashboard/admin/upload" class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-lg font-semibold shadow-lg transition flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        New Upload
                    </a>
                </div>
            </div>

            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'Import Success'): ?>
                <div class="bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 text-emerald-600 dark:text-emerald-400 p-4 rounded-lg mb-6 flex items-center gap-3">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>CSV Data Imported Successfully!</span>
                </div>
            <?php endif; ?>

            <div class="bg-white dark:bg-gray-800 rounded-xl overflow-hidden shadow-xl border border-gray-200 dark:border-gray-700">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-500 dark:text-gray-400 uppercase text-xs font-semibold tracking-wider">
                            <tr>
                                <th class="px-6 py-4">Batch Name</th>
                                <th class="px-6 py-4">For Month</th>
                                <th class="px-6 py-4">Date Uploaded</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <?php if (empty($uploads)): ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <svg class="w-10 h-10 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            <p>No uploads found.</p>
                                            <p class="text-sm">Start by uploading your first CSV file.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($uploads as $up): ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($up['batch_name']) ?></td>
                                    <td class="px-6 py-4 text-indigo-600 dark:text-indigo-300"><?= htmlspecialchars($up['for_month']) ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400"><?= htmlspecialchars($up['uploaded_at']) ?></td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">Active</span>
                                    </td>
                                    <td class="px-6 py-4 text-right flex items-center justify-end gap-3">
                                        <a href="/sales-kpi-dashboard/admin/records?upload_id=<?= $up['id'] ?>" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-sm font-medium hover:underline">View Records</a>
                                        <form action="/sales-kpi-dashboard/admin/upload/delete" method="POST" onsubmit="return confirm('Are you sure? This will delete all KPI records associated with this batch.');" class="inline">
                                            <input type="hidden" name="upload_id" value="<?= $up['id'] ?>">
                                            <button type="submit" class="text-red-500 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 text-sm font-medium hover:underline">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const openBtn = document.getElementById('openSidebar');
        const closeBtn = document.getElementById('closeSidebar');
        const overlay = document.getElementById('mobileMenuOverlay');

        function toggleSidebar() {
            sidebar.classList.toggle('-translate-x-full');
            if (overlay) overlay.classList.toggle('hidden');
        }

        if (openBtn) openBtn.addEventListener('click', toggleSidebar);
        if (closeBtn) closeBtn.addEventListener('click', toggleSidebar);
        if (overlay) overlay.addEventListener('click', toggleSidebar);

        // Chart Initialization
        const ctx = document.getElementById('adminGlobalChart').getContext('2d');
        
        // Dynamic Chart Colors based on theme
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
                    data: <?= json_encode($chartDataNetGP) ?>,
                    borderColor: '#3b82f6',
                    backgroundColor: gradient,
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#1d4ed8'
                },
                {
                    label: 'Gross Profit',
                    data: <?= json_encode($chartDataGrossProfit) ?>,
                    borderColor: '#10b981',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    fill: false,
                    tension: 0.4,
                    pointBackgroundColor: '#059669'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: true, labels: { color: colors.tick } } },
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

        // Update chart on theme switch
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    const newColors = getThemeColors();
                    myChart.options.scales.y.grid.color = newColors.grid;
                    myChart.options.scales.y.ticks.color = newColors.tick;
                    myChart.options.scales.x.ticks.color = newColors.tick;
                    myChart.options.plugins.legend.labels.color = newColors.tick;
                    myChart.update();
                }
            });
        });
        
        observer.observe(document.documentElement, { attributes: true });
    </script>
</body>
</html>
