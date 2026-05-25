<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = Expense::with('recordedBy')->latest()->paginate(15);
        
        // Calculate daily and monthly totals
        $today = Carbon::today();
        $currentMonth = Carbon::now()->startOfMonth();
        $currentMonthEnd = Carbon::now()->endOfMonth();
        
        $todayTotal = Expense::whereDate('date', $today)->sum('amount');

        $thisMonthTotal = Expense::whereBetween('date', [$currentMonth->toDateString(), $currentMonthEnd->toDateString()])->sum('amount');
        $materialsTotal = Expense::whereBetween('date', [$currentMonth->toDateString(), $currentMonthEnd->toDateString()])
            ->where('category', 'Materials')
            ->sum('amount');
        $otherTotal = Expense::whereBetween('date', [$currentMonth->toDateString(), $currentMonthEnd->toDateString()])
            ->where('category', '!=', 'Materials')
            ->sum('amount');
        
        return view('expenses.index', compact('expenses', 'todayTotal', 'thisMonthTotal', 'materialsTotal', 'otherTotal'));
    }

    public function create()
    {
        return view('expenses.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'expense_name' => 'required|string|max:255',
            'category' => 'required|in:Materials,Labor,Utilities,Rent,Equipment,Transportation,Marketing,Other',
            'other_category' => 'required_if:category,Other|nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date|before_or_equal:today',
            'description' => 'nullable|string',
            'receipt_number' => 'nullable|string|max:255',
            'receipt' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:5120',
        ]);

        if ($validated['category'] !== 'Other') {
            $validated['other_category'] = null;
        }

        if ($request->hasFile('receipt')) {
            $validated['receipt_path'] = $request->file('receipt')->store('expenses', 'public');
        }

        $validated['recorded_by'] = Auth::id();

        $expense = Expense::create($validated);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'created',
            'model_type' => Expense::class,
            'model_id' => $expense->id,
            'description' => "Expense '{$expense->expense_name}' recorded",
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('expenses.index')->with('success', 'Expense recorded successfully');
    }

    public function show(Expense $expense)
    {
        return view('expenses.show', compact('expense'));
    }

    public function edit(Expense $expense)
    {
        return view('expenses.edit', compact('expense'));
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'expense_name' => 'required|string|max:255',
            'category' => 'required|in:Materials,Labor,Utilities,Rent,Equipment,Transportation,Marketing,Other',
            'other_category' => 'required_if:category,Other|nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'receipt_number' => 'nullable|string|max:255',
            'receipt' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:5120',
        ]);

        if ($validated['category'] !== 'Other') {
            $validated['other_category'] = null;
        }

        if ($request->hasFile('receipt')) {
            if ($expense->receipt_path) {
                Storage::disk('public')->delete($expense->receipt_path);
            }
            $validated['receipt_path'] = $request->file('receipt')->store('expenses', 'public');
        }

        $expense->update($validated);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'updated',
            'model_type' => Expense::class,
            'model_id' => $expense->id,
            'description' => "Expense '{$expense->expense_name}' updated",
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('expenses.show', $expense)->with('success', 'Expense updated successfully');
    }

    public function destroy(Expense $expense)
    {
        if ($expense->receipt_path) {
            Storage::disk('public')->delete($expense->receipt_path);
        }

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'deleted',
            'model_type' => Expense::class,
            'model_id' => $expense->id,
            'description' => "Expense '{$expense->expense_name}' deleted",
            'ip_address' => request()->ip(),
        ]);

        $expense->delete();

        return redirect()->route('expenses.index')->with('success', 'Expense deleted successfully');
    }

    public function report(Request $request)
    {
        $query = Expense::query();

        if ($request->start_date) {
            $query->whereDate('date', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        if ($request->category) {
            $query->where('category', $request->category);
        }

        $expenses = $query->with('recordedBy')->get();

        $totalAmount = $expenses->sum('amount');
        $categoryBreakdown = $expenses->groupBy('category')->map(fn($group) => $group->sum('amount'));

        return view('expenses.report', compact('expenses', 'totalAmount', 'categoryBreakdown'));
    }
}
