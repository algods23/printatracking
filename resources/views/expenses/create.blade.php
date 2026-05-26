@extends('layouts.app')

@section('title', 'Create Disbursement')
@section('page-title', 'Record New Disbursement')
@section('page-subtitle', 'Add a new business disbursement')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Expense Details</h2>
        </div>

        <form action="{{ route('expenses.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Expense Name -->
                <div>
                    <label for="expense_name" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Name *</label>
                    <input type="text" id="expense_name" name="expense_name" value="{{ old('expense_name') }}" required class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 @error('expense_name') border-red-500 @enderror">
                    @error('expense_name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Category -->
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Category *</label>
                    <select id="category" name="category" required class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 @error('category') border-red-500 @enderror">
                        <option value="">Select category</option>
                        <option value="Materials" {{ old('category') == 'Materials' ? 'selected' : '' }}>Materials</option>
                        <option value="Labor" {{ old('category') == 'Labor' ? 'selected' : '' }}>Labor</option>
                        <option value="Utilities" {{ old('category') == 'Utilities' ? 'selected' : '' }}>Utilities</option>
                        <option value="Rent" {{ old('category') == 'Rent' ? 'selected' : '' }}>Rent</option>
                        <option value="Equipment" {{ old('category') == 'Equipment' ? 'selected' : '' }}>Equipment</option>
                        <option value="Transportation" {{ old('category') == 'Transportation' ? 'selected' : '' }}>Transportation</option>
                        <option value="Marketing" {{ old('category') == 'Marketing' ? 'selected' : '' }}>Marketing</option>
                        <option value="Other" {{ old('category') == 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('category')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Other Category (visible when Other is selected) -->
                <div id="otherCategoryWrapper" class="md:col-span-2 {{ old('category') === 'Other' ? '' : 'hidden' }}">
                    <label for="other_category" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Other Category *</label>
                    <input type="text" id="other_category" name="other_category" value="{{ old('other_category') }}" placeholder="Specify category type" class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 @error('other_category') border-red-500 @enderror">
                    @error('other_category')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Amount -->
                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Amount (₱) *</label>
                    <input type="number" id="amount" name="amount" value="{{ old('amount') }}" required step="0.01" min="0" class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 @error('amount') border-red-500 @enderror">
                    @error('amount')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Date -->
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Date *</label>
                    <input type="date" id="date" name="date" value="{{ old('date', today()->toDateString()) }}" required class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 @error('date') border-red-500 @enderror">
                    @error('date')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Description</label>
                <textarea id="description" name="description" rows="4" placeholder="Enter expense details..." class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Receipt Upload -->
            <div>
                <label for="receipt" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Receipt/Invoice</label>
                <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center cursor-pointer hover:border-yellow-500 transition-colors" onclick="document.getElementById('receipt').click()">
                    <i data-lucide="file-text" class="w-12 h-12 text-gray-400 mx-auto mb-3"></i>
                    <p class="text-gray-600 dark:text-gray-400 mb-1">Click to upload or drag and drop</p>
                    <p class="text-sm text-gray-500 dark:text-gray-500">PDF, JPG, PNG up to 5MB</p>
                    <input type="file" id="receipt" name="receipt" accept="image/*,.pdf" class="hidden" onchange="showFileName(event)">
                </div>
                <div id="fileName" class="mt-2 text-sm text-gray-600 dark:text-gray-400"></div>
                @error('receipt')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="receipt_number" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Receipt Number</label>
                <input type="text" id="receipt_number" name="receipt_number" value="{{ old('receipt_number') }}" placeholder="Enter receipt or invoice number" class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 @error('receipt_number') border-red-500 @enderror">
                @error('receipt_number')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Buttons -->
            <div class="flex gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="submit" class="flex-1 px-6 py-2 bg-yellow-500 hover:bg-yellow-600 text-black font-semibold rounded-lg transition-colors flex items-center justify-center gap-2">
                    <i data-lucide="check" class="w-5 h-5"></i>
                    Record Disbursement
                </button>
                <a href="{{ route('expenses.index') }}" class="flex-1 px-6 py-2 bg-gray-300 dark:bg-gray-700 text-gray-900 dark:text-white font-semibold rounded-lg hover:bg-gray-400 dark:hover:bg-gray-600 transition-colors flex items-center justify-center gap-2">
                    <i data-lucide="x" class="w-5 h-5"></i>
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function showFileName(event) {
        const file = event.target.files[0];
        const fileName = document.getElementById('fileName');
        if (file) {
            fileName.textContent = '✓ File selected: ' + file.name;
        }
    }

    function toggleOtherCategory() {
        const category = document.getElementById('category').value;
        const wrapper = document.getElementById('otherCategoryWrapper');
        const input = document.getElementById('other_category');

        if (category === 'Other') {
            wrapper.classList.remove('hidden');
            input.required = true;
        } else {
            wrapper.classList.add('hidden');
            input.required = false;
        }
    }

    document.getElementById('category').addEventListener('change', toggleOtherCategory);
    toggleOtherCategory();

    lucide.createIcons();
</script>
@endsection
