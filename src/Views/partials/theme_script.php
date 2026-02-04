<script>
    tailwind.config = {
        darkMode: 'class',
        theme: {
            extend: {
                colors: {
                    gray: {
                        900: '#0f172a',
                        800: '#1e293b', 
                        700: '#334155',
                    }
                }
            }
        }
    }
</script>
<script>
    // Always use dark theme
    document.documentElement.classList.add('dark');
    localStorage.theme = 'dark';
</script>
