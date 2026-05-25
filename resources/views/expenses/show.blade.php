@extends('layouts.app')

@section('title', 'Expense Details')
@section('page-title', 'Expense Details')
@section('page-subtitle', 'Review a recorded expense')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $expense->expense_name }}</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Recorded on {{ $expense->date->format('M d, Y') }}</p>
        </div>
        <a href="{{ route('expenses.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white font-semibold rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
            Back to Expenses
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Expense Information</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Category</p>
                <p class="text-base font-medium text-gray-900 dark:text-white">{{ $expense->category === 'Other' && $expense->other_category ? $expense->other_category : $expense->category }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Amount</p>
                <p class="text-base font-medium text-gray-900 dark:text-white">₱{{ number_format($expense->amount, 2) }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Date</p>
                <p class="text-base font-medium text-gray-900 dark:text-white">{{ $expense->date->format('M d, Y') }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Recorded By</p>
                <p class="text-base font-medium text-gray-900 dark:text-white">{{ $expense->recordedBy->name }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Receipt Number</p>
                <p class="text-base font-medium text-gray-900 dark:text-white">{{ $expense->receipt_number ?? 'N/A' }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Receipt File</p>
                @if($expense->receipt_path)
                    <a href="{{ asset('storage/' . $expense->receipt_path) }}" target="_blank" class="text-yellow-500 hover:text-yellow-600 font-medium">View Receipt</a>
                @else
                    <p class="text-base font-medium text-gray-900 dark:text-white">None</p>
                @endif
            </div>

            <div class="md:col-span-2">
                <p class="text-sm text-gray-500 dark:text-gray-400">Description</p>
                <p class="text-base font-medium text-gray-900 dark:text-white whitespace-pre-line">{{ $expense->description ?: 'No description provided.' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
