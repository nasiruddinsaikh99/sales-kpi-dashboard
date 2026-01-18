<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sales KPI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    <?php include __DIR__ . '/../partials/theme_script.php'; ?>
    <style>
        .glass-panel {
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen bg-slate-50 dark:bg-[#0f172a] transition-colors duration-300">

    <div class="glass-panel w-full max-w-md p-10 mx-4 rounded-xl shadow-2xl bg-white/80 dark:bg-white/5 border border-gray-200 dark:border-white/10">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold mb-2 text-gray-900 dark:text-white">Welcome Back</h1>
            <p class="text-gray-500 dark:text-gray-400">Enter your credentials to access the portal.</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-600 dark:text-red-400 p-3 rounded-lg mb-6 text-center text-sm">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="/sales-kpi-dashboard/login">
            <div class="mb-6">
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email Address</label>
                <input type="email" name="email" id="email" required 
                    class="w-full px-4 py-3 rounded-lg bg-gray-50 dark:bg-black/20 border border-gray-300 dark:border-white/10 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 transition placeholder-gray-400 dark:placeholder-gray-500"
                    placeholder="name@company.com">
            </div>

            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Password</label>
                <input type="password" name="password" id="password" required 
                    class="w-full px-4 py-3 rounded-lg bg-gray-50 dark:bg-black/20 border border-gray-300 dark:border-white/10 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 transition placeholder-gray-400 dark:placeholder-gray-500"
                    placeholder="••••••••">
            </div>

            <button type="submit" 
                class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold py-3 rounded-lg transition transform hover:scale-[1.02] active:scale-95 shadow-lg shadow-indigo-500/20">
                Sign In
            </button>
        </form>
        
        <div class="mt-6 text-center text-sm text-gray-500 dark:text-gray-400">
            For access issues, contact support@syntrex.io
        </div>
    </div>

</body>
</html>
