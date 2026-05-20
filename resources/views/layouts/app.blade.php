<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Printa Signages') }} - @yield('title')</title>

    <!-- Tailwind CSS -->
    @vite('resources/css/app.css')

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>

    <!-- Lucide Icons -->
    <script src="https://cdn.jsdelivr.net/npm/lucide@latest"></script>
</head>
<body class="bg-white dark:bg-gray-950 text-gray-900 dark:text-gray-100 transition-colors">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        @include('components.sidebar')

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navigation -->
            @include('components.topbar')

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50 dark:bg-gray-900">
                <!-- Flash Messages -->
                @if ($message = Session::get('success'))
                    <div x-data="{ show: true }" x-show="show" class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center gap-3">
                        <i data-lucide="check-circle" class="w-5 h-5"></i>
                        <span>{{ $message }}</span>
                        <button @click="show = false" class="ml-2">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                @endif

                @if ($message = Session::get('error'))
                    <div x-data="{ show: true }" x-show="show" class="fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center gap-3">
                        <i data-lucide="alert-circle" class="w-5 h-5"></i>
                        <span>{{ $message }}</span>
                        <button @click="show = false" class="ml-2">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                @endif

                <!-- Validation Errors -->
                @if ($errors->any())
                    <div class="fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
                        <div class="font-semibold mb-2">Please fix the following errors:</div>
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="p-6">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- Scripts -->
    @vite('resources/js/app.js')
    <script>
        // Auto-close messages
        document.querySelectorAll('[x-data*="show: true"]').forEach(el => {
            setTimeout(() => {
                if (el._x_dataStack) {
                    el._x_dataStack[0].show = false;
                }
            }, 5000);
        });
    </script>

    @yield('scripts')
</body>
</html>
