<aside class="sidebar-expanded bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800 flex flex-col fixed h-full transition-all duration-300 transform -translate-x-full md:translate-x-0 z-30" id="sidebar">
    <!-- Header with Collapse Button -->
    <div class="p-4 flex items-center justify-between border-b border-gray-200 dark:border-gray-800">
        <h1 class="sidebar-text text-lg font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-emerald-500 dark:from-blue-400 dark:to-emerald-400 whitespace-nowrap overflow-hidden">
            Admin
        </h1>
        <!-- Collapse/Expand Button (Desktop) -->
        <button id="toggleSidebarCollapse" class="hidden md:flex items-center justify-center w-8 h-8 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500 dark:text-gray-400 transition" title="Toggle Sidebar">
            <svg class="collapse-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path></svg>
            <svg class="expand-icon w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
        </button>
        <!-- Close Button (Mobile) -->
        <button id="closeSidebar" class="md:hidden text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>

    <nav class="flex-1 px-2 py-4 space-y-1 overflow-y-auto">
        <a href="/sales-kpi-dashboard/admin/dashboard" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg transition <?= strpos($_SERVER['REQUEST_URI'], '/admin/dashboard') !== false ? 'bg-indigo-50 dark:bg-gray-800 text-indigo-600 dark:text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800' ?>" title="Dashboard">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
            <span class="sidebar-text whitespace-nowrap">Dashboard</span>
        </a>
        
        <a href="/sales-kpi-dashboard/admin/upload" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg transition <?= strpos($_SERVER['REQUEST_URI'], '/admin/upload') !== false ? 'bg-indigo-50 dark:bg-gray-800 text-indigo-600 dark:text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800' ?>" title="Upload Data">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
            <span class="sidebar-text whitespace-nowrap">Upload Data</span>
        </a>

        <a href="/sales-kpi-dashboard/admin/records" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg transition <?= strpos($_SERVER['REQUEST_URI'], '/admin/records') !== false ? 'bg-indigo-50 dark:bg-gray-800 text-indigo-600 dark:text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800' ?>" title="Records">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            <span class="sidebar-text whitespace-nowrap">Records</span>
        </a>

        <a href="/sales-kpi-dashboard/admin/settings" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg transition <?= strpos($_SERVER['REQUEST_URI'], '/admin/settings') !== false ? 'bg-indigo-50 dark:bg-gray-800 text-indigo-600 dark:text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800' ?>" title="Settings">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            <span class="sidebar-text whitespace-nowrap">Settings</span>
        </a>
    </nav>

    <!-- Support Contact (hidden when collapsed) -->
    <div class="sidebar-text px-3 py-2 mx-2 mb-2 bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/20 rounded-lg">
        <div class="flex items-start gap-2">
            <svg class="w-4 h-4 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div>
                <p class="text-xs font-semibold text-blue-700 dark:text-blue-400">Need Help?</p>
                <p class="text-xs text-blue-600 dark:text-blue-400 mt-0.5 leading-tight">Contact <a href="mailto:support@syntrex.io" class="hover:text-blue-800 dark:hover:text-blue-300">support@syntrex.io</a></p>
            </div>
        </div>
    </div>



    <!-- Logout -->
    <div class="p-2 border-t border-gray-200 dark:border-gray-800">
        <a href="/sales-kpi-dashboard/logout" class="flex items-center gap-3 px-3 py-2.5 text-red-500 hover:bg-red-50 dark:hover:bg-gray-800/50 rounded-lg transition" title="Logout">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            <span class="sidebar-text whitespace-nowrap">Logout</span>
        </a>
    </div>
</aside>

<!-- Mobile Menu Button Overlay -->
<div id="mobileMenuOverlay" class="fixed inset-0 bg-black/50 z-20 hidden md:hidden"></div>

<style>
    /* Sidebar States */
    .sidebar-expanded { width: 16rem; } /* 256px */
    .sidebar-collapsed { width: 4.5rem; } /* 72px - just enough for icons */
    
    /* Hide text elements when collapsed */
    .sidebar-collapsed .sidebar-text { display: none; }
    .sidebar-collapsed .collapse-icon { display: none; }
    .sidebar-collapsed .expand-icon { display: block !important; }
    
    /* Center icons when collapsed */
    .sidebar-collapsed .nav-item { justify-content: center; padding-left: 0; padding-right: 0; }
    .sidebar-collapsed .nav-item svg { margin: 0; }
    
    /* Adjust main content margin based on sidebar state */
    .main-content-expanded { margin-left: 16rem; }
    .main-content-collapsed { margin-left: 4.5rem; }
    
    /* Hide transitions initially to prevent bounce */
    .no-transition, .no-transition * { transition: none !important; }
</style>

<!-- CRITICAL: Apply collapsed state IMMEDIATELY before page renders to prevent bounce -->
<script>
(function() {
    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (isCollapsed) {
        // Write CSS immediately to apply collapsed state before DOM renders
        document.write('<style id="instant-sidebar-style">#sidebar{width:4.5rem !important;}.sidebar-text{display:none !important;}.nav-item{justify-content:center !important;padding-left:0 !important;padding-right:0 !important;}.collapse-icon{display:none !important;}.expand-icon{display:block !important;}.md\\:ml-64{margin-left:4.5rem !important;}</style>');
    }
})();
</script>

<script>
    // Sidebar collapse/expand functionality
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('toggleSidebarCollapse');
        const mainContents = document.querySelectorAll('.md\\:ml-64, [class*="md:ml-"]');
        
        // Remove the instant style now that DOM is ready
        const instantStyle = document.getElementById('instant-sidebar-style');
        if (instantStyle) instantStyle.remove();
        
        // Apply proper classes based on saved state
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (isCollapsed) {
            sidebar.classList.remove('sidebar-expanded');
            sidebar.classList.add('sidebar-collapsed');
            mainContents.forEach(el => {
                el.classList.remove('md:ml-64');
                el.classList.add('md:ml-[4.5rem]');
            });
        }
        
        // Now enable transitions (small delay to ensure no flash)
        setTimeout(() => {
            sidebar.classList.remove('no-transition');
        }, 50);
        
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                if (sidebar.classList.contains('sidebar-collapsed')) {
                    expandSidebar();
                } else {
                    collapseSidebar();
                }
            });
        }
        
        function collapseSidebar() {
            sidebar.classList.remove('sidebar-expanded');
            sidebar.classList.add('sidebar-collapsed');
            mainContents.forEach(el => {
                el.classList.remove('md:ml-64');
                el.classList.add('md:ml-[4.5rem]');
            });
            localStorage.setItem('sidebarCollapsed', 'true');
        }
        
        function expandSidebar() {
            sidebar.classList.remove('sidebar-collapsed');
            sidebar.classList.add('sidebar-expanded');
            mainContents.forEach(el => {
                el.classList.remove('md:ml-[4.5rem]');
                el.classList.add('md:ml-64');
            });
            localStorage.setItem('sidebarCollapsed', 'false');
        }
    });
</script>

