@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Welcome back! Here\'s your business overview.')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
   

    <!-- Pending Tasks Card -->
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-white-600 dark:text-white-400 font-semibold">Pending</h3>
            <div class="w-10 h-10 bg-orange-500/10 rounded-lg flex items-center justify-center">
                <i data-lucide="alert-circle" class="w-6 h-6 text-orange-500"></i>
            </div>
        </div>
        <p class="text-3xl font-bold text-white-900 dark:text-white">{{ $pendingTasks }}</p>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Awaiting processing</p>
    </div>


    <!-- Completed Tasks Card -->
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-gray-600 dark:text-gray-400 font-semibold">Completed</h3>
            <div class="w-10 h-10 bg-blue-500/10 rounded-lg flex items-center justify-center">
                <i data-lucide="check-circle" class="w-6 h-6 text-blue-500"></i>
            </div>
        </div>
        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $completedTasks }}</p>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Finished Orders</p>
    </div>
    
        <!-- Received Tasks Card -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-gray-600 dark:text-gray-400 font-semibold">Received</h3>
                <div class="w-10 h-10 bg-green-500/10 rounded-lg flex items-center justify-center">
                    <i data-lucide="package-check" class="w-6 h-6 text-green-500"></i>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $receivedTasks }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Orders received by customers</p>
        </div>
     <!-- Total Tasks Card -->
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-gray-600 dark:text-gray-400 font-semibold">Total </h3>
            <div class="w-10 h-10 bg-black-500/10 rounded-lg flex items-center justify-center">
                <i data-lucide="check-square" class="w-6 h-6 text-black-500"></i>
            </div>
        </div>
        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $totalTasks }}</p>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">All tasks in system</p>
    </div>
</div>

<!-- Financial Overview -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Daily Sales -->
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-gray-600 dark:text-gray-400 font-semibold">Daily Sales</h3>
            <div class="w-10 h-10 bg-green-500/10 rounded-lg flex items-center justify-center">
                <i data-lucide="trending-up" class="w-6 h-6 text-green-500"></i>
            </div>
        </div>
        <p class="text-3xl font-bold text-gray-900 dark:text-white">₱{{ number_format($dailySales, 2) }}</p>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Today's revenue</p>
    </div>

    <!-- Monthly Sales -->
    @if(auth()->user()->isAdmin())
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-gray-600 dark:text-gray-400 font-semibold">Monthly Sales</h3>
            <div class="w-10 h-10 bg-blue-500/10 rounded-lg flex items-center justify-center">
                <i data-lucide="bar-chart-3" class="w-6 h-6 text-blue-500"></i>
            </div>
        </div>
        <p class="text-3xl font-bold text-gray-900 dark:text-white">₱{{ number_format($monthlySales, 2) }}</p>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">This month's revenue</p>
    </div>
    @endif

    <!-- Total Expenses -->
    @if(auth()->user()->isAdmin())
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-gray-600 dark:text-gray-400 font-semibold">Total Expenses</h3>
            <div class="w-10 h-10 bg-red-500/10 rounded-lg flex items-center justify-center">
                <i data-lucide="credit-card" class="w-6 h-6 text-red-500"></i>
            </div>
        </div>
        <p class="text-3xl font-bold text-gray-900 dark:text-white">₱{{ number_format($totalExpenses, 2) }}</p>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Monthly expenses</p>
    </div>
    @endif
    
  <!-- Today Expenses -->
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-gray-600 dark:text-gray-400 font-semibold text-sm">Today Expenses</h3>
            <div class="w-8 h-8 bg-red-500/10 rounded-lg flex items-center justify-center">
                <i data-lucide="calendar" class="w-5 h-5 text-red-500"></i>
            </div>
        </div>
        <p class="text-2xl font-bold text-gray-900 dark:text-white">₱{{ number_format($todayTotal, 2) }}</p>
    </div>
</div>



<!-- Charts and Analytics -->
<div class="grid grid-cols-1 {{ auth()->user()->isAdmin() ? 'lg:grid-cols-2' : '' }} gap-6 mb-8">
    <!-- Task Status Chart -->
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Task Status Distribution</h3>
        {{-- ✅ FIX: wrap canvas in a fixed-height container --}}
        <div style="position: relative; height: 300px;">
            <canvas id="taskStatusChart"></canvas>
        </div>
    </div>

    <!-- Revenue Chart -->
    @if(auth()->user()->isAdmin())
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Monthly Revenue</h3>
        {{-- ✅ FIX: wrap canvas in a fixed-height container --}}
        <div style="position: relative; height: 300px;">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>
    @endif
</div>



@endsection

@section('scripts')
<script>
    const isDark = document.documentElement.classList.contains('dark');
    const gridColor   = isDark ? '#374151' : '#e5e7eb';
    const tickColor   = isDark ? '#d1d5db' : '#6b7280';
    const legendColor = '#ffffff';
    const borderColor = isDark ? '#1f2937' : '#ffffff';

    // Task Status Chart
    const taskStatusCtx = document.getElementById('taskStatusChart').getContext('2d');
    const taskStatusData = @json($taskStatusData);

    const statuses = taskStatusData.map(item => item.status);
    const counts   = taskStatusData.map(item => item.count);
    const statusColors = {
        Pending: '#f97316',
        Received: '#2563eb',
        Completed: '#22c55e',
       
    };

    new Chart(taskStatusCtx, {
        type: 'doughnut',
        data: {
            labels: statuses,
            datasets: [{
                data: counts,
                backgroundColor: statuses.map(status => statusColors[status] || '#6b7280'),
                borderColor: borderColor,
                borderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: legendColor,
                        padding: 20,
                    }
                }
            }
        }
    });

    // Monthly Revenue Chart
    @if(auth()->user()->isAdmin())
    const revenueCtx  = document.getElementById('revenueChart').getContext('2d');
    const monthlyData = @json($monthlyRevenueData);

    const months   = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    const revenues = Array(12).fill(0);

    monthlyData.forEach(item => {
        revenues[new Date(item.month).getMonth()] = item.total || 0;
    });

    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                label: 'Revenue',
                data: revenues,
                borderColor: '#eab308',
                backgroundColor: 'rgba(234, 179, 8, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#eab308',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 5,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: { color: legendColor }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid:  { color: gridColor },
                    ticks: { color: tickColor }
                },
                x: {
                    grid:  { display: false },
                    ticks: { color: tickColor }
                }
            }
        }
    });
    @endif

    lucide.createIcons();
</script>
@endsection
