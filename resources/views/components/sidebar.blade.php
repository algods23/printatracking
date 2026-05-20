<aside class="w-64 bg-black dark:bg-gray-900 border-r border-gray-800 flex flex-col">
    <!-- Logo -->
    <div class="p-5 border-b border-gray-800">
        <a href="{{ route('dashboard') }}" class="block">
            <img
                src="{{ asset('images/printa-world-logo.png') }}"
                alt="Printa World"
                class="w-full max-h-16 object-contain mx-auto"
            />
        </a>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto py-4 px-3">
        <div class="space-y-1">
            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }} flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-800 transition-colors {{ request()->routeIs('dashboard') ? 'bg-yellow-500 text-black' : '' }}">
                <i data-lucide="home" class="w-5 h-5"></i>
                <span>Dashboard</span>
            </a>

            <!-- Tasks -->
            <a href="{{ route('tasks.index') }}" class="nav-link {{ request()->routeIs('tasks.*') ? 'active' : '' }} flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-800 transition-colors {{ request()->routeIs('tasks.*') ? 'bg-yellow-500 text-black' : '' }}">
                <i data-lucide="check-square" class="w-5 h-5"></i>
                <span>Tasks</span>
            </a>

            <!-- Expenses -->
            <a href="{{ route('expenses.index') }}" class="nav-link {{ request()->routeIs('expenses.*') ? 'active' : '' }} flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-800 transition-colors {{ request()->routeIs('expenses.*') ? 'bg-yellow-500 text-black' : '' }}">
                <i data-lucide="credit-card" class="w-5 h-5"></i>
                <span>Expenses</span>
            </a>

            <!-- Reports -->
            <div x-data="{ open: {{ request()->routeIs('reports.*') ? 'true' : 'false' }} }">
                <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-800 transition-colors group">
                    <span class="flex items-center gap-3">
                        <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                        <span>Reports</span>
                    </span>
                    <i data-lucide="chevron-down" class="w-4 h-4 group-open:rotate-180 transition-transform" :class="{ 'rotate-180': open }"></i>
                </button>
                <div x-show="open" class="pl-4 space-y-1 mt-1">
                    <a href="{{ route('reports.sales') }}" class="block px-4 py-2 text-sm text-gray-400 hover:text-gray-300 hover:bg-gray-800 rounded transition-colors">Sales Report</a>
                    <a href="{{ route('reports.expenses') }}" class="block px-4 py-2 text-sm text-gray-400 hover:text-gray-300 hover:bg-gray-800 rounded transition-colors">Expense Report</a>
                    <a href="{{ route('reports.tasks') }}" class="block px-4 py-2 text-sm text-gray-400 hover:text-gray-300 hover:bg-gray-800 rounded transition-colors">Task Report</a>
                    <a href="{{ route('reports.productivity') }}" class="block px-4 py-2 text-sm text-gray-400 hover:text-gray-300 hover:bg-gray-800 rounded transition-colors">Productivity</a>
                    <a href="{{ route('reports.monthly') }}" class="block px-4 py-2 text-sm text-gray-400 hover:text-gray-300 hover:bg-gray-800 rounded transition-colors">Monthly Summary</a>
                </div>
            </div>

            <!-- Settings -->
            @if(auth()->user()->isAdmin())
            <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }} flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-800 transition-colors {{ request()->routeIs('settings.*') ? 'bg-yellow-500 text-black' : '' }}">
                <i data-lucide="settings" class="w-5 h-5"></i>
                <span>Settings</span>
            </a>
            @endif
        </div>
    </nav>

    <!-- User Info -->
    <div class="p-4 border-t border-gray-800">
        <div class="flex items-center justify-between px-4 py-3 bg-gray-800 rounded-lg">
            <div class="flex items-center gap-3 flex-1">
                <div class="w-10 h-10 bg-yellow-500 rounded-full flex items-center justify-center text-black font-bold">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-gray-400">{{ auth()->user()->role }}</p>
                </div>
            </div>
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="text-gray-400 hover:text-gray-300">
                    <i data-lucide="more-vertical" class="w-4 h-4"></i>
                </button>
                <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-gray-700 rounded-lg shadow-lg z-50">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-300 hover:bg-gray-600 rounded-lg transition-colors flex items-center gap-2">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</aside>
