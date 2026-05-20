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
        $ongoingTasks = Task::whereIn('status', ['Designing', 'Printing', 'Installing'])->count();
        $completedTasks = Task::where('status', 'Completed')->count();

        // Calculate sales
        $dailySales = Receipt::whereDate('created_at', Carbon::today())
            ->sum('total');
        $monthlySales = Receipt::whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('total');

        // Calculate expenses
        $totalExpenses = Expense::whereDate('date', '>=', Carbon::now()->startOfMonth())
            ->sum('amount');

        // Recent activities
        $recentActivities = ActivityLog::with('user')
            ->latest()
            ->limit(10)
            ->get();

        // Task status breakdown for chart
        $taskStatusData = Task::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        // Revenue by month
        $monthlyRevenueData = Receipt::select(
            DB::raw("strftime('%Y-%m', created_at) as month"),
            DB::raw('SUM(total) as total')
        )
            ->whereYear('created_at', Carbon::now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('dashboard.index', [
            'totalTasks' => $totalTasks,
            'pendingTasks' => $pendingTasks,
            'ongoingTasks' => $ongoingTasks,
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
