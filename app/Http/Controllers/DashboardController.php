<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Receipt;
use App\Models\Expense;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $isAdmin = Auth::user()?->isAdmin() ?? false;
        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        $taskQuery = Task::query();
        $receiptQuery = Receipt::whereHas('task', fn ($query) => $query->where('status', '!=', 'Cancelled'));
        $expenseQuery = Expense::query();
        $activityQuery = ActivityLog::with('user')->latest();

        if ($isAdmin) {
            $taskQuery->whereBetween('created_at', [$monthStart, $monthEnd]);
            $receiptQuery->whereBetween('created_at', [$monthStart, $monthEnd]);
            $expenseQuery->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()]);
            $activityQuery->whereBetween('created_at', [$monthStart, $monthEnd]);
        }

        $totalTasks = (clone $taskQuery)->count();
        $pendingTasks = (clone $taskQuery)->where('status', 'Pending')->count();
        $receivedTasks = (clone $taskQuery)->where('status', 'Received')->count();
        $completedTasks = (clone $taskQuery)->where('status', 'Completed')->count();

        // Calculate sales from actual payments only. Unpaid job orders have no receipts,
        // so they should not increase revenue totals.
        $dailySales = (clone $receiptQuery)
            ->whereDate('created_at', $today)
            ->sum('cash_received');
        $monthlySales = (clone $receiptQuery)
            ->sum('cash_received');

        
            
        // Calculate expenses
        $totalExpenses = (clone $expenseQuery)
            ->sum('amount');

        // Recent activities
        $recentActivities = (clone $activityQuery)
            ->limit(10)
            ->get();

        // Task status breakdown for chart
        $taskStatusData = collect([
            [
                'status' => 'Pending',
                'count' => (clone $taskQuery)->where('status', 'Pending')->count(),
            ],
            [
                'status' => 'Received',
                'count' => (clone $taskQuery)->where('status', 'Received')->count(),
            ],
            [
                'status' => 'Completed',
                'count' => (clone $taskQuery)->where('status', 'Completed')->count(),
            ],

        ]);

        $todayTotal = (clone $expenseQuery)->whereDate('date', $today)->sum('amount');

        // Revenue by month
        $monthlyRevenueData = (clone $receiptQuery)->select(
            DB::raw("strftime('%Y-%m', created_at) as month"),
            DB::raw('SUM(cash_received) as total')
        )
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
            'todayTotal'=> $todayTotal, 
            'recentActivities' => $recentActivities,
            'taskStatusData' => $taskStatusData,
            'monthlyRevenueData' => $monthlyRevenueData,
        ]);
    }
}
