<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Receipt;
use App\Models\Expense;
use App\Models\Pcv;
use Illuminate\Http\Request;
use App\Exports\ReportExcelExporter;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    private const REPORT_TYPES = [
        'sales'          => 'Sales report',
        'expenses'       => 'Expense report',
        'pcv'            => 'PCV report',
        'tasks'          => 'Task report',
        'productivity'   => 'Productivity',
        'monthly'        => 'Monthly summary',
        'daily_expenses' => 'Daily expenses',
        'daily_report'   => 'Daily report',
    ];

    private const REPORT_TYPE_META = [
        'sales'          => ['icon' => 'line-chart', 'wide' => false],
        'expenses'       => ['icon' => 'receipt', 'wide' => false],
        'pcv'            => ['icon' => 'wallet', 'wide' => false],
        'tasks'          => ['icon' => 'check-square', 'wide' => false],
        'productivity'   => ['icon' => 'zap', 'wide' => false],
        'monthly'        => ['icon' => 'calendar-clock', 'wide' => false],
        'daily_expenses' => ['icon' => 'calendar', 'wide' => false],
        'daily_report'   => ['icon' => 'calendar-days', 'wide' => false],
    ];

    private function getAvailableReportTypes(): array
    {
        $allTypes = self::REPORT_TYPES;

        if (auth()->check() && !auth()->user()->isAdmin()) {
            unset($allTypes['tasks']);
            unset($allTypes['monthly']);
            unset($allTypes['productivity']);
            unset($allTypes['expenses']);
            unset($allTypes['pcv']);
        }

        return $allTypes;
    }

    public function index(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $selectedTypes = $request->input('types', []);

        $reports = [];
        $availableTypes = $this->getAvailableReportTypes();

        if ($request->boolean('generate')) {
            $validated = $request->validate([
                'types'        => ['required', 'array', 'min:1'],
                'types.*'      => [Rule::in(array_keys($availableTypes))],
                'start_date'   => 'required|date',
                'end_date'     => 'required|date|after_or_equal:start_date',
            ]);

            $startDate = $validated['start_date'];
            $endDate = $validated['end_date'];
            $selectedTypes = $validated['types'];

            foreach ($selectedTypes as $type) {
                $reports[$type] = $this->buildReportData($type, $startDate, $endDate);
            }
        }

        return view('reports.index', [
            'reportTypes'    => $availableTypes,
            'reportTypeMeta' => self::REPORT_TYPE_META,
            'startDate'      => $startDate,
            'endDate'        => $endDate,
            'selectedTypes'  => $selectedTypes,
            'reports'        => $reports,
        ]);
    }

    public function exportExcel(Request $request, ReportExcelExporter $exporter)
    {
        $baseRules = [
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ];

        $availableTypes = $this->getAvailableReportTypes();

        if ($request->has('types')) {
            $validated = $request->validate(array_merge($baseRules, [
                'types'   => ['required', 'array', 'min:1'],
                'types.*' => [Rule::in(array_keys($availableTypes))],
            ]));

            $reportsByType = [];
            foreach ($validated['types'] as $type) {
                $reportsByType[$type] = $this->buildReportData($type, $validated['start_date'], $validated['end_date']);
            }

            return $exporter->downloadMultiple(
                $reportsByType,
                $validated['start_date'],
                $validated['end_date']
            );
        }

        $validated = $request->validate(array_merge($baseRules, [
            'type' => ['required', Rule::in(array_keys($availableTypes))],
        ]));

        $data = $this->buildReportData($validated['type'], $validated['start_date'], $validated['end_date']);

        return $exporter->download(
            $validated['type'],
            $data,
            $validated['start_date'],
            $validated['end_date']
        );
    }

    private function buildReportData(string $type, string $startDate, string $endDate): array
    {
        return match ($type) {
            'sales'          => $this->salesData($startDate, $endDate),
            'expenses'       => $this->expenseData($startDate, $endDate),
            'pcv'            => $this->pcvData($startDate, $endDate),
            'tasks'          => $this->taskData($startDate, $endDate),
            'productivity'   => $this->productivityData($startDate, $endDate),
            'monthly'        => $this->monthlyData($startDate, $endDate),
            'daily_expenses' => $this->dailyExpensesData($startDate, $endDate),
            'daily_report'   => $this->dailyReportData($startDate, $endDate),
            default          => [],
        };
    }

    private function salesData(string $startDate, string $endDate): array
    {
        $receipts = Receipt::whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->with(['task', 'issuedBy'])
            ->orderByDesc('created_at')
            ->get();

        return [
            'type'                   => 'sales',
            'receipts'               => $receipts,
            'totalSales'             => $receipts->sum('cash_received'),
            'totalDiscount'          => $receipts->sum('discount'),
            'totalTax'               => $receipts->sum('tax'),
            'paymentMethodBreakdown' => $receipts->groupBy('payment_method')->map(fn ($g) => $g->sum('cash_received')),
        ];
    }

    private function expenseData(string $startDate, string $endDate): array
    {
        $expenses = Expense::whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->with('recordedBy')
            ->orderByDesc('date')
            ->get();

        return [
            'type'              => 'expenses',
            'expenses'          => $expenses,
            'totalExpenses'     => $expenses->sum('amount'),
            'categoryBreakdown' => $expenses->groupBy('category')->map(fn ($g) => $g->sum('amount')),
        ];
    }

    private function pcvData(string $startDate, string $endDate): array
    {
        $pcvs = Pcv::whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->with('recordedBy')
            ->orderByDesc('date')
            ->get();

        return [
            'type'           => 'pcv',
            'pcvs'           => $pcvs,
            'totalPcv'       => $pcvs->sum('amount'),
            'categoryBreakdown' => $pcvs->groupBy(fn ($pcv) => $pcv->category === 'Other' && $pcv->other_category ? $pcv->other_category : $pcv->category)
                ->map(fn ($g) => $g->sum('amount')),
        ];
    }

    private function taskData(string $startDate, string $endDate): array
    {
        $tasks = Task::whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->with('assignedTo')
            ->orderByDesc('created_at')
            ->get();

        $completedTasks = $tasks->whereIn('status', ['Completed', 'Received'])->count();
        $totalTasks = $tasks->count();

        return [
            'type'            => 'tasks',
            'tasks'           => $tasks,
            'totalTasks'      => $totalTasks,
            'completedTasks'  => $completedTasks,
            'completionRate'  => $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0,
            'statusBreakdown' => $tasks->groupBy('status')->map->count(),
        ];
    }

    private function productivityData(string $startDate, string $endDate): array
    {
        $tasks = Task::whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->with('assignedTo')
            ->get();

        $staffProductivity = $tasks->groupBy('assigned_to')->map(function ($group) {
            $name = $group->first()->assignedTo?->name ?? 'Unassigned';

            return [
                'name'      => $name,
                'total'     => $group->count(),
                'completed' => $group->whereIn('status', ['Completed', 'Received'])->count(),
                'pending'   => $group->where('status', 'Pending')->count(),
            ];
        })->values();

        return [
            'type'              => 'productivity',
            'staffProductivity' => $staffProductivity,
        ];
    }

    private function monthlyData(string $startDate, string $endDate): array
    {
        $rangeStart = Carbon::parse($startDate)->startOfDay();
        $rangeEnd = Carbon::parse($endDate)->endOfDay();
        $cursor = $rangeStart->copy()->startOfMonth();

        $rows = [];
        $totalSales = 0;
        $totalExpenses = 0;
        $totalPcv = 0;

        while ($cursor <= $rangeEnd) {
            $from = $cursor->copy()->startOfMonth()->max($rangeStart);
            $to = $cursor->copy()->endOfMonth()->min($rangeEnd);

            $sales = (float) Receipt::whereBetween('created_at', [$from, $to])->sum('cash_received');

            $expenses = (float) Expense::whereDate('date', '>=', $from->toDateString())
                ->whereDate('date', '<=', $to->toDateString())
                ->sum('amount');

            $pcv = (float) Pcv::whereDate('date', '>=', $from->toDateString())
                ->whereDate('date', '<=', $to->toDateString())
                ->sum('amount');

            $rows[] = [
                'label'    => $cursor->format('F Y'),
                'sales'    => $sales,
                'expenses' => $expenses,
                'pcv'      => $pcv,
                'total'    => $sales - $expenses - $pcv,
            ];

            $totalSales += $sales;
            $totalExpenses += $expenses;
            $totalPcv += $pcv;
            $cursor->addMonth();
        }

        return [
            'type'          => 'monthly',
            'monthlyRows'   => $rows,
            'totalSales'    => $totalSales,
            'totalExpenses' => $totalExpenses,
            'totalPcv'      => $totalPcv,
            'netProfit'     => $totalSales - $totalExpenses - $totalPcv,
        ];
    }

    private function dailyExpensesData(string $startDate, string $endDate): array
    {
        $expenses = Expense::whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->with('recordedBy')
            ->orderByDesc('date')
            ->get();

        $grouped = $expenses->groupBy(fn ($e) => $e->date->format('Y-m-d'));

        $dailySummary = [];
        foreach ($grouped as $date => $dayExpenses) {
            $dailySummary[] = [
                'date' => Carbon::parse($date),
                'total' => $dayExpenses->sum('amount'),
                'count' => $dayExpenses->count(),
                'items' => $dayExpenses
            ];
        }

        return [
            'type'          => 'daily_expenses',
            'expenses'      => $expenses,
            'totalExpenses' => $expenses->sum('amount'),
            'dailySummary'  => $dailySummary,
        ];
    }

    private function dailyReportData(string $startDate, string $endDate): array
    {
        $rangeStart = Carbon::parse($startDate)->startOfDay();
        $rangeEnd = Carbon::parse($endDate)->endOfDay();
        $cursor = $rangeStart->copy();

        $rows = [];
        $totalSales = 0.0;
        $totalExpenses = 0.0;

        while ($cursor <= $rangeEnd) {
            $from = $cursor->copy()->startOfDay();
            $to = $cursor->copy()->endOfDay();

            $sales = (float) Receipt::whereBetween('created_at', [$from, $to])->sum('cash_received');
            $expenses = (float) Expense::whereDate('date', '>=', $from->toDateString())
                ->whereDate('date', '<=', $to->toDateString())
                ->sum('amount');

            if ($sales > 0 || $expenses > 0) {
                $rows[] = [
                    'date' => $cursor->copy(),
                    'sales' => $sales,
                    'expenses' => $expenses,
                    'profit' => $sales - $expenses,
                ];
            }

            $totalSales += $sales;
            $totalExpenses += $expenses;
            $cursor->addDay();
        }

        // Sort descending by date so most recent is on top
        usort($rows, fn($a, $b) => $b['date'] <=> $a['date']);

        return [
            'type'          => 'daily_report',
            'dailyRows'     => $rows,
            'totalSales'    => $totalSales,
            'totalExpenses' => $totalExpenses,
            'netProfit'     => $totalSales - $totalExpenses,
        ];
    }
}
