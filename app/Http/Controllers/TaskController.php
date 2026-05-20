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
    public function index()
    {
        $tasks = $this->visibleTasksQuery()
            ->with('assignedTo')
            ->latest()
            ->paginate(15);

        return view('tasks.index', compact('tasks'));
    }

    public function create()
    {
        $staff = User::where('role', 'Staff')->where('is_active', true)->get();
        return view('tasks.create', compact('staff'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name'    => 'required|string|max:255',
            'contact_number'   => 'required|string|max:20',
            'product_type'     => 'nullable|in:Signage,Sticker,Banner,Label,Other',
            'signage_type'     => 'nullable|in:Digital,Vinyl,Neon,LED,Wooden,Metal,Other',
            'sticker_type'     => 'nullable|in:Vinyl,Paper,Label,Die-cut,Other',
            'assigned_to'      => 'nullable|exists:users,id',
            'due_date'         => 'required|date|after:today',
            'due_time'         => 'nullable|date_format:H:i',
            'priority'         => 'required|in:Low,Medium,High,Urgent',
            'notes'            => 'nullable|string',
            'payment_amount'   => 'nullable|numeric|min:0',
            'payment_method'   => 'required|in:Cash,Bank Transfer,GCash,Maya,Credit Card,Other',
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
        $staff = User::where('role', 'Staff')->where('is_active', true)->get();

        return view('tasks.edit', compact('task', 'staff'));
    }

    public function update(Request $request, Task $task)
    {
        $this->authorizeTaskAccess($task);

        $validated = $request->validate([
            'customer_name'     => 'required|string|max:255',
            'contact_number'    => 'required|string|max:20',
            'assigned_to'       => 'nullable|exists:users,id',
            'due_date'          => 'required|date',
            'due_time'          => 'nullable|date_format:H:i',
            'status'            => 'required|in:Pending,Designing,Printing,Installing,Completed,Cancelled',
            'priority'          => 'required|in:Low,Medium,High,Urgent',
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

    public function destroy(Task $task)
    {
        $this->authorizeTaskAccess($task);

        if ($task->image_path) {
            Storage::disk('public')->delete($task->image_path);
        }
        if (!empty($task->attachments)) {
            foreach ($task->attachments as $attachment) {
                Storage::disk('public')->delete($attachment);
            }
        }

        ActivityLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'deleted',
            'model_type'  => Task::class,
            'model_id'    => $task->id,
            'description' => "Task {$task->task_id} deleted",
            'ip_address'  => request()->ip(),
        ]);

        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully');
    }

    public function search(Request $request)
    {
        $query = $request->input('q');
        $tasks = $this->visibleTasksQuery()
            ->where(function ($q) use ($query) {
                $q->where('task_id', 'like', "%{$query}%")
                    ->orWhere('customer_name', 'like', "%{$query}%")
                    ->orWhere('contact_number', 'like', "%{$query}%");
            })
            ->with('assignedTo')
            ->latest()
            ->paginate(15);

        return view('tasks.index', compact('tasks', 'query'));
    }

    public function filter(Request $request)
    {
        $query = $this->visibleTasksQuery();

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->priority) {
            $query->where('priority', $request->priority);
        }

        if (Auth::user()->isAdmin() && $request->assigned_to) {
            $query->where('assigned_to', $request->assigned_to);
        }

        $tasks = $query->with('assignedTo')->latest()->paginate(15);

        return view('tasks.index', compact('tasks'));
    }

    private function visibleTasksQuery()
    {
        $query = Task::query();

        if (! Auth::user()->isAdmin()) {
            $query->where('assigned_to', Auth::id());
        }

        return $query;
    }

    private function authorizeTaskAccess(Task $task): void
    {
        if (! Auth::user()->isAdmin() && (int) $task->assigned_to !== (int) Auth::id()) {
            abort(403, 'You can only access tasks assigned to your account.');
        }
    }
}
