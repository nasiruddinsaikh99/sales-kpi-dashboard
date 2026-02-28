<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Leaderboard - Sales KPI</title>
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
        @keyframes confetti {
            0% { transform: translateY(0) rotateZ(0deg); opacity: 1; }
            100% { transform: translateY(300px) rotateZ(720deg); opacity: 0; }
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
        .confetti {
            position: fixed;
            width: 10px;
            height: 10px;
            animation: confetti 3s ease-out forwards;
            pointer-events: none;
            z-index: 100;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-purple-50 via-white to-indigo-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 min-h-screen pb-12 text-gray-900 dark:text-gray-100 transition-colors duration-300">

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
                       class="px-4 py-2 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                        Dashboard
                    </a>
                    <a href="/sales-kpi-dashboard/agent/communications"
                       class="px-4 py-2 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition relative">
                        Communications
                    </a>
                    <a href="/sales-kpi-dashboard/agent/leaderboard"
                       class="px-4 py-2 rounded-lg text-sm font-medium bg-gradient-to-r from-purple-600 to-indigo-600 text-white transition">
                        🏆 Leaderboard
                    </a>
                </nav>
            </div>

            <div class="flex items-center gap-4">
                <a href="/sales-kpi-dashboard/logout" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition">Logout</a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 transition-colors duration-300">
        <?php if (!isset($currentUpload) || !$currentUpload): ?>
        <!-- No Data State -->
        <div class="flex items-center justify-center min-h-[60vh]">
            <div class="text-center">
                <svg class="w-32 h-32 mx-auto text-gray-300 dark:text-gray-600 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <h2 class="text-2xl font-bold text-gray-500 dark:text-gray-400 mb-2">No Leaderboard Data Available</h2>
                <p class="text-gray-400 dark:text-gray-500">Check back later for the latest rankings!</p>
            </div>
        </div>
        <?php else: ?>
        <!-- Leaderboard Display -->
        <?php require __DIR__ . '/../partials/leaderboard_display.php'; ?>
        <?php endif; ?>
    </main>

    <script>
        // Create confetti effect for top performers
        function createConfetti() {
            const colors = ['#FF6B6B', '#4ECDC4', '#45B7D1', '#FFA07A', '#98D8C8', '#FFD93D'];
            const confettiCount = 50;

            for (let i = 0; i < confettiCount; i++) {
                setTimeout(() => {
                    const confetti = document.createElement('div');
                    confetti.className = 'confetti';
                    confetti.style.left = Math.random() * 100 + '%';
                    confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                    confetti.style.transform = `rotate(${Math.random() * 360}deg)`;
                    document.body.appendChild(confetti);

                    setTimeout(() => confetti.remove(), 3000);
                }, i * 30);
            }
        }

        // Check if user is in top 3 and trigger confetti
        <?php if (isset($currentUserRank) && $currentUserRank <= 3): ?>
        setTimeout(createConfetti, 500);
        <?php endif; ?>
    </script>

</body>
</html>