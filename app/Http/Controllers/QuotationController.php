<?php

namespace App\Http\Controllers;

use App\Models\Setting;
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
            ->with(['assignedTo', 'receipts', 'items'])
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

        $billingRows = collect();

        foreach ($quotations as $task) {
            if ($task->items->isNotEmpty()) {
                foreach ($task->items as $item) {
                    $billingRows->push([
                        'date' => $task->created_at->format('M d, Y'),
                        'job_order' => $task->task_id,
                        'quantity' => (int) $item->quantity,
                        'unit' => 'pc',
                        'details' => $item->job_order,
                        'price' => (float) $item->price,
                        'amount' => (float) $item->total,
                    ]);
                }

                continue;
            }

            $billingRows->push([
                'date' => $task->created_at->format('M d, Y'),
                'job_order' => $task->task_id,
                'quantity' => 1,
                'unit' => 'job',
                'details' => $task->notes ? Str::limit($task->notes, 60) : ($task->product_type ?: $task->customer_name),
                'price' => (float) $task->amount,
                'amount' => (float) $task->amount,
            ]);
        }

        $totalAmount = (float) $quotations->sum('amount');
        $totalDeposit = (float) $quotations->sum(fn ($task) => (float) ($task->paid_amount ?? $task->receipts->sum('cash_received')));
        $totalBalance = (float) $quotations->sum(fn ($task) => (float) $task->balance);
        $singleCustomer = $customerName !== null && $customerName !== '';
        $displayCustomerName = $singleCustomer
            ? $customerName
            : ($quotations->count() === 1 ? $quotations->first()?->customer_name : null);
        $displayContactNumber = $singleCustomer ? $quotations->first()?->contact_number : null;

        $companyName = Setting::get('company_name', 'PRINTA SIGNAGES & STICKERS');
        $companyAddress = Setting::get('company_address', 'KUMINTANG ST., MINTAL, DAVAO CITY');
        $companyPhone = Setting::get('company_phone', '09667550044');
        $logoPath = Setting::get('company_logo');
        $logoDataUri = null;

        if ($logoPath) {
            $logoFile = public_path('storage/' . $logoPath);

            if (is_file($logoFile)) {
                $extension = strtolower(pathinfo($logoFile, PATHINFO_EXTENSION));
                $mimeType = match ($extension) {
                    'jpg', 'jpeg' => 'image/jpeg',
                    'gif' => 'image/gif',
                    'webp' => 'image/webp',
                    default => 'image/png',
                };

                $logoDataUri = 'data:' . $mimeType . ';base64,' . base64_encode(file_get_contents($logoFile));
            }
        }

        $billingReference = str_pad((string) ($quotations->first()?->id ?? 1), 3, '0', STR_PAD_LEFT);

        $pdf = Pdf::loadView('quotation.print', [
            'quotations' => $quotations,
            'billingRows' => $billingRows,
            'totalAmount' => $totalAmount,
            'totalDeposit' => $totalDeposit,
            'totalBalance' => $totalBalance,
            'search' => $request->input('q'),
            'customerName' => $displayCustomerName,
            'customerContact' => $displayContactNumber,
            'showCustomerColumn' => ! $singleCustomer,
            'generatedAt' => now(),
            'billingReference' => $billingReference,
            'companyName' => $companyName,
            'companyAddress' => $companyAddress,
            'companyPhone' => $companyPhone,
            'logoDataUri' => $logoDataUri,
        ])->setPaper('a4', 'portrait');

        $filename = 'billing-' . ($displayCustomerName ? Str::slug($displayCustomerName) . '-' : '') . now()->format('Ymd-His') . '.pdf';

        return $pdf->download($filename);
    }
}