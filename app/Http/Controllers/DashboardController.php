<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Receipt;
use App\Models\Expense;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $totalTasks = Task::count();
        $pendingTasks = Task::where('status', 'Pending')->count();
        $receivedTasks = Task::where('status', 'Received')->count();
        $completedTasks = Task::where('status', 'Completed')->count();

        // Calculate sales from actual payments only. Unpaid job orders have no receipts,
        // so they should not increase revenue totals.
        $dailySales = Receipt::whereDate('created_at', Carbon::today())
            ->whereHas('task', fn ($query) => $query->where('status', '!=', 'Cancelled'))
            ->sum('cash_received');
        $monthlySales = Receipt::whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereHas('task', fn ($query) => $query->where('status', '!=', 'Cancelled'))
            ->sum('cash_received');

        // Calculate expenses
        $totalExpenses = Expense::whereDate('date', '>=', Carbon::now()->startOfMonth())
            ->sum('amount');

        // Recent activities
        $recentActivities = ActivityLog::with('user')
            ->latest()
            ->limit(10)
            ->get();

        // Task status breakdown for chart
        $taskStatusData = collect([
            [
                'status' => 'Pending',
                'count' => Task::where('status', 'Pending')->count(),
            ],
            [
                'status' => 'Received',
                'count' => Task::where('status', 'Received')->count(),
            ],
            [
                'status' => 'Completed',
                'count' => Task::where('status', 'Completed')->count(),
            ],

        ]);

        // Revenue by month
        $monthlyRevenueData = Receipt::select(
            DB::raw("strftime('%Y-%m', created_at) as month"),
            DB::raw('SUM(cash_received) as total')
        )
            ->whereYear('created_at', Carbon::now()->year)
            ->whereHas('task', fn ($query) => $query->where('status', '!=', 'Cancelled'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('dashboard.index', [
            'totalTasks' => $totalTasks,
            'pendingTasks' => $pendingTasks,
            'receivedTasks' => $receivedTasks,
            'completedTasks' => $completedTasks,
            'dailySales' => $dailySales,
            'monthlySales' => $monthlySales,
            'totalExpenses' => $totalExpenses,
            'recentActivities' => $recentActivities,
            'taskStatusData' => $taskStatusData,
            'monthlyRevenueData' => $monthlyRevenueData,
        ]);
    }
}
