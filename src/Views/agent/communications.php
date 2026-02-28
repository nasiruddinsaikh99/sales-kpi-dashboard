<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Communications - Sales KPI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <?php include __DIR__ . '/../partials/theme_script.php'; ?>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: rgba(156, 163, 175, 0.3);
            border-radius: 20px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background-color: rgba(156, 163, 175, 0.5);
        }
        .message-stream {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
        }

        /* Message content formatting display */
        .message-content p {
            margin-bottom: 0.5rem;
        }
        .message-content p:last-child {
            margin-bottom: 0;
        }
        .message-content strong {
            font-weight: 600;
            color: inherit;
        }
        .message-content em {
            font-style: italic;
        }
        .message-content s {
            text-decoration: line-through;
        }
        .message-content ol {
            list-style-type: decimal;
            margin-left: 1.5rem;
            margin-bottom: 0.5rem;
        }
        .message-content ul {
            list-style-type: disc;
            margin-left: 1.5rem;
            margin-bottom: 0.5rem;
        }
        .message-content li {
            margin-bottom: 0.25rem;
        }
    </style>
</head>
<body class="min-h-screen bg-slate-50 dark:bg-[#0B1120] text-slate-900 dark:text-white transition-colors duration-300">

    <!-- Header with Tabs -->
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
                       class="px-4 py-2 rounded-lg text-sm font-medium bg-indigo-100 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 transition">
                        Communications
                    </a>
                    <a href="/sales-kpi-dashboard/agent/leaderboard"
                       class="px-4 py-2 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                        🏆 Leaderboard
                    </a>
                </nav>
            </div>

            <div class="flex items-center gap-4">
                <a href="/sales-kpi-dashboard/logout" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition">Logout</a>
            </div>
        </div>
    </header>

    <main class="flex max-w-7xl mx-auto h-[calc(100vh-64px)]">
        <!-- Communication Stream (Left + Center) -->
        <div class="flex-1 flex flex-col">

            <!-- Page Header -->
            <div class="border-b border-gray-200 dark:border-gray-800 px-6 py-4">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Communications</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Updates and files from management</p>
            </div>

            <!-- Message Stream -->
            <div class="message-stream custom-scrollbar" id="messageStream">
                <?php if (empty($communicationsWithFiles)): ?>
                    <div class="flex flex-col items-center justify-center h-full text-gray-400 dark:text-gray-500">
                        <svg class="w-16 h-16 mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                        <p class="font-medium">No messages yet</p>
                        <p class="text-sm mt-1">Check back later for updates</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($communicationsWithFiles as $comm): ?>
                        <div class="mb-8 hover:bg-gray-50/50 dark:hover:bg-gray-800/30 -mx-4 px-4 py-3 rounded-lg transition">
                            <div class="flex gap-3">
                                <!-- Avatar -->
                                <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-semibold text-sm flex-shrink-0">
                                    <?= strtoupper(substr($comm['author_name'], 0, 1)) ?>
                                </div>

                                <!-- Message Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-baseline gap-2 mb-1">
                                        <span class="font-semibold text-gray-900 dark:text-white"><?= htmlspecialchars($comm['author_name']) ?></span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400"><?= date('M d, Y g:i A', strtotime($comm['created_at'])) ?></span>
                                    </div>

                                    <h3 class="font-semibold text-gray-900 dark:text-white mb-1"><?= htmlspecialchars($comm['title']) ?></h3>

                                    <div class="message-content text-gray-700 dark:text-gray-300 text-sm leading-relaxed">
                                        <?= $comm['message'] ?>
                                    </div>

                                    <!-- Attached Files Inline -->
                                    <?php if (!empty($comm['files'])): ?>
                                        <div class="mt-3 space-y-1.5">
                                            <?php foreach ($comm['files'] as $file): ?>
                                                <a href="/sales-kpi-dashboard/communications/download?file_id=<?= $file['id'] ?>"
                                                   class="flex items-center gap-2 p-2.5 border border-gray-200 dark:border-gray-700 rounded-lg hover:border-indigo-400 dark:hover:border-indigo-500 hover:bg-indigo-50/50 dark:hover:bg-indigo-500/5 transition group max-w-md">
                                                    <div class="w-8 h-8 rounded bg-indigo-100 dark:bg-indigo-500/20 flex items-center justify-center flex-shrink-0">
                                                        <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                                        </svg>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate"><?= htmlspecialchars($file['original_filename']) ?></p>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400"><?= number_format($file['filesize'] / 1024, 1) ?> KB</p>
                                                    </div>
                                                    <svg class="w-4 h-4 text-gray-400 group-hover:text-indigo-500 transition flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                                    </svg>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Sidebar: All Files -->
        <aside class="hidden lg:block w-72 border-l border-gray-200 dark:border-gray-800 overflow-y-auto custom-scrollbar">
            <div class="p-4 border-b border-gray-200 dark:border-gray-800">
                <h3 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                    </svg>
                    All Files
                </h3>
            </div>

            <div class="p-3">
                <?php if (empty($allFiles)): ?>
                    <div class="text-center text-gray-400 dark:text-gray-500 py-12">
                        <svg class="w-10 h-10 mx-auto mb-2 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        <p class="text-xs">No files yet</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-1.5">
                        <?php foreach ($allFiles as $file): ?>
                            <a href="/sales-kpi-dashboard/communications/download?file_id=<?= $file['id'] ?>"
                               class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition group">
                                <div class="w-7 h-7 rounded bg-indigo-100 dark:bg-indigo-500/20 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-gray-900 dark:text-white truncate"><?= htmlspecialchars($file['original_filename']) ?></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400"><?= number_format($file['filesize'] / 1024, 1) ?> KB</p>
                                </div>
                                <svg class="w-3.5 h-3.5 text-gray-400 group-hover:text-indigo-500 transition flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </aside>
    </main>

    <script>
        // Scroll to bottom on load
        const messageStream = document.getElementById('messageStream');
        if (messageStream) {
            setTimeout(() => {
                messageStream.scrollTop = messageStream.scrollHeight;
            }, 100);
        }
    </script>
</body>
</html>
