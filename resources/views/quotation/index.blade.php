@extends('layouts.app')

@section('title', 'Billing')
@section('page-title', 'Billing')
@section('page-subtitle', 'Search customers with unpaid or partially paid transactions')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <form method="GET" action="{{ route('billing.index') }}" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5 flex flex-col md:flex-row gap-3 items-stretch md:items-end">
        <div class="flex-1">
            <label for="q" class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Search customer</label>
            <input type="text" id="q" name="q" value="{{ request('q') }}" placeholder="Search by customer name, billing ID, or phone..." class="w-full px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 text-gray-900 dark:text-white">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="px-5 py-2.5 bg-yellow-500 hover:bg-yellow-600 text-black font-semibold rounded-lg transition-colors">Search</button>
            <a href="{{ route('billing.index') }}" class="px-5 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white font-semibold rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">Clear</a>
            <button id="downloadBtn" type="button" onclick="submitSelectedDownload(); return false;" class="px-5 py-2.5 bg-green-700 hover:bg-green-600 text-white font-semibold rounded-lg transition-colors">Download File</button>
        </div>
    </form>

    <form id="downloadSelectedForm" action="{{ route('billing.download') }}" method="GET" class="hidden"></form>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">₱{{ number_format($totalAmount, 2) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700">
            <p class="text-sm text-gray-500 dark:text-gray-400">Deposit</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400">₱{{ number_format($totalDeposit, 2) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700">
            <p class="text-sm text-gray-500 dark:text-gray-400">Balance</p>
            <p class="text-2xl font-bold text-red-600 dark:text-red-400">₱{{ number_format($totalBalance, 2) }}</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                        <th class="px-6 py-3 text-left">
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" id="selectAll" checked onclick="toggleAll(this)" class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-yellow-500 focus:ring-yellow-500 bg-white dark:bg-gray-700">
                                <span class="text-xs font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider whitespace-nowrap">Select All</span>
                            </label>
                        </th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Date</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Job Order #</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Customer</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Total</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Deposit</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Balance</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($quotations as $task)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <td class="px-6 py-4">
                                <input type="checkbox" value="{{ $task->id }}" checked onclick="syncCheckboxes()" class="row-checkbox w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-yellow-500 focus:ring-yellow-500 bg-white dark:bg-gray-700">
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $task->created_at->format('M d, Y') }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $task->task_id }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                <div>{{ $task->customer_name }}</div>
                                <div class="text-xs text-gray-500">{{ $task->contact_number }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900 dark:text-white">₱{{ number_format($task->amount, 2) }}</td>
                            <td class="px-6 py-4 text-sm font-semibold text-green-600 dark:text-green-400">₱{{ number_format($task->paid_amount ?? 0, 2) }}</td>
                            <td class="px-6 py-4 text-sm font-semibold text-red-600 dark:text-red-400">₱{{ number_format($task->balance, 2) }}</td>
                            <td class="px-6 py-4 text-sm">
                                @switch($task->payment_status)
                                    @case('Unpaid')
                                        <span class="inline-block px-3 py-1 bg-red-500/10 text-red-600 dark:text-red-400 rounded-full text-xs font-medium">Unpaid</span>
                                    @break
                                    @case('Partial')
                                        <span class="inline-block px-3 py-1 bg-yellow-500/10 text-yellow-600 dark:text-yellow-400 rounded-full text-xs font-medium">Partial</span>
                                    @break
                                    @default
                                        <span class="inline-block px-3 py-1 bg-gray-500/10 text-gray-600 dark:text-gray-400 rounded-full text-xs font-medium">{{ $task->payment_status }}</span>
                                @endswitch
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('tasks.show', $task) }}" class="p-2 text-blue-600 dark:text-blue-400 hover:bg-blue-500/10 rounded transition-colors inline-flex" title="View task">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                    <a href="{{ route('billing.download', ['customer' => $task->customer_name]) }}" class="p-2 text-green-700 dark:text-green-400 hover:bg-green-500/10 rounded transition-colors inline-flex" title="Download customer billing PDF">
                                        <i data-lucide="download" class="w-4 h-4"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <i data-lucide="inbox" class="w-12 h-12 text-gray-400"></i>
                                    <p class="text-gray-500 dark:text-gray-400">No billing data found</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $quotations->links() }}
    </div>
</div>
@endsection

@section('scripts')
<script>
    lucide.createIcons();

    function updateDownloadBtn() {
        var downloadBtn = document.getElementById('downloadBtn');
        var checked = document.querySelectorAll('.row-checkbox:checked');
        if (checked.length > 0) {
            downloadBtn.textContent = 'Download Selected (' + checked.length + ')';
        } else {
            downloadBtn.textContent = 'Download File';
        }
    }

    function submitSelectedDownload() {
        var checked = document.querySelectorAll('.row-checkbox:checked');
        if (!checked.length) {
            return;
        }

        var form = document.getElementById('downloadSelectedForm');
        form.innerHTML = '';

        checked.forEach(function(cb) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = cb.value;
            form.appendChild(input);
        });

        var searchInput = document.getElementById('q');
        if (searchInput && searchInput.value.trim() !== '') {
            var queryInput = document.createElement('input');
            queryInput.type = 'hidden';
            queryInput.name = 'q';
            queryInput.value = searchInput.value.trim();
            form.appendChild(queryInput);
        }

        form.submit();
    }

    function toggleAll(source) {
        var checkboxes = document.querySelectorAll('.row-checkbox');
        for (var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].checked = source.checked;
        }
        updateDownloadBtn();
    }

    function syncCheckboxes() {
        var all = document.querySelectorAll('.row-checkbox');
        var checked = document.querySelectorAll('.row-checkbox:checked');
        var selectAll = document.getElementById('selectAll');
        selectAll.checked = (all.length === checked.length);
        selectAll.indeterminate = (checked.length > 0 && checked.length < all.length);
        updateDownloadBtn();
    }

    // Set initial button state on load
    updateDownloadBtn();
</script>
@endsection