<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Receipt;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function salesReport(Request $request)
    {
        $query = Receipt::query();

        if ($request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $receipts = $query->with(['task', 'issuedBy'])->get();

        $totalSales = $receipts->sum('total');
        $totalDiscount = $receipts->sum('discount');
        $totalTax = $receipts->sum('tax');

        $paymentMethodBreakdown = $receipts->groupBy('payment_method')
            ->map(fn($group) => $group->sum('total'));

        return view('reports.sales', compact(
            'receipts',
            'totalSales',
            'totalDiscount',
            'totalTax',
            'paymentMethodBreakdown'
        ));
    }

    public function expenseReport(Request $request)
    {
        $query = Expense::query();

        if ($request->start_date) {
            $query->whereDate('date', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        $expenses = $query->with('recordedBy')->get();

        $totalExpenses = $expenses->sum('amount');
        $categoryBreakdown = $expenses->groupBy('category')
            ->map(fn($group) => $group->sum('amount'));

        return view('reports.expenses', compact(
            'expenses',
            'totalExpenses',
            'categoryBreakdown'
        ));
    }

    public function taskReport(Request $request)
    {
        $query = Task::query();

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $tasks = $query->with('assignedTo')->get();

        $totalTasks = $tasks->count();
        $completedTasks = $tasks->whereIn('status', ['Completed', 'Received'])->count();
        $completionRate = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;
        $statusBreakdown = $tasks->groupBy('status')->map->count();

        return view('reports.tasks', compact(
            'tasks',
            'totalTasks',
            'completedTasks',
            'completionRate',
            'statusBreakdown'
        ));
    }

    public function productivityReport(Request $request)
    {
        $start_date = $request->start_date ?? Carbon::now()->startOfMonth();
        $end_date = $request->end_date ?? Carbon::now();

        $staffProductivity = Task::whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->with('assignedTo')
            ->get()
            ->groupBy('assigned_to')
            ->map(function ($tasks) {
                return [
                    'total' => $tasks->count(),
                    'completed' => $tasks->whereIn('status', ['Completed', 'Received'])->count(),
                    'pending' => $tasks->where('status', 'Pending')->count(),
                ];
            });

        return view('reports.productivity', compact('staffProductivity'));
    }

    public function monthlySummary(Request $request)
    {
        $year = $request->year ?? Carbon::now()->year;

        $monthlySales = [];
        $monthlyExpenses = [];

        for ($month = 1; $month <= 12; $month++) {
            $monthlyReceipts = Receipt::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->sum('total');

            $monthlyExpense = Expense::whereYear('date', $year)
                ->whereMonth('date', $month)
                ->sum('amount');

            $monthlySales[$month] = $monthlyReceipts;
            $monthlyExpenses[$month] = $monthlyExpense;
        }

        return view('reports.monthly', compact('monthlySales', 'monthlyExpenses', 'year'));
    }

    public function exportPdf($report, Request $request)
    {
        switch ($report) {
            case 'sales':
                $view = 'reports.sales';
                $data = $this->getSalesData($request);
                $filename = 'sales-report.pdf';
                break;
            case 'expenses':
                $view = 'reports.expenses';
                $data = $this->getExpensesData($request);
                $filename = 'expense-report.pdf';
                break;
            case 'tasks':
                $view = 'reports.tasks';
                $data = $this->getTasksData($request);
                $filename = 'task-report.pdf';
                break;
            default:
                return redirect()->back();
        }

        $pdf = Pdf::loadView($view, $data);
        return $pdf->download($filename);
    }

    private function getSalesData(Request $request)
    {
        $query = Receipt::query();

        if ($request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $receipts = $query->with(['task', 'issuedBy'])->get();

        return [
            'receipts' => $receipts,
            'totalSales' => $receipts->sum('total'),
        ];
    }

    private function getExpensesData(Request $request)
    {
        $query = Expense::query();

        if ($request->start_date) {
            $query->whereDate('date', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        $expenses = $query->with('recordedBy')->get();

        return [
            'expenses' => $expenses,
            'totalExpenses' => $expenses->sum('amount'),
        ];
    }

    private function getTasksData(Request $request)
    {
        $query = Task::query();

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $tasks = $query->with('assignedTo')->get();

        return [
            'tasks' => $tasks,
            'totalTasks' => $tasks->count(),
        ];
    }
}
