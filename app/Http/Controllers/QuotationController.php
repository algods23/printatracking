<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        if (! auth()->check() || ! auth()->user()?->isAdmin()) {
            abort(403, 'Only administrators can access quotations.');
        }

        $query = Task::query()
            ->with(['assignedTo', 'receipts'])
            ->where('status', '!=', 'Cancelled')
            ->where('payment_status', '!=', 'Paid');

        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('task_id', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%");
            });

            $query->orderByRaw(
                'CASE WHEN customer_name LIKE ? THEN 0 WHEN customer_name LIKE ? THEN 1 ELSE 2 END',
                ["{$search}%", "%{$search}%"]
            );
        }

        $quotations = $query->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $summaryQuery = Task::query()
            ->where('status', '!=', 'Cancelled')
            ->where('payment_status', '!=', 'Paid');

        if ($request->filled('q')) {
            $search = $request->input('q');
            $summaryQuery->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('task_id', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%");
            });
        }

        $summary = $summaryQuery
            ->withSum('receipts as paid_amount', 'cash_received')
            ->get();

        $totalAmount = (float) $summary->sum('amount');
        $totalDeposit = (float) $summary->sum(fn ($task) => (float) ($task->paid_amount ?? 0));
        $totalBalance = (float) $summary->sum(fn ($task) => (float) $task->balance);

        return view('quotation.index', compact(
            'quotations',
            'totalAmount',
            'totalDeposit',
            'totalBalance'
        ));
    }

    public function download(Request $request)
    {
        if (! auth()->check() || ! auth()->user()?->isAdmin()) {
            abort(403, 'Only administrators can access quotations.');
        }

        $query = Task::query()
            ->with(['assignedTo', 'receipts'])
            ->where('status', '!=', 'Cancelled')
            ->where('payment_status', '!=', 'Paid');

        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('task_id', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%");
            });
        }

        $quotations = $query->orderByDesc('created_at')->get();

        $totalAmount = (float) $quotations->sum('amount');
        $totalDeposit = (float) $quotations->sum(fn ($task) => (float) ($task->paid_amount ?? $task->receipts->sum('cash_received')));
        $totalBalance = (float) $quotations->sum(fn ($task) => (float) $task->balance);

        $html = view('quotation.print', [
            'quotations' => $quotations,
            'totalAmount' => $totalAmount,
            'totalDeposit' => $totalDeposit,
            'totalBalance' => $totalBalance,
            'search' => $request->input('q'),
            'generatedAt' => now(),
        ])->render();

        $filename = 'quotation-' . now()->format('Ymd-His') . '.html';

        return response()->streamDownload(function () use ($html) {
            echo $html;
        }, $filename, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }
}