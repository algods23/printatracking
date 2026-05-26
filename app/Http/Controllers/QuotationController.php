<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        if (! auth()->check() || ! auth()->user()?->isAdmin()) {
            abort(403, 'Only administrators can access billing.');
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
            abort(403, 'Only administrators can access billing.');
        }

        $customerName = $request->input('customer');
        $selectedIds = array_values(array_filter((array) $request->input('ids', [])));
        $query = Task::query()
            ->with(['assignedTo', 'receipts'])
            ->where('status', '!=', 'Cancelled')
            ->where('payment_status', '!=', 'Paid');

        if (! empty($selectedIds)) {
            $query->whereIn('id', $selectedIds);
        } elseif ($customerName) {
            $query->where('customer_name', $customerName);
        } elseif ($request->filled('q')) {
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
        $singleCustomer = $customerName !== null && $customerName !== '';
        $displayCustomerName = $singleCustomer
            ? $customerName
            : ($quotations->count() === 1 ? $quotations->first()?->customer_name : null);
        $displayContactNumber = $singleCustomer ? $quotations->first()?->contact_number : null;

        $pdf = Pdf::loadView('quotation.print', [
            'quotations' => $quotations,
            'totalAmount' => $totalAmount,
            'totalDeposit' => $totalDeposit,
            'totalBalance' => $totalBalance,
            'search' => $request->input('q'),
            'customerName' => $displayCustomerName,
            'customerContact' => $displayContactNumber,
            'showCustomerColumn' => ! $singleCustomer,
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        $filename = 'billing-' . ($displayCustomerName ? Str::slug($displayCustomerName) . '-' : '') . now()->format('Ymd-His') . '.pdf';

        return $pdf->download($filename);
    }
}