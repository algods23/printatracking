<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Receipt;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::with('assignedTo')->latest()->paginate(15);
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
        $task->load(['assignedTo', 'items', 'receipts.issuedBy']);

        return view('tasks.show', compact('task'));
    }

    public function edit(Task $task)
    {
        $staff = User::where('role', 'Staff')->where('is_active', true)->get();
        return view('tasks.edit', compact('task', 'staff'));
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'customer_name'    => 'required|string|max:255',
            'contact_number'   => 'required|string|max:20',
            'product_type'     => 'required|in:Signage,Sticker,Banner,Label,Other',
            'signage_type'     => 'nullable|in:Digital,Vinyl,Neon,LED,Wooden,Metal,Other',
            'sticker_type'     => 'nullable|in:Vinyl,Paper,Label,Die-cut,Other',
            'assigned_to'      => 'nullable|exists:users,id',
            'due_date'         => 'required|date',
            'due_time'         => 'nullable|date_format:H:i',
            'status'           => 'required|in:Pending,Designing,Printing,Installing,Completed,Cancelled',
            'priority'         => 'required|in:Low,Medium,High,Urgent',
            'notes'            => 'nullable|string',
            'amount'           => 'required|numeric|min:0',
            'payment_status'   => 'required|in:Unpaid,Partial,Paid',
            'payment_amount'   => 'nullable|numeric|min:0',
            'payment_method'   => 'nullable|string',
            'reference_number' => 'nullable|string',
            'attachments'      => 'nullable|array',
            'attachments.*'    => 'file|max:51200',
        ]);

        if ($request->hasFile('attachments')) {
            $attachmentPaths = $task->attachments ?? [];
            foreach ($request->file('attachments') as $file) {
                $attachmentPaths[] = $file->store('tasks/attachments', 'public');
            }
            $validated['attachments'] = $attachmentPaths;
        }

        $task->update($validated);

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
        $tasks = Task::where('task_id', 'like', "%{$query}%")
            ->orWhere('customer_name', 'like', "%{$query}%")
            ->orWhere('contact_number', 'like', "%{$query}%")
            ->with('assignedTo')
            ->latest()
            ->paginate(15);

        return view('tasks.index', compact('tasks', 'query'));
    }

    public function filter(Request $request)
    {
        $query = Task::query();

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->priority) {
            $query->where('priority', $request->priority);
        }

        if ($request->assigned_to) {
            $query->where('assigned_to', $request->assigned_to);
        }

        $tasks = $query->with('assignedTo')->latest()->paginate(15);

        return view('tasks.index', compact('tasks'));
    }
}
