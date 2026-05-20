<header class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 px-6 py-4">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">@yield('page-title', 'Dashboard')</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">@yield('page-subtitle', 'Welcome to Printa Signages Management System')</p>
        </div>

        <div class="flex items-center gap-4">
            <!-- Search -->
            <div class="hidden sm:block relative">
                <input type="text" placeholder="Search..." class="bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 rounded-lg pl-4 pr-4 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                <i data-lucide="search" class="absolute right-3 top-2.5 w-5 h-5 text-gray-400"></i>
            </div>

            <!-- Dark Mode Toggle -->
            <button onclick="toggleDarkMode()" class="p-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                <i data-lucide="moon" class="w-5 h-5 dark:hidden"></i>
                <i data-lucide="sun" class="w-5 h-5 hidden dark:block"></i>
            </button>

            <!-- Notifications -->
            <button class="relative p-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                <i data-lucide="bell" class="w-5 h-5"></i>
                <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
            </button>

            <!-- Quick Actions -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="p-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                    <i data-lucide="plus-circle" class="w-5 h-5"></i>
                </button>
                <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-xl z-50 border border-gray-200 dark:border-gray-700">
                    <a href="{{ route('tasks.create') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 first:rounded-t-lg transition-colors flex items-center gap-2">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        New Task
                    </a>
                    <a href="{{ route('receipts.create') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors flex items-center gap-2">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        New Receipt
                    </a>
                    <a href="{{ route('expenses.create') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 last:rounded-b-lg transition-colors flex items-center gap-2">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        New Expense
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
function toggleDarkMode() {
    const html = document.documentElement;
    const isDark = html.classList.contains('dark');
    
    if (isDark) {
        html.classList.remove('dark');
        localStorage.setItem('darkMode', 'false');
    } else {
        html.classList.add('dark');
        localStorage.setItem('darkMode', 'true');
    }
    
    lucide.createIcons();
}
</script>
