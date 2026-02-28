<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ranking Management - Sales KPI Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <?php include __DIR__ . '/../../partials/theme_script.php'; ?>
</head>
<body class="min-h-screen bg-slate-50 dark:bg-[#0f172a] text-slate-900 dark:text-[#e2e8f0] flex">

    <!-- Sidebar -->
    <?php include __DIR__ . '/../../partials/admin_sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col md:ml-64 transition-all duration-300">
        <main class="flex-1 overflow-y-auto">
            <!-- Header -->
            <div class="bg-white dark:bg-slate-800 shadow-sm border-b border-gray-200 dark:border-slate-700 transition-colors duration-300">
                <div class="px-8 py-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Employee Rankings</h1>
                            <p class="text-gray-600 dark:text-gray-400 mt-1">Manage daily leaderboard data uploads</p>
                        </div>
                        <div class="flex gap-3">
                            <a href="/sales-kpi-dashboard/admin/leaderboard" class="bg-purple-600 hover:bg-purple-700 text-white px-5 py-2.5 rounded-lg font-semibold shadow-lg transition flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                View Leaderboard
                            </a>
                            <a href="/sales-kpi-dashboard/admin/rankings/upload" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg font-semibold shadow-lg transition flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                Upload Rankings
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Success/Error Messages -->
            <?php if ($successMessage): ?>
            <div class="mx-8 mt-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg animate-pulse">
                <p class="font-medium"><?= htmlspecialchars($successMessage) ?></p>
                <?php if (isset($_GET['warning'])): ?>
                <p class="text-sm mt-1"><?= htmlspecialchars($_GET['warning']) ?></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
            <div class="mx-8 mt-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg">
                <p class="font-medium">
                    <?php
                    $errorMessages = [
                        'invalid_id' => 'Invalid upload ID provided.',
                        'delete_failed' => 'Failed to delete the ranking data.'
                    ];
                    echo htmlspecialchars($errorMessages[$_GET['error']] ?? 'An error occurred.');
                    ?>
                </p>
            </div>
            <?php endif; ?>

            <!-- Rankings Table -->
            <div class="p-8">
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg overflow-hidden transition-colors duration-300">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700 bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-slate-800 dark:to-slate-700">
                        <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Ranking Uploads History</h2>
                    </div>

                    <?php if (empty($uploads)): ?>
                    <div class="p-12 text-center">
                        <svg class="w-24 h-24 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <h3 class="text-xl font-medium text-gray-500 dark:text-gray-400 mb-2">No ranking data uploaded yet</h3>
                        <p class="text-gray-400 dark:text-gray-500 mb-6">Start by uploading your first employee ranking CSV file</p>
                        <a href="/sales-kpi-dashboard/admin/rankings/upload" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-semibold transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Upload First Rankings
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-gray-200 dark:border-slate-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Upload Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Uploaded At</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Uploaded By</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Records</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
                                <?php foreach($uploads as $index => $upload): ?>
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-900/30 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <?php if ($index === 0): ?>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                Current
                                            </span>
                                            <?php endif; ?>
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                                                <?= date('F j, Y', strtotime($upload['upload_date'])) ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        <?= date('M j, Y g:i A', strtotime($upload['uploaded_at'])) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        <?= htmlspecialchars($upload['uploaded_by_name']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            <?= number_format($upload['record_count']) ?> agents
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1.5">
                                            <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                                            <span class="text-sm text-gray-600 dark:text-gray-400">Active</span>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="flex items-center gap-3">
                                            <a href="/sales-kpi-dashboard/admin/leaderboard?date=<?= $upload['upload_date'] ?>"
                                               class="text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300 font-medium hover:underline">
                                                View Leaderboard
                                            </a>
                                            <form method="POST" action="/sales-kpi-dashboard/admin/rankings/delete" class="inline"
                                                  onsubmit="return confirm('Are you sure? This will delete all ranking records for this date.');">
                                                <input type="hidden" name="upload_id" value="<?= $upload['id'] ?>">
                                                <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 font-medium hover:underline">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

</body>
</html>