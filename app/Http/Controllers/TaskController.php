<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Receipt;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->filteredTasksQuery($request);
        $tasks = $query->with('assignedTo')
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('tasks.index', [
            'tasks' => $tasks,
            'query' => $request->input('q'),
        ]);
    }

    public function create()
    {
        $staff = $this->assignableUsers();
        return view('tasks.create', compact('staff'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name'    => 'required|string|max:255',
            'contact_number'   => 'nullable|string|max:20',
            'product_type'     => 'nullable|in:Signage,Sticker,Banner,Label,Other',
            'signage_type'     => 'nullable|in:Digital,Vinyl,Neon,LED,Wooden,Metal,Other',
            'sticker_type'     => 'nullable|in:Vinyl,Paper,Label,Die-cut,Other',
            'assigned_to'      => 'nullable|exists:users,id',
            'due_date'         => 'nullable|date|after_or_equal:today',
            'due_time'         => 'nullable|date_format:H:i',
            'priority'         => 'nullable|in:Low,Medium,High,Urgent',
            'notes'            => 'nullable|string',
            'payment_amount'   => 'nullable|numeric|min:0',
            'payment_method'   => 'nullable|in:Cash,Bank Transfer,GCash,Maya,Credit Card,Other',
            'reference_number' => 'nullable|string',
            'items'            => 'required|array|min:1',
            'items.*.job_order' => 'required|string|max:255',
            'items.*.quantity'  => 'required|integer|min:1',
            'items.*.price'     => 'required|numeric|min:0',
            'attachments'      => 'nullable|array',
            'attachments.*'    => 'file|max:51200',
        ]);

        $validated['product_type'] = $validated['product_type'] ?? 'Other';

        if (! Auth::user()->isAdmin()) {
            $validated['assigned_to'] = Auth::id();
        }

        $items = collect($validated['items'])->map(function ($item) {
            $quantity = (int) $item['quantity'];
            $price = (float) $item['price'];

            return [
                'job_order' => $item['job_order'],
                'quantity'  => $quantity,
                'price'     => $price,
                'total'     => $quantity * $price,
            ];
        });

        $validated['amount'] = $items->sum('total');
        unset($validated['items']);

        if ($request->hasFile('attachments')) {
            $attachmentPaths = [];
            foreach ($request->file('attachments') as $file) {
                $attachmentPaths[] = $file->store('tasks/attachments', 'public');
            }
            $validated['attachments'] = $attachmentPaths;
        }

        $initialPayment = (float) ($validated['payment_amount'] ?? 0);
        $paymentMethod = $validated['payment_method'];
        $referenceNumber = $validated['reference_number'] ?? null;
        unset($validated['payment_amount'], $validated['payment_method'], $validated['reference_number']);

        $receipt = null;

        $task = DB::transaction(function () use ($validated, $items, $initialPayment, $paymentMethod, $referenceNumber, &$receipt) {
            $task = Task::create($validated);

            foreach ($items as $item) {
                $task->items()->create($item);
            }

            if ($initialPayment > 0) {
                $receipt = $task->recordPayment($initialPayment, $paymentMethod, $referenceNumber, Auth::id());
            }

            return $task;
        });

        ActivityLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'created',
            'model_type'  => Task::class,
            'model_id'    => $task->id,
            'description' => "Task {$task->task_id} created",
            'ip_address'  => request()->ip(),
        ]);

        if ($receipt) {
            ActivityLog::create([
                'user_id'     => Auth::id(),
                'action'      => 'created',
                'model_type'  => Receipt::class,
                'model_id'    => $receipt->id,
                'description' => "Receipt {$receipt->receipt_number} created with task",
                'ip_address'  => request()->ip(),
            ]);
        }

        return redirect()->route('tasks.show', $task)->with('success', 'Task created successfully');
    }

    public function show(Task $task)
    {
        $this->authorizeTaskAccess($task);

        $task->load(['assignedTo', 'items', 'receipts.issuedBy']);

        return view('tasks.show', compact('task'));
    }

    public function printJobOrder(Task $task)
    {
        $this->authorizeTaskAccess($task);

        $task->load(['items', 'receipts']);

        $totalAmount = (float) $task->amount;
        $paidAmount = (float) $task->receipts->sum('cash_received');
        $balance = max($totalAmount - $paidAmount, 0);

        $printedBy = Auth::user()?->name ?? 'Staff';

        $methods = $task->receipts->pluck('payment_method')->filter()->unique();

        $cashMethods = ['Cash', 'Card', 'Check'];
        $gcashMethods = ['Bank Transfer', 'Other'];

        $checkboxCash = $methods->contains(fn ($m) => in_array($m, $cashMethods, true));
        $checkboxGcash = $methods->contains(fn ($m) => in_array($m, $gcashMethods, true));

        if ($paidAmount > 0 && ! $checkboxCash && ! $checkboxGcash && $methods->isNotEmpty()) {
            $checkboxCash = true;
        }

        $maxRows = 8;
        $items = $task->items()->orderBy('id')->get();
        $placeholders = collect(range(1, max(0, $maxRows - $items->count())))->map(fn () => null);
        $tableRows = $items->concat($placeholders)->take($maxRows);

        $companyName = Setting::get('company_name', 'PRINTA SIGNAGES');
        $companyAddress = Setting::get('company_address', 'KUMINTANG ST., MINTAL, DAVAO CITY');
        $companyPhone = Setting::get('company_phone', '09667550044');
        $logoPath = Setting::get('company_logo');

        return view('tasks.print-job-order', compact(
            'task',
            'totalAmount',
            'paidAmount',
            'balance',
            'printedBy',
            'checkboxCash',
            'checkboxGcash',
            'tableRows',
            'companyName',
            'companyAddress',
            'companyPhone',
            'logoPath'
        ));
    }

    public function edit(Task $task)
    {
        $this->authorizeTaskAccess($task);

        $task->load(['items', 'receipts']);
        $staff = $this->assignableUsers();

        return view('tasks.edit', compact('task', 'staff'));
    }

    public function update(Request $request, Task $task)
    {
        $this->authorizeTaskAccess($task);

        $validated = $request->validate([
            'customer_name'     => 'required|string|max:255',
            'contact_number'    => 'nullable|string|max:20',
            'assigned_to'       => 'nullable|exists:users,id',
            'due_date'          => 'nullable|date',
            'due_time'          => 'nullable|date_format:H:i',
            'status'            => 'required|in:Pending,Designing,Printing,Installing,Completed,Received,Cancelled',
            'priority'          => 'nullable|in:Low,Medium,High,Urgent',
            'notes'             => 'nullable|string',
            'items'             => 'required|array|min:1',
            'items.*.job_order' => 'required|string|max:255',
            'items.*.quantity'  => 'required|integer|min:1',
            'items.*.price'     => 'required|numeric|min:0',
            'attachments'       => 'nullable|array',
            'attachments.*'     => 'file|max:51200',
        ]);

        if (! Auth::user()->isAdmin()) {
            $validated['assigned_to'] = Auth::id();
        }

        $items = collect($validated['items'])->map(function ($item) {
            $quantity = (int) $item['quantity'];
            $price = (float) $item['price'];

            return [
                'job_order' => $item['job_order'],
                'quantity'  => $quantity,
                'price'     => $price,
                'total'     => $quantity * $price,
            ];
        });

        $validated['amount'] = $items->sum('total');
        unset($validated['items']);

        if ($request->hasFile('attachments')) {
            $attachmentPaths = $task->attachments ?? [];
            foreach ($request->file('attachments') as $file) {
                $attachmentPaths[] = $file->store('tasks/attachments', 'public');
            }
            $validated['attachments'] = $attachmentPaths;
        }

        DB::transaction(function () use ($task, $validated, $items) {
            $task->update($validated);

            $task->items()->delete();
            foreach ($items as $item) {
                $task->items()->create($item);
            }

            $task->refresh();
            $task->syncPaymentStatus();
        });

        ActivityLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'updated',
            'model_type'  => Task::class,
            'model_id'    => $task->id,
            'description' => "Task {$task->task_id} updated",
            'ip_address'  => request()->ip(),
        ]);

        return redirect()->route('tasks.show', $task)->with('success', 'Task updated successfully');
    }

    public function updateStatus(Request $request, Task $task)
    {
        $this->authorizeTaskAccess($task);

        $validated = $request->validate([
            'status' => 'required|in:Pending,Designing,Printing,Installing,Completed,Cancelled',
        ]);

        if ($task->status !== $validated['status']) {
            $oldStatus = $task->status;
            $task->update(['status' => $validated['status']]);

            ActivityLog::create([
                'user_id'     => Auth::id(),
                'action'      => 'updated',
                'model_type'  => Task::class,
                'model_id'    => $task->id,
                'description' => "Task {$task->task_id} status changed from {$oldStatus} to {$validated['status']}",
                'ip_address'  => request()->ip(),
            ]);
        }

        return redirect()->route('tasks.show', $task)->with('success', 'Task status updated successfully.');
    }

    public function markReceived(Task $task)
    {
        $this->authorizeTaskAccess($task);

        if ($task->status !== 'Completed' && $task->status !== 'Received') {
            return redirect()->route('tasks.show', $task)->with('error', 'Task must be completed before it can be marked as received.');
        }

        if ($task->status !== 'Received') {
            $task->update(['status' => 'Received']);

            ActivityLog::create([
                'user_id'     => Auth::id(),
                'action'      => 'updated',
                'model_type'  => Task::class,
                'model_id'    => $task->id,
                'description' => "Task {$task->task_id} marked as received by customer",
                'ip_address'  => request()->ip(),
            ]);
        }

        return redirect()->route('tasks.show', $task)->with('success', 'Task marked as received by customer.');
    }

    public function destroy(Request $request, Task $task)
    {
        $this->authorizeTaskAccess($task);

        // Only admins can cancel tasks
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Only administrators can cancel tasks.');
        }

        $validated = $request->validate([
            'cancellation_reason' => 'nullable|string|max:500',
            'admin_password'      => ['required', 'current_password'],
        ]);

        // Delete all receipts for this task to reset paid amount to 0
        Receipt::where('task_id', $task->id)->delete();

        $task->update([
            'status' => 'Cancelled',
            'payment_status' => 'Unpaid',
            'cancellation_reason' => $validated['cancellation_reason'] ?? null,
        ]);

        ActivityLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'cancelled',
            'model_type'  => Task::class,
            'model_id'    => $task->id,
            'description' => "Task {$task->task_id} cancelled. Reason: " . ($validated['cancellation_reason'] ?? 'No reason provided'),
            'ip_address'  => request()->ip(),
        ]);

        return redirect()->route('tasks.index')->with('success', 'Task cancelled successfully');
    }

    public function search(Request $request)
    {
        return $this->index($request);
    }

    public function filter(Request $request)
    {
        return $this->index($request);
    }

    private function filteredTasksQuery(Request $request)
    {
        $query = $this->visibleTasksQuery();
        $showArchived = $request->boolean('archived');

        if ($showArchived) {
            $query->where(function ($q) {
                $q->where('status', 'Cancelled')
                    ->orWhere(function ($q) {
                        $q->where('status', 'Received')
                            ->where('payment_status', 'Paid');
                    });
            });
        } else {
            $query->where(function ($q) {
                $q->where('status', '!=', 'Received')
                    ->orWhere('payment_status', '!=', 'Paid');
            });
            $query->where('status', '!=', 'Cancelled');
        }

        if ($request->filled('q')) {
            $search = $request->input('q');

            $query->where(function ($q) use ($search) {
                $q->where('task_id', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $allowedStatuses = $showArchived
                ? ['Received', 'Cancelled']
                : ['Pending', 'Designing', 'Printing', 'Installing', 'Completed', 'Received'];

            if (in_array($request->status, $allowedStatuses, true)) {
                $query->where('status', $request->status);
            }
        }

        if ($request->priority) {
            $query->where('priority', $request->priority);
        }

        if ($request->created_date) {
            $query->whereDate('created_at', $request->created_date);
        }

        if (! $showArchived && $request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->due_date_from) {
            $query->whereDate('due_date', '>=', $request->due_date_from);
        }

        if ($request->due_date_to) {
            $query->whereDate('due_date', '<=', $request->due_date_to);
        }

        if (Auth::user()->isAdmin() && $request->assigned_to) {
            $query->where('assigned_to', $request->assigned_to);
        }

        return $query;
    }

    private function visibleTasksQuery()
    {
        return Task::query();
    }

    private function authorizeTaskAccess(Task $task): void
    {
        // Any authenticated user can view and edit transactions.
    }

    private function assignableUsers()
    {
        return User::whereIn('role', ['Admin', 'Staff'])
            ->where('is_active', true)
            ->orderBy('role')
            ->orderBy('name')
            ->get();
    }
}
