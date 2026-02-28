<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Leaderboard - Sales KPI Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <?php include __DIR__ . '/../partials/theme_script.php'; ?>
    <style>
        @keyframes slideDown {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        @keyframes shine {
            from { background-position: -200% center; }
            to { background-position: 200% center; }
        }
        .animate-slide-down { animation: slideDown 0.5s ease-out; }
        .animate-pulse-slow { animation: pulse 2s ease-in-out infinite; }
        .shine-effect {
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            background-size: 200% 100%;
            animation: shine 3s linear infinite;
        }
        .medal-gold { background: linear-gradient(135deg, #FFD700, #FFA500); }
        .medal-silver { background: linear-gradient(135deg, #C0C0C0, #808080); }
        .medal-bronze { background: linear-gradient(135deg, #CD7F32, #8B4513); }
    </style>
</head>
<body class="min-h-screen bg-slate-50 dark:bg-[#0f172a] text-slate-900 dark:text-[#e2e8f0] flex">

    <!-- Sidebar -->
    <?php include __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col md:ml-64 transition-all duration-300">
        <main class="flex-1 overflow-y-auto bg-gradient-to-br from-purple-50 via-white to-indigo-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
            <?php if (!isset($currentUpload) || !$currentUpload): ?>
            <!-- No Data State -->
            <div class="flex items-center justify-center min-h-screen">
                <div class="text-center">
                    <svg class="w-32 h-32 mx-auto text-gray-300 dark:text-gray-600 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <h2 class="text-2xl font-bold text-gray-500 dark:text-gray-400 mb-2">No Leaderboard Data Available</h2>
                    <p class="text-gray-400 dark:text-gray-500 mb-6">Please upload ranking data to display the leaderboard</p>
                    <a href="/sales-kpi-dashboard/admin/rankings/upload" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-semibold transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        Upload Rankings
                    </a>
                </div>
            </div>
            <?php else: ?>
            <!-- Leaderboard Display -->
            <?php require __DIR__ . '/../partials/leaderboard_display.php'; ?>
            <?php endif; ?>
        </main>
    </div>

</body>
</html>