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

        $customerNames = $quotations
            ->pluck('customer_name')
            ->filter()
            ->map(fn ($name) => trim((string) $name))
            ->unique(fn ($name) => mb_strtolower($name))
            ->values();

        $contactNumbers = $quotations
            ->pluck('contact_number')
            ->filter()
            ->map(fn ($number) => trim((string) $number))
            ->unique(fn ($number) => mb_strtolower($number))
            ->values();

        $displayCustomerName = $customerNames->count() === 1
            ? $customerNames->first()
            : ($customerName ?: null);
        $displayContactNumber = $contactNumbers->count() === 1 ? $contactNumbers->first() : null;
        $multipleCustomers = $customerNames->count() > 1;

        $companyName = Setting::get('company_name', 'PRINTA SIGNAGES & STICKERS');
        $companyAddress = Setting::get('company_address', 'KUMINTANG ST., MINTAL, DAVAO CITY');
        $companyPhone = Setting::get('company_phone', '09667550044');
        $logoPath = Setting::get('company_logo');
        $logoDataUri = null;

        $logoCandidates = array_filter([
            $logoPath ? public_path('storage/' . $logoPath) : null,
            public_path('images/printa-3color.png'),
        ]);

        foreach ($logoCandidates as $logoFile) {
            if (! is_file($logoFile)) {
                continue;
            }

            $extension = strtolower(pathinfo($logoFile, PATHINFO_EXTENSION));
            $mimeType = match ($extension) {
                'jpg', 'jpeg' => 'image/jpeg',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                default => 'image/png',
            };

            $logoDataUri = 'data:' . $mimeType . ';base64,' . base64_encode(file_get_contents($logoFile));
            break;
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
            'showCustomerColumn' => $multipleCustomers,
            'generatedAt' => now(),
            'billingReference' => $billingReference,
            'companyName' => $companyName,
            'companyAddress' => $companyAddress,
            'companyPhone' => $companyPhone,
            'logoDataUri' => $logoDataUri,
            'dueDate' => \Carbon\Carbon::parse($request->input('due_date', now()->addDays(14)))->format('M d, Y'),
            'authRep' => $request->input('auth_rep', 'Jelian Fernandez'),
        ])->setPaper('a4', 'portrait');

        $filename = 'billing-' . ($displayCustomerName ? Str::slug($displayCustomerName) . '-' : '') . now()->format('Ymd-His') . '.pdf';

        return $pdf->download($filename);
    }
}