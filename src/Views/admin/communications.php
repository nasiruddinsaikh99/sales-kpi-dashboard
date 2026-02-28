<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Communications - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Quill WYSIWYG Editor -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <?php include __DIR__ . '/../partials/theme_script.php'; ?>
    <style>
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
        /* Quill Editor Styling - Blended toolbar and editor */
        #editor-container {
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            background: white;
        }
        .dark #editor-container {
            border-color: #374151;
            background: #1f2937;
        }
        .ql-toolbar {
            border: none !important;
            border-bottom: 1px solid #e5e7eb !important;
            background: transparent !important;
            padding: 8px !important;
        }
        .dark .ql-toolbar {
            border-bottom-color: #374151 !important;
        }
        .ql-container {
            font-size: 14px;
            border: none !important;
            background: transparent !important;
        }
        .ql-editor {
            min-height: 100px;
            max-height: 180px;
            overflow-y: auto;
            padding: 12px;
            color: #111827;
        }
        .dark .ql-editor {
            color: #f3f4f6;
        }
        .ql-editor.ql-blank::before {
            color: #9ca3af;
            font-style: normal;
            left: 12px;
        }
        .ql-snow .ql-stroke {
            stroke: #6b7280;
        }
        .dark .ql-snow .ql-stroke {
            stroke: #9ca3af;
        }
        .ql-snow .ql-fill {
            fill: #6b7280;
        }
        .dark .ql-snow .ql-fill {
            fill: #9ca3af;
        }
        .ql-snow .ql-picker-label {
            color: #6b7280;
        }
        .dark .ql-snow .ql-picker-label {
            color: #9ca3af;
        }
        .ql-toolbar button:hover,
        .ql-toolbar button:focus,
        .ql-toolbar button.ql-active {
            color: #4f46e5 !important;
        }
        .ql-toolbar button:hover .ql-stroke,
        .ql-toolbar button.ql-active .ql-stroke {
            stroke: #4f46e5 !important;
        }
        .ql-toolbar button:hover .ql-fill,
        .ql-toolbar button.ql-active .ql-fill {
            fill: #4f46e5 !important;
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

        .file-preview {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.75rem;
            border: 1px solid;
            border-radius: 0.5rem;
            margin-top: 0.5rem;
            margin-right: 0.5rem;
            transition: all 0.15s;
        }
    </style>
</head>
<body class="min-h-screen bg-slate-50 dark:bg-[#0f172a] text-slate-900 dark:text-[#e2e8f0] flex">

    <!-- Sidebar -->
    <?php include __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-1 flex md:ml-64 transition-all duration-300">

        <!-- Communication Stream (Left + Center) -->
        <div class="flex-1 flex flex-col h-screen">

            <!-- Header -->
            <header class="border-b border-gray-200 dark:border-gray-800 px-6 py-4">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Communications</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Share updates and files with all agents</p>
            </header>

            <?php if (isset($_GET['success']) && $_GET['success'] == 'created'): ?>
                <div class="mx-6 mt-4 bg-emerald-50 dark:bg-emerald-500/10 border-l-4 border-emerald-500 text-emerald-700 dark:text-emerald-400 p-3 text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>Message sent successfully</span>
                </div>
            <?php endif; ?>

            <!-- Message Stream -->
            <div class="message-stream custom-scrollbar" id="messageStream">
                <?php if (empty($communicationsWithFiles)): ?>
                    <div class="flex flex-col items-center justify-center h-full text-gray-400 dark:text-gray-500">
                        <svg class="w-16 h-16 mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                        <p class="font-medium">No messages yet</p>
                        <p class="text-sm mt-1">Start the conversation below</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($communicationsWithFiles as $comm): ?>
                        <div class="mb-8 hover:bg-gray-50/50 dark:hover:bg-gray-800/30 -mx-4 px-4 py-3 rounded-lg transition group">
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
                                                   class="flex items-center gap-2 p-2.5 border border-gray-200 dark:border-gray-700 rounded-lg hover:border-indigo-400 dark:hover:border-indigo-500 hover:bg-indigo-50/50 dark:hover:bg-indigo-500/5 transition group/file max-w-md">
                                                    <div class="w-8 h-8 rounded bg-indigo-100 dark:bg-indigo-500/20 flex items-center justify-center flex-shrink-0">
                                                        <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                                        </svg>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate"><?= htmlspecialchars($file['original_filename']) ?></p>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400"><?= number_format($file['filesize'] / 1024, 1) ?> KB</p>
                                                    </div>
                                                    <svg class="w-4 h-4 text-gray-400 group-hover/file:text-indigo-500 transition flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                                    </svg>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Delete Button -->
                                <form method="POST" action="/sales-kpi-dashboard/admin/communications/delete" onsubmit="return confirm('Delete this message?');" class="opacity-0 group-hover:opacity-100 transition">
                                    <input type="hidden" name="communication_id" value="<?= $comm['id'] ?>">
                                    <button type="submit" class="text-gray-400 hover:text-red-500 dark:hover:text-red-400 p-1.5 rounded hover:bg-red-50 dark:hover:bg-red-500/10 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Compose Panel - Entire area is droppable -->
            <div class="border-t border-gray-200 dark:border-gray-800 p-4 relative" id="composePanel">
                <!-- Drag Overlay for entire compose area -->
                <div id="dragOverlay" class="hidden absolute inset-0 bg-indigo-50 dark:bg-indigo-500/20 border-2 border-dashed border-indigo-400 dark:border-indigo-500 rounded-lg flex items-center justify-center z-50 pointer-events-none">
                    <div class="text-center">
                        <svg class="w-16 h-16 mx-auto mb-3 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        <p class="text-indigo-600 dark:text-indigo-400 font-semibold text-lg">Drop files here</p>
                        <p class="text-indigo-500 dark:text-indigo-300 text-sm mt-1">to attach them to your message</p>
                    </div>
                </div>

                <form method="POST" action="/sales-kpi-dashboard/admin/communications/store" enctype="multipart/form-data" id="composeForm">
                    <div class="mb-2">
                        <input type="text" name="title" id="titleInput" placeholder="Message title"
                               class="w-full px-3 py-2 text-sm border-0 border-b border-gray-200 dark:border-gray-700 bg-transparent focus:outline-none focus:border-indigo-500 text-gray-900 dark:text-white placeholder-gray-400">
                    </div>

                    <!-- Quill Editor -->
                    <div id="editor-container">
                        <div id="editor"></div>
                    </div>
                    <input type="hidden" name="message" id="messageInput">

                    <!-- File Preview Area (Bigger and with remove buttons) -->
                    <div id="filePreviewArea" class="mt-3 hidden min-h-[60px] p-3 bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-lg">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Attached Files</span>
                            <button type="button" onclick="clearAllFiles()" class="text-xs text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">Clear all</button>
                        </div>
                        <div id="fileList" class="space-y-2"></div>
                    </div>

                    <div class="mt-3 flex items-center justify-between gap-3">
                        <label class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white cursor-pointer rounded transition border border-gray-300 dark:border-gray-600 hover:border-indigo-500 dark:hover:border-indigo-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                            </svg>
                            <span>Add files</span>
                            <input type="file" multiple id="fileInput" class="hidden" onchange="handleFileSelect(event)">
                        </label>
                        <button type="submit" class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium rounded-lg transition flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                            Send
                        </button>
                    </div>
                </form>
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
    </div>

    <script>
        // Sidebar toggle
        const sidebar = document.getElementById('sidebar');
        const openBtn = document.getElementById('openSidebar');
        const closeBtn = document.getElementById('closeSidebar');
        const overlay = document.getElementById('mobileMenuOverlay');

        function toggleSidebar() {
            if (sidebar) sidebar.classList.toggle('-translate-x-full');
            if (overlay) overlay.classList.toggle('hidden');
        }

        if (openBtn) openBtn.addEventListener('click', toggleSidebar);
        if (closeBtn) closeBtn.addEventListener('click', toggleSidebar);
        if (overlay) overlay.addEventListener('click', toggleSidebar);

        // Scroll to bottom on load
        const messageStream = document.getElementById('messageStream');
        if (messageStream) {
            setTimeout(() => {
                messageStream.scrollTop = messageStream.scrollHeight;
            }, 100);
        }

        // Initialize Quill WYSIWYG Editor
        var quill = new Quill('#editor', {
            theme: 'snow',
            placeholder: 'Write your message...',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'strike'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['clean']
                ]
            }
        });

        // File management with proper FormData
        let selectedFiles = [];
        let fileInputElement = document.getElementById('fileInput');

        function handleFileSelect(event) {
            const files = Array.from(event.target.files);
            addFiles(files);
            event.target.value = ''; // Reset input so same file can be added again
        }

        function addFiles(files) {
            files.forEach(file => {
                // Check if file already exists
                const exists = selectedFiles.some(f => f.name === file.name && f.size === file.size);
                if (!exists) {
                    selectedFiles.push(file);
                }
            });
            updateFilePreview();
        }

        function removeFile(index) {
            selectedFiles.splice(index, 1);
            updateFilePreview();
        }

        function clearAllFiles() {
            selectedFiles = [];
            updateFilePreview();
        }

        function updateFilePreview() {
            const previewArea = document.getElementById('filePreviewArea');
            const fileList = document.getElementById('fileList');

            if (selectedFiles.length > 0) {
                previewArea.classList.remove('hidden');
                fileList.innerHTML = '';

                selectedFiles.forEach((file, index) => {
                    const fileDiv = document.createElement('div');
                    fileDiv.className = 'flex items-center gap-2 p-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg group hover:border-indigo-400 dark:hover:border-indigo-500 transition';
                    fileDiv.innerHTML = `
                        <svg class="w-4 h-4 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-medium text-gray-900 dark:text-white truncate">${file.name}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">${(file.size / 1024).toFixed(1)} KB</p>
                        </div>
                        <button type="button" onclick="removeFile(${index})" class="p-1 text-gray-400 hover:text-red-500 dark:hover:text-red-400 rounded hover:bg-red-50 dark:hover:bg-red-500/10 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    `;
                    fileList.appendChild(fileDiv);
                });
            } else {
                previewArea.classList.add('hidden');
            }
        }

        // Drag and drop functionality - entire compose panel
        const composePanel = document.getElementById('composePanel');
        const dragOverlay = document.getElementById('dragOverlay');
        let dragCounter = 0; // Fix for blinking

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            composePanel.addEventListener(eventName, preventDefaults, false);
            document.body.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        composePanel.addEventListener('dragenter', (e) => {
            dragCounter++;
            if (e.dataTransfer.types.includes('Files')) {
                dragOverlay.classList.remove('hidden');
            }
        }, false);

        composePanel.addEventListener('dragleave', (e) => {
            dragCounter--;
            if (dragCounter === 0) {
                dragOverlay.classList.add('hidden');
            }
        }, false);

        composePanel.addEventListener('drop', (e) => {
            dragCounter = 0;
            dragOverlay.classList.add('hidden');
            const dt = e.dataTransfer;
            const files = Array.from(dt.files);
            if (files.length > 0) {
                addFiles(files);
            }
        }, false);

        composePanel.addEventListener('dragover', (e) => {
            if (e.dataTransfer.types.includes('Files')) {
                dragOverlay.classList.remove('hidden');
            }
        }, false);

        // Form submission with proper file handling
        document.getElementById('composeForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const titleInput = document.getElementById('titleInput');

            // Validate title is required
            if (!titleInput.value.trim()) {
                alert('Please enter a message title');
                titleInput.focus();
                return;
            }

            const formData = new FormData();

            // Get HTML content from Quill
            const messageHtml = quill.root.innerHTML;

            // Add title and message
            formData.append('title', titleInput.value);
            formData.append('message', messageHtml);

            // Add all selected files
            selectedFiles.forEach((file, index) => {
                formData.append('files[]', file);
            });

            // Submit via fetch
            fetch('/sales-kpi-dashboard/admin/communications/store', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (response.ok) {
                    window.location.href = '/sales-kpi-dashboard/admin/communications?success=created';
                } else {
                    alert('Error sending message. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error sending message. Please try again.');
            });
        });
    </script>
</body>
</html>
