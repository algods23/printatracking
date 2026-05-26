@extends('layouts.app')

@section('title', 'Dashboard Locked')
@section('page-title', 'Dashboard Locked')
@section('page-subtitle', 'Enter your account password to open the dashboard')

@section('content')
<div class="max-w-md mx-auto mt-10">
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-10 h-10 rounded-lg bg-yellow-500/10 flex items-center justify-center">
                <i data-lucide="lock" class="w-5 h-5 text-yellow-500"></i>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Unlock Dashboard</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Security check required every time you open dashboard.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('dashboard.unlock') }}" class="space-y-4">
            @csrf
            <div>
                <label for="dashboard_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password</label>
                <input
                    id="dashboard_password"
                    name="password"
                    type="password"
                    required
                    autofocus
                    class="w-full px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 text-gray-900 dark:text-white"
                    placeholder="Enter your account password"
                >
            </div>

            <div class="flex items-center justify-between gap-3">
                <button type="submit" class="px-5 py-2.5 bg-yellow-500 hover:bg-yellow-600 text-black font-semibold rounded-lg transition-colors">
                    Unlock
                </button>

                <a href="{{ route('tasks.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                    Go to Transactions
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    lucide.createIcons();
</script>
@endsection
