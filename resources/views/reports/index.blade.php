@extends('layouts.app')

@section('title', 'Reports')
@section('page-title', 'Generate reports')
@section('page-subtitle', 'Select a date range and report types to generate')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <form method="GET" action="{{ route('reports.index') }}" id="reportForm" class="space-y-6">
        <input type="hidden" name="generate" value="1">

        <!-- Date range -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xs font-bold tracking-wider text-gray-500 dark:text-gray-400 uppercase">Date range</h2>
            </div>
            <div class="p-5 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="start_date" class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">From</label>
                        <div class="relative">
                            <input type="date" id="start_date" name="start_date" value="{{ $startDate }}" required
                                class="w-full px-4 py-2.5 pr-10 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 text-gray-900 dark:text-white">
                        </div>
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">To</label>
                        <div class="relative">
                            <input type="date" id="end_date" name="end_date" value="{{ $endDate }}" required
                                class="w-full px-4 py-2.5 pr-10 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 text-gray-900 dark:text-white">
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" data-preset="week" class="date-preset px-4 py-1.5 text-sm rounded-full border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">This week</button>
                    <button type="button" data-preset="month" class="date-preset px-4 py-1.5 text-sm rounded-full border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">This month</button>
                    <button type="button" data-preset="last_month" class="date-preset px-4 py-1.5 text-sm rounded-full border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">Last month</button>
                    <button type="button" data-preset="year" class="date-preset px-4 py-1.5 text-sm rounded-full border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">This year</button>
                </div>
            </div>
        </div>

        <!-- Report types -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h2 class="text-xs font-bold tracking-wider text-gray-500 dark:text-gray-400 uppercase">Report types</h2>
                <button type="button" id="selectAllBtn" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-yellow-600 dark:hover:text-yellow-400 transition-colors">
                    Select all
                </button>
            </div>
            <div class="p-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($reportTypes as $value => $label)
                        @php
                            $meta = $reportTypeMeta[$value];
                            $checked = in_array($value, $selectedTypes, true);
                            $wide = $meta['wide'] ?? false;
                        @endphp
                        <label class="report-type-card flex items-center gap-3 px-4 py-3.5 rounded-xl border-2 cursor-pointer transition-colors
                            {{ $wide ? 'sm:col-span-2' : '' }}
                            {{ $checked ? 'border-yellow-500 bg-yellow-500/5' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500' }}">
                            <input type="checkbox" name="types[]" value="{{ $value }}" class="report-type-checkbox w-4 h-4 rounded border-gray-400 text-yellow-500 focus:ring-yellow-500" {{ $checked ? 'checked' : '' }}>
                            <i data-lucide="{{ $meta['icon'] }}" class="w-5 h-5 text-gray-600 dark:text-gray-400 shrink-0"></i>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @error('types')
                    <p class="text-red-500 text-xs mt-3">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <button type="submit" class="w-full px-6 py-3 bg-yellow-500 hover:bg-yellow-600 text-black font-semibold rounded-xl transition-colors flex items-center justify-center gap-2">
            <i data-lucide="file-bar-chart" class="w-5 h-5"></i>
            Generate reports
        </button>
    </form>

    @if(!empty($reports))
        <div class="space-y-6 pt-2">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <div class="text-center sm:text-left">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ count($reports) }} {{ Str::plural('report', count($reports)) }} generated
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} — {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                    </p>
                </div>
                <a href="{{ route('reports.export-excel', ['types' => array_keys($reports), 'start_date' => $startDate, 'end_date' => $endDate]) }}"
                    class="w-full sm:w-auto px-5 py-2.5 bg-green-700 hover:bg-green-600 text-white font-semibold rounded-xl transition-colors flex items-center justify-center gap-2 shrink-0">
                    <i data-lucide="file-spreadsheet" class="w-5 h-5"></i>
                    Export all to Excel
                </a>
            </div>
            @foreach($reports as $type => $report)
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 flex flex-wrap items-center justify-between gap-3">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $reportTypes[$type] }}</h2>
                        <a href="{{ route('reports.export-excel', ['type' => $type, 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="px-3 py-1.5 text-sm bg-green-700 hover:bg-green-600 text-white font-medium rounded-lg transition-colors flex items-center gap-2">
                            <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                            Export Excel
                        </a>
                    </div>
                    <div class="p-6">
                        @include('reports.partials.' . $report['type'], array_merge($report, ['startDate' => $startDate, 'endDate' => $endDate]))
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    function formatDate(d) {
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${y}-${m}-${day}`;
    }

    function applyPreset(preset) {
        const today = new Date();
        const start = document.getElementById('start_date');
        const end = document.getElementById('end_date');
        end.value = formatDate(today);

        if (preset === 'week') {
            const d = new Date(today);
            const day = d.getDay();
            const diff = day === 0 ? 6 : day - 1;
            d.setDate(d.getDate() - diff);
            start.value = formatDate(d);
        } else if (preset === 'month') {
            start.value = formatDate(new Date(today.getFullYear(), today.getMonth(), 1));
        } else if (preset === 'last_month') {
            const first = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            const last = new Date(today.getFullYear(), today.getMonth(), 0);
            start.value = formatDate(first);
            end.value = formatDate(last);
        } else if (preset === 'year') {
            start.value = formatDate(new Date(today.getFullYear(), 0, 1));
        }
    }

    document.querySelectorAll('.date-preset').forEach((btn) => {
        btn.addEventListener('click', () => applyPreset(btn.dataset.preset));
    });

    const checkboxes = () => Array.from(document.querySelectorAll('.report-type-checkbox'));
    const cards = () => Array.from(document.querySelectorAll('.report-type-card'));

    function syncCardStyles() {
        cards().forEach((card) => {
            const cb = card.querySelector('.report-type-checkbox');
            const on = cb.checked;
            card.classList.toggle('border-yellow-500', on);
            card.classList.toggle('bg-yellow-500/5', on);
            card.classList.toggle('border-gray-200', !on);
            card.classList.toggle('dark:border-gray-600', !on);
        });
    }

    function updateSelectAllLabel() {
        const all = checkboxes();
        const btn = document.getElementById('selectAllBtn');
        const allChecked = all.length > 0 && all.every((cb) => cb.checked);
        btn.textContent = allChecked ? 'Deselect all' : 'Select all';
    }

    document.getElementById('selectAllBtn').addEventListener('click', () => {
        const all = checkboxes();
        const allChecked = all.every((cb) => cb.checked);
        all.forEach((cb) => { cb.checked = !allChecked; });
        syncCardStyles();
        updateSelectAllLabel();
    });

    checkboxes().forEach((cb) => {
        cb.addEventListener('change', () => {
            syncCardStyles();
            updateSelectAllLabel();
        });
    });

    cards().forEach((card) => {
        card.addEventListener('click', (e) => {
            if (e.target.tagName === 'INPUT') return;
            const cb = card.querySelector('.report-type-checkbox');
            cb.checked = !cb.checked;
            syncCardStyles();
            updateSelectAllLabel();
        });
    });

    syncCardStyles();
    updateSelectAllLabel();
    lucide.createIcons();
</script>
@endsection
