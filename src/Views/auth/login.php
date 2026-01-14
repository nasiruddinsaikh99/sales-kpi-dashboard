<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sales KPI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #1e1e2f 0%, #2a2a40 100%);
            color: #fff;
            min-height: 100vh;
        }
        .glass {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1rem;
        }
        .input-glass {
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
        }
    </style>
</head>
<body class="flex items-center justify-center">

    <div class="glass p-10 w-full max-w-md shadow-2xl">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold mb-2">Welcome Back</h1>
            <p class="text-gray-400">Enter your credentials to access the portal.</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="bg-red-500/20 border border-red-500 text-red-200 p-3 rounded mb-4 text-center">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="/sales-kpi-dashboard/login">
            <div class="mb-6">
                <label for="email" class="block text-sm font-medium text-gray-300 mb-2">Email Address</label>
                <input type="email" name="email" id="email" required 
                    class="input-glass w-full px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 transition placeholder-gray-500"
                    placeholder="name@company.com">
            </div>

            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-gray-300 mb-2">Password</label>
                <input type="password" name="password" id="password" required 
                    class="input-glass w-full px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 transition placeholder-gray-500"
                    placeholder="••••••••">
            </div>

            <button type="submit" 
                class="w-full bg-gradient-to-r from-purple-500 to-indigo-600 hover:from-purple-600 hover:to-indigo-700 text-white font-bold py-3 rounded-lg transition transform hover:scale-[1.02] active:scale-95 shadow-lg">
                Sign In
            </button>
        </form>
        
        <div class="mt-6 text-center text-sm text-gray-500">
            For access issues, contact System Administrator.
        </div>
    </div>

</body>
</html>
