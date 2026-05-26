@extends('layouts.app')

@section('title', 'PCV')
@section('page-title', 'Petty Cash Voucher')
@section('page-subtitle', 'Track and manage petty cash vouchers separately from expenses')

@section('content')
<div class="mb-6 flex items-center justify-between gap-4">
    <div class="flex-1">
        <div class="relative">
            <input type="text" placeholder="Search PCV records..." class="w-full px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
            <i data-lucide="search" class="absolute right-3 top-2.5 w-5 h-5 text-gray-400"></i>
        </div>
    </div>
    <a href="{{ route('pcv.create') }}" class="px-6 py-2 bg-yellow-500 hover:bg-yellow-600 text-black font-semibold rounded-lg transition-colors flex items-center gap-2">
        <i data-lucide="plus" class="w-5 h-5"></i>
        New PCV
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
        <h3 class="text-gray-600 dark:text-gray-400 font-semibold text-sm mb-2">Today PCV</h3>
        <p class="text-2xl font-bold text-gray-900 dark:text-white">₱{{ number_format($todayTotal, 2) }}</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
        <h3 class="text-gray-600 dark:text-gray-400 font-semibold text-sm mb-2">This Month</h3>
        <p class="text-2xl font-bold text-gray-900 dark:text-white">₱{{ number_format($thisMonthTotal, 2) }}</p>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">PCV #</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Name</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Category</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Amount</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Date</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Recorded By</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($pcvs as $pcv)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $pcv->display_pcv_number }}</td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $pcv->pcv_name }}</td>
                        <td class="px-6 py-4 text-sm">
                            <span class="inline-block px-3 py-1 bg-blue-500/10 text-blue-600 dark:text-blue-400 rounded-full text-xs font-medium">{{ $pcv->category === 'Other' && $pcv->other_category ? $pcv->other_category : $pcv->category }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900 dark:text-gray-100">₱{{ number_format($pcv->amount, 2) }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $pcv->date->format('M d, Y') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $pcv->recordedBy->name }}</td>
                        <td class="px-6 py-4 text-sm">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('pcv.show', $pcv) }}" class="p-2 text-blue-600 dark:text-blue-400 hover:bg-blue-500/10 rounded transition-colors">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                                <a href="{{ route('pcv.edit', $pcv) }}" class="p-2 text-yellow-600 dark:text-yellow-400 hover:bg-yellow-500/10 rounded transition-colors">
                                    <i data-lucide="edit" class="w-4 h-4"></i>
                                </a>
                                <form action="{{ route('pcv.destroy', $pcv) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-red-600 dark:text-red-400 hover:bg-red-500/10 rounded transition-colors">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <i data-lucide="inbox" class="w-12 h-12 text-gray-400"></i>
                                <p class="text-gray-500 dark:text-gray-400">No PCV records found</p>
                                <a href="{{ route('pcv.create') }}" class="text-yellow-500 hover:text-yellow-600 text-sm font-semibold">Record a PCV</a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6">
    {{ $pcvs->links() }}
</div>
@endsection

@section('scripts')
<script>lucide.createIcons();</script>
@endsection