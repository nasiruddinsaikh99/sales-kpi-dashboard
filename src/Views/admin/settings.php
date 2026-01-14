<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Settings - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: #0f172a;
            color: #e2e8f0;
        }
    </style>
</head>
<body class="min-h-screen bg-[#0f172a] text-[#e2e8f0] flex">

    <!-- Sidebar -->
    <?php include __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col md:ml-64 transition-all duration-300 w-full">
        
        <!-- Mobile Header -->
        <header class="md:hidden bg-gray-900 border-b border-gray-800 p-4 flex justify-between items-center sticky top-0 z-20">
            <h1 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-400 to-emerald-400">Syntrex Admin</h1>
            <button id="openSidebar" class="text-gray-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
        </header>

        <main class="flex-grow p-4 md:p-8 max-w-4xl mx-auto w-full">
            <div class="mb-8">
                <h2 class="text-3xl font-bold">Settings</h2>
                <p class="text-gray-400 mt-1">Manage your account and application preferences.</p>
            </div>

            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'updated'): ?>
                <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 p-4 rounded-lg mb-6 flex items-center gap-3">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>Settings Updated Successfully!</span>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'error'): ?>
                <div class="bg-red-500/10 border border-red-500/20 text-red-400 p-4 rounded-lg mb-6 flex items-center gap-3">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    <span>An error occurred. Please try again.</span>
                </div>
            <?php endif; ?>

            <div class="bg-gray-800 rounded-xl shadow-lg border border-gray-700 overflow-hidden">
                <div class="p-6 border-b border-gray-700">
                    <h3 class="text-lg font-semibold text-white">Change Password</h3>
                    <p class="text-sm text-gray-400">Update the admin account password.</p>
                </div>
                
                <form action="/sales-kpi-dashboard/admin/settings/update" method="POST" class="p-6 space-y-6">
                    <div>
                        <label class="block text-sm font-medium mb-2 text-gray-300">New Password</label>
                        <input type="password" name="new_password" minlength="6"
                            class="w-full bg-gray-700 border border-gray-600 rounded-lg p-3 focus:border-indigo-500 focus:outline-none transition"
                            placeholder="Enter new password">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2 text-gray-300">Confirm Password</label>
                        <input type="password" name="confirm_password" minlength="6"
                            class="w-full bg-gray-700 border border-gray-600 rounded-lg p-3 focus:border-indigo-500 focus:outline-none transition"
                            placeholder="Confirm new password">
                    </div>

                    <div class="flex justify-end pt-4">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-2 rounded-lg font-semibold transition shadow-lg">
                            Update Password
                        </button>
                    </div>
                </form>
            </div>

            <!-- Application Preferences -->
            <div class="mt-8 bg-gray-800 rounded-xl shadow-lg border border-gray-700 overflow-hidden">
                <div class="p-6 border-b border-gray-700">
                    <h3 class="text-lg font-semibold text-white">Data Visibility</h3>
                    <p class="text-sm text-gray-400">Control how much historical data agents can access.</p>
                </div>
                
                <form action="/sales-kpi-dashboard/admin/settings/update" method="POST" class="p-6">
                    <div class="flex items-center gap-4">
                        <div class="flex-grow">
                            <label class="block text-sm font-medium mb-1 text-gray-300">History Limit (Months)</label>
                            <p class="text-xs text-gray-500 mb-2">Set to 0 for unlimited history.</p>
                            <input type="number" name="history_visibility_months" value="<?= $historyMonths ?? 3 ?>" min="0" max="60"
                                class="w-full bg-gray-700 border border-gray-600 rounded-lg p-3 focus:border-emerald-500 focus:outline-none transition">
                        </div>
                        <div class="pt-6">
                            <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white px-6 py-2.5 rounded-lg font-semibold transition shadow-lg flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Save Setting
                            </button>
                        </div>
                    </div>
                </form>
            </div>

        </main>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const openBtn = document.getElementById('openSidebar');
        const overlay = document.getElementById('mobileMenuOverlay');

        function toggleSidebar() {
            sidebar.classList.toggle('-translate-x-full');
            if (overlay) overlay.classList.toggle('hidden');
        }

        if (openBtn) openBtn.addEventListener('click', toggleSidebar);
    </script>
</body>
</html>
