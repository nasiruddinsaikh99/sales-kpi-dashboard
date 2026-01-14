<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload CSV - Sales KPI</title>
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

        <main class="flex-grow p-4 md:p-8 w-full flex items-center justify-center">
            
            <div class="w-full max-w-2xl bg-gray-800 rounded-xl shadow-2xl overflow-hidden border border-gray-700">
                <div class="p-6 border-b border-gray-700 bg-gray-900 flex justify-between items-center">
                    <div>
                        <h2 class="text-xl font-bold">Upload KPI Data</h2>
                        <p class="text-gray-400 text-sm mt-1">Import monthly commission data from a CSV file.</p>
                    </div>
                    <a href="/sales-kpi-dashboard/admin/dashboard" class="text-sm text-gray-500 hover:text-gray-300">&larr; Back</a>
                </div>
                
                <form action="/sales-kpi-dashboard/admin/upload/process" method="POST" enctype="multipart/form-data" class="p-8">
                    <div class="grid md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium mb-2 text-gray-300">batch Name</label>
                            <input type="text" name="batch_name" required value="Upload <?= date('F Y') ?>"
                                class="w-full bg-gray-700 border border-gray-600 rounded-lg p-3 focus:border-indigo-500 focus:outline-none transition">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2 text-gray-300">For Month</label>
                            <input type="date" name="month" required value="<?= date('Y-m-01') ?>"
                                class="w-full bg-gray-700 border border-gray-600 rounded-lg p-3 focus:border-indigo-500 focus:outline-none text-gray-200 transition">
                        </div>
                    </div>

                    <div class="mb-8">
                        <label class="block text-sm font-medium mb-2 text-gray-300">CSV File</label>
                        <div class="flex items-center justify-center w-full">
                            <label class="flex flex-col w-full h-40 border-2 border-dashed border-gray-600 hover:border-indigo-500 rounded-xl cursor-pointer hover:bg-gray-700/50 transition group">
                                <div class="flex flex-col items-center justify-center pt-7">
                                    <svg class="w-10 h-10 text-gray-400 group-hover:text-indigo-400 transition mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                    <p class="text-sm text-gray-400 group-hover:text-gray-300 font-medium">Click to select CSV file</p>
                                    <p class="text-xs text-gray-500 mt-1">Supports standard KPI format</p>
                                </div>
                                <input type="file" name="csv_file" accept=".csv" class="opacity-0" required />
                            </label>
                        </div>
                    </div>

                    <div class="flex gap-4 pt-4 border-t border-gray-700">
                        <a href="/sales-kpi-dashboard/admin/dashboard" class="flex-1 py-3 text-center border border-gray-600 rounded-lg hover:bg-gray-700 transition font-medium text-gray-400 hover:text-white">Cancel</a>
                        <button type="submit" class="flex-1 py-3 bg-indigo-600 hover:bg-indigo-500 rounded-lg text-white font-semibold transition shadow-lg hover:shadow-indigo-500/20">Upload Data</button>
                    </div>
                </form>
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
    </script>
</body>
</html>
