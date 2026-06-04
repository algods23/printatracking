<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Pcv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PcvController extends Controller
{
    private const DEFAULT_CATEGORIES = [
        'Materials',
        'Labor',
        'Utilities',
        'Rent',
        'Equipment',
        'Transportation',
        'Marketing',
        'Other',
    ];

    public function index()
    {
        $pcvs = Pcv::with('recordedBy')->latest()->paginate(15);

        $today = Carbon::today();
        $currentMonth = Carbon::now()->startOfMonth();
        $currentMonthEnd = Carbon::now()->endOfMonth();

        $todayTotal = Pcv::whereDate('date', $today)->sum('amount');
        $thisMonthTotal = Pcv::whereBetween('date', [$currentMonth->toDateString(), $currentMonthEnd->toDateString()])->sum('amount');

        return view('pcv.index', compact('pcvs', 'todayTotal', 'thisMonthTotal'));
    }

    public function create()
    {
        return view('pcv.create', [
            'categories' => $this->categoryOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pcv_name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'other_category' => 'required_if:category,Other|nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date|before_or_equal:today',
            'description' => 'nullable|string',
            'voucher_number' => 'nullable|string|max:255',
            'voucher' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:5120',
        ]);

        $validated = $this->normalizeCategory($validated);

        if ($request->hasFile('voucher')) {
            $validated['voucher_path'] = $request->file('voucher')->store('pcv', 'public');
        }

        if (Schema::hasColumn('pcvs', 'pcv_number')) {
            $validated['pcv_number'] = $this->nextPcvNumber();
        }

        $validated['recorded_by'] = Auth::id();

        $pcv = Pcv::create($validated);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'created',
            'model_type' => Pcv::class,
            'model_id' => $pcv->id,
            'description' => "PCV '{$pcv->pcv_name}' recorded",
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('pcv.index')->with('success', 'PCV recorded successfully');
    }

    public function show(Pcv $pcv)
    {
        return view('pcv.show', compact('pcv'));
    }

    public function edit(Pcv $pcv)
    {
        return view('pcv.edit', [
            'pcv' => $pcv,
            'categories' => $this->categoryOptions(),
        ]);
    }

    public function update(Request $request, Pcv $pcv)
    {
        $validated = $request->validate([
            'pcv_name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'other_category' => 'required_if:category,Other|nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'voucher_number' => 'nullable|string|max:255',
            'voucher' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:5120',
        ]);

        $validated = $this->normalizeCategory($validated);

        if ($request->hasFile('voucher')) {
            if ($pcv->voucher_path) {
                Storage::disk('public')->delete($pcv->voucher_path);
            }

            $validated['voucher_path'] = $request->file('voucher')->store('pcv', 'public');
        }

        $pcv->update($validated);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'updated',
            'model_type' => Pcv::class,
            'model_id' => $pcv->id,
            'description' => "PCV '{$pcv->pcv_name}' updated",
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('pcv.show', $pcv)->with('success', 'PCV updated successfully');
    }

    public function destroy(Request $request, Pcv $pcv)
    {
        $request->validate([
            'admin_password' => ['required', 'current_password'],
        ]);
        if ($pcv->voucher_path) {
            Storage::disk('public')->delete($pcv->voucher_path);
        }

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'deleted',
            'model_type' => Pcv::class,
            'model_id' => $pcv->id,
            'description' => "PCV '{$pcv->pcv_name}' deleted",
            'ip_address' => request()->ip(),
        ]);

        $pcv->delete();

        return redirect()->route('pcv.index')->with('success', 'PCV deleted successfully');
    }

    private function nextPcvNumber(): string
    {
        if (! Schema::hasColumn('pcvs', 'pcv_number')) {
            $nextId = ((int) Pcv::max('id')) + 1;

            return 'PCV # ' . str_pad((string) $nextId, 2, '0', STR_PAD_LEFT);
        }

        $lastNumber = Pcv::whereNotNull('pcv_number')
            ->orderByDesc('id')
            ->value('pcv_number');

        $next = 1;

        if (is_string($lastNumber) && preg_match('/(\d+)$/', $lastNumber, $matches)) {
            $next = ((int) $matches[1]) + 1;
        }

        return 'PCV # ' . str_pad((string) $next, 2, '0', STR_PAD_LEFT);
    }

    private function categoryOptions(): array
    {
        $customCategories = Pcv::where('category', 'Other')
            ->whereNotNull('other_category')
            ->distinct()
            ->orderBy('other_category')
            ->pluck('other_category')
            ->filter()
            ->all();

        return collect(self::DEFAULT_CATEGORIES)
            ->merge($customCategories)
            ->unique()
            ->values()
            ->all();
    }

    private function normalizeCategory(array $validated): array
    {
        if (! in_array($validated['category'], self::DEFAULT_CATEGORIES, true)) {
            $validated['other_category'] = $validated['category'];
            $validated['category'] = 'Other';
        } elseif ($validated['category'] !== 'Other') {
            $validated['other_category'] = null;
        }

        return $validated;
    }
}
