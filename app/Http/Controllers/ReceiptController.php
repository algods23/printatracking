<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use App\Models\ReceiptItem;
use App\Models\Task;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReceiptController extends Controller
{
    public function index()
    {
        $receipts = Receipt::with(['task', 'issuedBy'])->latest()->paginate(15);
        return view('receipts.index', compact('receipts'));
    }

    public function create(Request $request)
    {
        $tasksQuery = Task::where('status', '!=', 'Cancelled');

        $tasks = $tasksQuery
            ->withSum('receipts as paid_amount', 'cash_received')
            ->latest()
            ->get();

        $selectedTask = $request->filled('task_id')
            ? $tasks->firstWhere('id', (int) $request->task_id)
            : null;

        if (! $selectedTask) {
            return redirect()->route('tasks.index')->with('error', 'Open a task first before creating a receipt.');
        }

        return view('receipts.create', compact('tasks', 'selectedTask'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'payment_amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:Cash,Card,Check,Bank Transfer,GCash,Maya,Credit Card,Other',
            'payment_reference' => 'required_unless:payment_method,Cash|nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $normalizedMethod = $this->normalizePaymentMethod($validated['payment_method']);

        $receipt = DB::transaction(function () use ($validated, $normalizedMethod) {
            $task = Task::with(['items'])
                ->withSum('receipts as paid_amount', 'cash_received')
                ->lockForUpdate()
                ->findOrFail($validated['task_id']);

            $paidAmount = (float) ($task->paid_amount ?? 0);
            $taskAmount = (float) $task->amount;
            $remainingAmount = max($taskAmount - $paidAmount, 0);
            $paymentAmount = min((float) $validated['payment_amount'], $remainingAmount);

            if ($remainingAmount <= 0) {
                abort(422, 'This task is already fully paid.');
            }

            if ($paymentAmount <= 0) {
                abort(422, 'Payment amount must be greater than zero.');
            }

            $receipt = Receipt::create([
                'task_id' => $task->id,
                'customer_name' => $task->customer_name,
                'customer_phone' => $task->contact_number,
                'customer_email' => null,
                'subtotal' => $taskAmount,
                'discount' => 0,
                'tax' => 0,
                'total' => $taskAmount,
                'cash_received' => $paymentAmount,
                'change' => 0,
                'payment_method' => $normalizedMethod,
                'payment_channel' => $validated['payment_method'],
                'payment_reference' => $normalizedMethod === 'Cash' ? null : $validated['payment_reference'],
                'notes' => $validated['notes'] ?? null,
                'issued_by' => Auth::id(),
            ]);

            if ($task->items->isNotEmpty()) {
                foreach ($task->items as $item) {
                    ReceiptItem::create([
                        'receipt_id' => $receipt->id,
                        'product_name' => $item->job_order,
                        'description' => "Payment for {$task->task_id}",
                        'quantity' => $item->quantity,
                        'unit_price' => $item->price,
                        'total' => $item->total,
                    ]);
                }
            } else {
                ReceiptItem::create([
                    'receipt_id' => $receipt->id,
                    'product_name' => $task->product_type,
                    'description' => "Payment for {$task->task_id}",
                    'quantity' => 1,
                    'unit_price' => $taskAmount,
                    'total' => $taskAmount,
                ]);
            }

            $task->syncPaymentStatus();

            return $receipt;
        });

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'created',
            'model_type' => Receipt::class,
            'model_id' => $receipt->id,
            'description' => "Receipt {$receipt->receipt_number} created",
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('tasks.show', $receipt->task_id)->with('success', 'Payment receipt created successfully');
    }

    public function show(Receipt $receipt)
    {
        $receipt->load(['task.receipts', 'issuedBy', 'items']);
        return view('receipts.show', compact('receipt'));
    }

    public function edit(Receipt $receipt)
    {
        $receipt->load(['task', 'items']);
        $tasks = Task::where('status', '!=', 'Cancelled')->get();
        return view('receipts.edit', compact('receipt', 'tasks'));
    }

    public function update(Request $request, Receipt $receipt)
    {
        $validated = $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'payment_amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:Cash,Card,Check,Bank Transfer,GCash,Maya,Credit Card,Other',
            'payment_reference' => 'required_unless:payment_method,Cash|nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $normalizedMethod = $this->normalizePaymentMethod($validated['payment_method']);

        DB::transaction(function () use ($validated, $receipt, $normalizedMethod) {
            $task = Task::withSum('receipts as paid_amount', 'cash_received')
                ->lockForUpdate()
                ->findOrFail($validated['task_id']);

            $paidWithoutReceipt = (float) $task->receipts()
                ->whereKeyNot($receipt->id)
                ->sum('cash_received');
            $taskAmount = (float) $task->amount;
            $remainingAmount = max($taskAmount - $paidWithoutReceipt, 0);
            $paymentAmount = min((float) $validated['payment_amount'], $remainingAmount);

            $receipt->update([
                'task_id' => $task->id,
                'customer_name' => $task->customer_name,
                'customer_phone' => $task->contact_number,
                'subtotal' => $taskAmount,
                'total' => $taskAmount,
                'cash_received' => $paymentAmount,
                'change' => 0,
                'payment_method' => $normalizedMethod,
                'payment_channel' => $validated['payment_method'],
                'payment_reference' => $normalizedMethod === 'Cash' ? null : $validated['payment_reference'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $receipt->items()->delete();
            ReceiptItem::create([
                'receipt_id' => $receipt->id,
                'product_name' => $task->product_type,
                'description' => "Payment for {$task->task_id}",
                'quantity' => 1,
                'unit_price' => $taskAmount,
                'total' => $taskAmount,
            ]);

            $task->syncPaymentStatus();
        });

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'updated',
            'model_type' => Receipt::class,
            'model_id' => $receipt->id,
            'description' => "Receipt {$receipt->receipt_number} updated",
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('receipts.show', $receipt)->with('success', 'Receipt updated successfully');
    }

    public function destroy(Receipt $receipt)
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'deleted',
            'model_type' => Receipt::class,
            'model_id' => $receipt->id,
            'description' => "Receipt {$receipt->receipt_number} deleted",
            'ip_address' => request()->ip(),
        ]);

        $task = $receipt->task;

        $receipt->delete();

        if ($task) {
            $task->syncPaymentStatus();
        }

        return redirect()->route('receipts.index')->with('success', 'Receipt deleted successfully');
    }

    public function printReceipt(Receipt $receipt)
    {
        $receipt->load(['task', 'issuedBy', 'items']);

        $pdf = Pdf::loadView('receipts.print', ['receipt' => $receipt]);

        return $pdf->stream("receipt-{$receipt->receipt_number}.pdf");
    }

    public function exportPdf(Receipt $receipt)
    {
        $receipt->load(['task', 'issuedBy', 'items']);

        $pdf = Pdf::loadView('receipts.print', ['receipt' => $receipt]);

        return $pdf->download("receipt-{$receipt->receipt_number}.pdf");
    }

    private function normalizePaymentMethod(string $method): string
    {
        return match ($method) {
            'Credit Card' => 'Card',
            'GCash', 'Maya' => 'Other',
            default => in_array($method, ['Cash', 'Card', 'Check', 'Bank Transfer', 'Other'], true)
                ? $method
                : 'Other',
        };
    }

}
