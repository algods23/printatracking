@extends('layouts.app')

@section('title', 'PCV Details')
@section('page-title', 'PCV Details')
@section('page-subtitle', 'Review a petty cash voucher')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $pcv->pcv_name }}</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Recorded on {{ $pcv->date->format('M d, Y') }}</p>
        </div>
        <a href="{{ route('pcv.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white font-semibold rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
            Back to PCV
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">PCV Information</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">
            <div><p class="text-sm text-gray-500 dark:text-gray-400">Category</p><p class="text-base font-medium text-gray-900 dark:text-white">{{ $pcv->category === 'Other' && $pcv->other_category ? $pcv->other_category : $pcv->category }}</p></div>
            <div><p class="text-sm text-gray-500 dark:text-gray-400">Amount</p><p class="text-base font-medium text-gray-900 dark:text-white">₱{{ number_format($pcv->amount, 2) }}</p></div>
            <div><p class="text-sm text-gray-500 dark:text-gray-400">Date</p><p class="text-base font-medium text-gray-900 dark:text-white">{{ $pcv->date->format('M d, Y') }}</p></div>
            <div><p class="text-sm text-gray-500 dark:text-gray-400">Recorded By</p><p class="text-base font-medium text-gray-900 dark:text-white">{{ $pcv->recordedBy->name }}</p></div>
            <div><p class="text-sm text-gray-500 dark:text-gray-400">Voucher Number</p><p class="text-base font-medium text-gray-900 dark:text-white">{{ $pcv->voucher_number ?? 'N/A' }}</p></div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Voucher File</p>
                @if($pcv->voucher_path)
                    <a href="{{ asset('storage/' . $pcv->voucher_path) }}" target="_blank" class="text-yellow-500 hover:text-yellow-600 font-medium">View Voucher</a>
                @else
                    <p class="text-base font-medium text-gray-900 dark:text-white">None</p>
                @endif
            </div>
            <div class="md:col-span-2">
                <p class="text-sm text-gray-500 dark:text-gray-400">Description</p>
                <p class="text-base font-medium text-gray-900 dark:text-white whitespace-pre-line">{{ $pcv->description ?: 'No description provided.' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection