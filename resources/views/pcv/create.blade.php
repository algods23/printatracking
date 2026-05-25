@extends('layouts.app')

@section('title', 'Create PCV')
@section('page-title', 'Record New PCV')
@section('page-subtitle', 'Add a new petty cash voucher')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">PCV Details</h2>
        </div>

        <form action="{{ route('pcv.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="pcv_name" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">PCV Name *</label>
                    <input type="text" id="pcv_name" name="pcv_name" value="{{ old('pcv_name') }}" required class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 @error('pcv_name') border-red-500 @enderror">
                    @error('pcv_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

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
                    @error('category')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div id="otherCategoryWrapper" class="{{ old('category') === 'Other' ? '' : 'hidden' }}">
                    <label for="other_category" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Other Category *</label>
                    <input type="text" id="other_category" name="other_category" value="{{ old('other_category') }}" placeholder="Specify category type" class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 @error('other_category') border-red-500 @enderror">
                    @error('other_category')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Amount (₱) *</label>
                    <input type="number" id="amount" name="amount" value="{{ old('amount') }}" required step="0.01" min="0" class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 @error('amount') border-red-500 @enderror">
                    @error('amount')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="date" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Date *</label>
                    <input type="date" id="date" name="date" value="{{ old('date', today()->toDateString()) }}" required class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 @error('date') border-red-500 @enderror">
                    @error('date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Description</label>
                <textarea id="description" name="description" rows="4" placeholder="Enter PCV details..." class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                @error('description')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="voucher_number" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Voucher Number</label>
                <input type="text" id="voucher_number" name="voucher_number" value="{{ old('voucher_number') }}" placeholder="Enter voucher number" class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 @error('voucher_number') border-red-500 @enderror">
                @error('voucher_number')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="voucher" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Voucher Attachment</label>
                <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center cursor-pointer hover:border-yellow-500 transition-colors" onclick="document.getElementById('voucher').click()">
                    <i data-lucide="file-text" class="w-12 h-12 text-gray-400 mx-auto mb-3"></i>
                    <p class="text-gray-600 dark:text-gray-400 mb-1">Click to upload or drag and drop</p>
                    <p class="text-sm text-gray-500 dark:text-gray-500">PDF, JPG, PNG up to 5MB</p>
                    <input type="file" id="voucher" name="voucher" accept="image/*,.pdf" class="hidden" onchange="showFileName(event)">
                </div>
                <div id="fileName" class="mt-2 text-sm text-gray-600 dark:text-gray-400"></div>
                @error('voucher')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="flex gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="submit" class="flex-1 px-6 py-2 bg-yellow-500 hover:bg-yellow-600 text-black font-semibold rounded-lg transition-colors flex items-center justify-center gap-2">
                    <i data-lucide="check" class="w-5 h-5"></i>
                    Record PCV
                </button>
                <a href="{{ route('pcv.index') }}" class="flex-1 px-6 py-2 bg-gray-300 dark:bg-gray-700 text-gray-900 dark:text-white font-semibold rounded-lg hover:bg-gray-400 dark:hover:bg-gray-600 transition-colors flex items-center justify-center gap-2">
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
        if (file) fileName.textContent = '✓ File selected: ' + file.name;
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