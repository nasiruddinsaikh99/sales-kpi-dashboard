<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Rankings - Sales KPI Admin</title>
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
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Upload Employee Rankings</h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">Import daily performance ranking data from CSV</p>
                </div>
            </div>

            <!-- Error Messages -->
            <?php if (isset($_GET['error'])): ?>
            <div class="mx-8 mt-6 bg-red-100 dark:bg-red-900/20 border-l-4 border-red-500 text-red-700 dark:text-red-400 p-4 rounded-lg">
                <p class="font-medium">
                    <?php
                    $errorMessages = [
                        'no_file' => 'Please select a CSV file to upload.',
                        'invalid_date' => 'Invalid date format provided.',
                        'duplicate_date' => 'Ranking data for this date already exists. Please delete the existing data first.',
                        'file_read' => 'Failed to read the uploaded file.',
                        'invalid_csv' => 'Invalid CSV format. Please check your file.',
                        'missing_columns' => 'Required columns (Employee Name, Overall Rank) are missing from the CSV.'
                    ];
                    echo htmlspecialchars($errorMessages[$_GET['error']] ?? 'An error occurred during upload.');
                    ?>
                </p>
            </div>
            <?php endif; ?>

            <!-- Upload Form -->
            <div class="p-8">
                <div class="max-w-3xl mx-auto">
                    <form action="/sales-kpi-dashboard/admin/rankings/process" method="POST" enctype="multipart/form-data"
                          class="bg-white dark:bg-slate-800 rounded-xl shadow-lg p-8 transition-colors duration-300">

                        <!-- Instructions -->
                        <div class="mb-8 p-6 bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20 rounded-lg border border-purple-200 dark:border-purple-800">
                            <h2 class="text-lg font-semibold text-purple-900 dark:text-purple-100 mb-3">CSV Format Requirements</h2>
                            <ul class="space-y-2 text-sm text-purple-800 dark:text-purple-200">
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span><strong>Required columns:</strong> Individual Employee, Overall Rank, Total Attainment %</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span><strong>Optional KPI columns:</strong> HPU, VHI Conv, Upg Conv, CSGA, VMP Take, Perks, Traffic GP/Cust Attainment %</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>First row must contain column headers</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>Employee names will be automatically matched to existing users</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Date Selection -->
                        <div class="mb-6">
                            <label for="upload_date" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Ranking Date
                            </label>
                            <input type="date"
                                   id="upload_date"
                                   name="upload_date"
                                   value="<?= date('Y-m-d') ?>"
                                   max="<?= date('Y-m-d') ?>"
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent bg-white dark:bg-slate-700 text-gray-900 dark:text-white transition">
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Select the date these rankings represent (typically today)</p>
                        </div>

                        <!-- File Upload -->
                        <div class="mb-8">
                            <label for="csv_file" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                CSV File
                            </label>
                            <div class="relative">
                                <input type="file"
                                       id="csv_file"
                                       name="csv_file"
                                       accept=".csv"
                                       required
                                       class="hidden">
                                <label for="csv_file"
                                       id="dropzone"
                                       class="flex items-center justify-center w-full px-4 py-8 border-2 border-dashed border-gray-300 dark:border-slate-600 rounded-lg cursor-pointer hover:border-purple-500 dark:hover:border-purple-400 transition bg-gray-50 dark:bg-slate-900/50 hover:bg-purple-50 dark:hover:bg-purple-900/10">
                                    <div class="text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                        </svg>
                                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                            <span class="font-semibold text-purple-600 dark:text-purple-400">Click to upload</span> or drag and drop
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">CSV files only</p>
                                        <p id="file-name" class="mt-3 text-sm font-medium text-green-600 dark:text-green-400 hidden"></p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Sample Data Preview -->
                        <div class="mb-8 p-4 bg-gray-50 dark:bg-slate-900/50 rounded-lg">
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Expected Format Example:</h3>
                            <div class="overflow-x-auto">
                                <table class="text-xs">
                                    <thead>
                                        <tr class="text-left text-gray-600 dark:text-gray-400">
                                            <th class="pr-4">Individual Employee</th>
                                            <th class="pr-4">Overall Rank</th>
                                            <th class="pr-4">Total Attainment %</th>
                                            <th class="pr-4">HPU Attainment %</th>
                                            <th>...</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-gray-700 dark:text-gray-300">
                                        <tr>
                                            <td class="pr-4">Emily Franell</td>
                                            <td class="pr-4">1</td>
                                            <td class="pr-4">120.92%</td>
                                            <td class="pr-4">111.10%</td>
                                            <td>...</td>
                                        </tr>
                                        <tr>
                                            <td class="pr-4">Kareem Jackson</td>
                                            <td class="pr-4">2</td>
                                            <td class="pr-4">118.02%</td>
                                            <td class="pr-4">125.00%</td>
                                            <td>...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex justify-end gap-4 pt-6 border-t border-gray-200 dark:border-slate-700">
                            <a href="/sales-kpi-dashboard/admin/rankings"
                               class="px-6 py-2.5 rounded-lg border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 font-semibold transition">
                                Cancel
                            </a>
                            <button type="submit"
                                    class="px-6 py-2.5 rounded-lg bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-semibold shadow-lg transition flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                Upload Rankings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        const fileInput = document.getElementById('csv_file');
        const dropzone = document.getElementById('dropzone');
        const fileNameElement = document.getElementById('file-name');

        function updateFileName(file) {
            if (file) {
                fileNameElement.textContent = 'Selected: ' + file.name;
                fileNameElement.classList.remove('hidden');
                dropzone.classList.add('border-green-500', 'dark:border-green-400');
            } else {
                fileNameElement.classList.add('hidden');
                dropzone.classList.remove('border-green-500', 'dark:border-green-400');
            }
        }

        // File input change
        fileInput.addEventListener('change', function(e) {
            updateFileName(this.files[0]);
        });

        // Drag and drop functionality
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            dropzone.classList.add('border-purple-500', 'bg-purple-50', 'dark:bg-purple-900/20');
        }

        function unhighlight(e) {
            dropzone.classList.remove('border-purple-500', 'bg-purple-50', 'dark:bg-purple-900/20');
        }

        dropzone.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;

            if (files.length > 0 && files[0].type === 'text/csv') {
                fileInput.files = files;
                updateFileName(files[0]);
            } else {
                alert('Please drop a CSV file');
            }
        }
    </script>

</body>
</html>