@extends('layouts.app')

@section('title', 'Create Task')
@section('page-title', 'Create New Task')
@section('page-subtitle', 'Add a new printing or signage task')

@section('content')
@php
    $orderItems = old('items', [
        ['job_order' => '', 'quantity' => 1, 'price' => ''],
    ]);
@endphp

<div class="max-w-5xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Task Details</h2>
        </div>

        <form action="{{ route('tasks.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-6 gap-6">
                <!-- Customer Name -->
                <div class="md:col-span-2">
                    <label for="customer_name" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Customer Name *</label>
                    <input type="text" id="customer_name" name="customer_name" value="{{ old('customer_name') }}" required class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 @error('customer_name') border-red-500 @enderror" />
                    @error('customer_name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Contact Number -->
                <div class="md:col-span-2">
                    <label for="contact_number" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Contact Number *</label>
                    <input type="text" id="contact_number" name="contact_number" value="{{ old('contact_number') }}" required class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 @error('contact_number') border-red-500 @enderror" />
                    @error('contact_number')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                @if(auth()->user()->isAdmin())
                <!-- Assign To -->
                <div class="md:col-span-2">
                    <label for="assigned_to" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Assign To</label>
                    <select id="assigned_to" name="assigned_to" class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                        <option value="">Select staff</option>
                        @foreach($staff as $member)
                            <option value="{{ $member->id }}" {{ old('assigned_to') == $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <!-- Due Date -->
                <div class="md:col-span-2">
                    <label for="due_date" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Due Date *</label>
                    <input type="date" id="due_date" name="due_date" value="{{ old('due_date') }}" required
                        min="{{ date('Y-m-d') }}"
                        class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 @error('due_date') border-red-500 @enderror" />
                    @error('due_date')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Due Time -->
                <div class="md:col-span-2">
                    <label for="due_time" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Due Time *</label>
                    <input type="time" id="due_time" name="due_time" value="{{ old('due_time') }}" required class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 @error('due_time') border-red-500 @enderror" />
                    @error('due_time')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Priority -->
                <div class="md:col-span-2">
                    <label for="priority" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Priority *</label>
                    <select id="priority" name="priority" required class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 @error('priority') border-red-500 @enderror">
                        <option value="">Select priority</option>
                        <option value="Low" {{ old('priority') == 'Low' ? 'selected' : '' }}>Low</option>
                        <option value="Urgent" {{ old('priority') == 'Urgent' ? 'selected' : '' }}>Urgent</option>
                    </select>
                    @error('priority')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Job Orders -->
                <div class="md:col-span-6">
                    <div class="flex items-center justify-between mb-3">
                        <label class="block text-sm font-medium text-gray-900 dark:text-white">Job Orders *</label>
                        <button type="button" onclick="addOrderRow()" class="px-3 py-2 bg-yellow-500 hover:bg-yellow-600 text-black font-semibold rounded-lg transition-colors text-sm flex items-center gap-2">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            Add Order
                        </button>
                    </div>

                    <div class="overflow-x-auto border border-gray-300 dark:border-gray-600 rounded-lg">
                        <table class="w-full min-w-[680px] text-sm">
                            <thead class="bg-gray-100 dark:bg-gray-900 text-gray-700 dark:text-gray-300">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold">Job Order</th>
                                    <th class="px-4 py-3 text-left font-semibold w-24">Qty</th>
                                    <th class="px-4 py-3 text-left font-semibold w-36">Price</th>
                                    <th class="px-4 py-3 text-left font-semibold w-36">Total</th>
                                    <th class="px-4 py-3 w-14"></th>
                                </tr>
                            </thead>
                            <tbody id="orderRows" class="divide-y divide-gray-300 dark:divide-gray-700">
                                @foreach($orderItems as $index => $item)
                                    <tr class="order-row bg-white dark:bg-gray-800">
                                        <td class="p-2">
                                            <input type="text" name="items[{{ $index }}][job_order]" value="{{ $item['job_order'] ?? '' }}" required placeholder="e.g. Signage, Sticker, Banner" class="job-order w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                                        </td>
                                        <td class="p-2">
                                            <input type="number" name="items[{{ $index }}][quantity]" value="{{ $item['quantity'] ?? 1 }}" required min="1" class="quantity w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                                        </td>
                                        <td class="p-2">
                                            <input type="number" name="items[{{ $index }}][price]" value="{{ $item['price'] ?? '' }}" required min="0" step="0.01" class="price w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                                        </td>
                                        <td class="p-2">
                                            <div class="row-total px-3 py-2 bg-gray-100 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg font-semibold text-gray-900 dark:text-white">&#8369;0.00</div>
                                        </td>
                                        <td class="p-2 text-center">
                                            <button type="button" onclick="removeOrderRow(this)" class="remove-row p-2 text-red-500 hover:bg-red-500/10 rounded-lg" title="Remove order">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-100 dark:bg-gray-900">
                                <tr>
                                    <td colspan="3" class="px-4 py-3 text-right font-bold text-gray-900 dark:text-white">Total</td>
                                    <td class="px-4 py-3 font-bold text-green-600 dark:text-green-400" id="grandTotal">&#8369;0.00</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @error('items')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    @error('items.*.job_order')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Notes</label>
                <textarea id="notes" name="notes" rows="4" placeholder="Enter any additional notes..." class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 @error('notes') border-red-500 @enderror">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Payment Method + Reference Number (always visible side by side) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Payment Method -->
                <div>
                    <label for="payment_method" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Payment Method (optional)</label>
                    <select id="payment_method" name="payment_method"
                        class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 @error('payment_method') border-red-500 @enderror"
                        onchange="toggleReference(this.value)">
                        <option value="">Select method</option>
                        <option value="Cash"          {{ old('payment_method') == 'Cash'          ? 'selected' : '' }}>Cash</option>
                        <option value="Bank Transfer" {{ old('payment_method') == 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="GCash"         {{ old('payment_method') == 'GCash'         ? 'selected' : '' }}>GCash</option>
                        <option value="Maya"          {{ old('payment_method') == 'Maya'          ? 'selected' : '' }}>Maya</option>
                        <option value="Credit Card"   {{ old('payment_method') == 'Credit Card'   ? 'selected' : '' }}>Credit Card</option>
                        <option value="Other"         {{ old('payment_method') == 'Other'         ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('payment_method')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Payment Amount (optional) -->
                <div class="md:col-span-1">
                    <label for="payment_amount" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Payment Amount (optional)</label>
                    <input type="number" step="0.01" min="0" id="payment_amount" name="payment_amount" value="{{ old('payment_amount') }}" placeholder="Enter amount"
                        class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 @error('payment_amount') border-red-500 @enderror" />
                    @error('payment_amount')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Reference Number (hidden when Cash) -->
                <div id="reference_div" style="display: none;">
                    <label for="reference_number" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Reference Number</label>
                    <input type="text" id="reference_number" name="reference_number" value="{{ old('reference_number') }}"
                        placeholder="Enter transaction / reference number"
                        class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 @error('reference_number') border-red-500 @enderror" />
                    @error('reference_number')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total</p>
                    <p id="summaryTotal" class="text-lg font-semibold text-gray-900 dark:text-white">&#8369;0.00</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Paid</p>
                    <p id="summaryPaid" class="text-lg font-semibold text-green-600 dark:text-green-400">&#8369;0.00</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Balance</p>
                    <p id="summaryBalance" class="text-lg font-semibold text-yellow-600 dark:text-yellow-400">&#8369;0.00</p>
                </div>
            </div>

            <!-- Attachments Upload -->
            <div>
                <label for="attachments" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Upload Files (Images, Layouts, etc.)</label>
                <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center cursor-pointer hover:border-yellow-500 transition-colors" onclick="document.getElementById('attachments').click()">
                    <i data-lucide="upload-cloud" class="w-12 h-12 text-gray-400 mx-auto mb-3"></i>
                    <p class="text-gray-600 dark:text-gray-400 mb-1">Click to upload multiple files or drag and drop</p>
                    <p class="text-sm text-gray-500 dark:text-gray-500">All file types supported, up to 50MB each</p>
                    <input type="file" id="attachments" name="attachments[]" multiple class="hidden" onchange="previewFiles(event)">
                </div>
                <div id="filePreview" class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4"></div>
                @error('attachments')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
                @error('attachments.*')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Buttons -->
            <div class="flex gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="submit" class="flex-1 px-6 py-2 bg-yellow-500 hover:bg-yellow-600 text-black font-semibold rounded-lg transition-colors flex items-center justify-center gap-2">
                    <i data-lucide="check" class="w-5 h-5"></i>
                    Create Task
                </button>
                <a href="{{ route('tasks.index') }}" class="flex-1 px-6 py-2 bg-gray-300 dark:bg-gray-700 text-gray-900 dark:text-white font-semibold rounded-lg hover:bg-gray-400 dark:hover:bg-gray-600 transition-colors flex items-center justify-center gap-2">
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
    // ── Payment method toggle ────────────────────────────────────────────────
    function toggleReference(method) {
        const refDiv = document.getElementById('reference_div');
        refDiv.style.display = (method && method !== 'Cash') ? 'block' : 'none';
    }

    // ── Block past dates on the date picker ─────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        // Set today as min date (YYYY-MM-DD in local time)
        const today = new Date();
        const yyyy  = today.getFullYear();
        const mm    = String(today.getMonth() + 1).padStart(2, '0');
        const dd    = String(today.getDate()).padStart(2, '0');
        document.getElementById('due_date').min = `${yyyy}-${mm}-${dd}`;

        // Restore payment method state after validation error
        toggleReference(document.getElementById('payment_method').value);
    });

    // ── File upload & preview ────────────────────────────────────────────────
    let selectedFiles = new DataTransfer();

    function previewFiles(event) {
        const input = document.getElementById('attachments');
        for (let i = 0; i < event.target.files.length; i++) {
            selectedFiles.items.add(event.target.files[i]);
        }
        input.files = selectedFiles.files;
        renderPreview();
    }

    function removeFile(index) {
        const input = document.getElementById('attachments');
        const dt    = new DataTransfer();
        for (let i = 0; i < input.files.length; i++) {
            if (i !== index) dt.items.add(input.files[i]);
        }
        selectedFiles  = dt;
        input.files    = selectedFiles.files;
        renderPreview();
    }

    function renderPreview() {
        const input   = document.getElementById('attachments');
        const preview = document.getElementById('filePreview');
        preview.innerHTML = '';
        Array.from(input.files).forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.innerHTML += `
                        <div class="relative group border border-gray-200 dark:border-gray-700 rounded-lg p-2 flex items-center justify-center bg-gray-50 dark:bg-gray-800">
                            <img src="${e.target.result}" alt="${file.name}" class="max-h-32 object-contain rounded">
                            <div class="absolute bottom-0 left-0 right-0 bg-black/50 text-white text-xs truncate p-1 rounded-b">${file.name}</div>
                            <button type="button" onclick="removeFile(${index})" class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity z-10 hover:bg-red-600 focus:outline-none">
                                <i data-lucide="x" class="w-3 h-3"></i>
                            </button>
                        </div>`;
                    lucide.createIcons();
                };
                reader.readAsDataURL(file);
            } else {
                preview.innerHTML += `
                    <div class="relative group border border-gray-200 dark:border-gray-700 rounded-lg p-4 flex flex-col items-center justify-center bg-gray-50 dark:bg-gray-800 h-36">
                        <i data-lucide="file" class="w-8 h-8 text-gray-400 mb-2"></i>
                        <span class="text-xs text-center text-gray-600 dark:text-gray-300 w-full truncate" title="${file.name}">${file.name}</span>
                        <span class="text-[10px] text-gray-500 mt-1">${(file.size / 1024 / 1024).toFixed(2)} MB</span>
                        <button type="button" onclick="removeFile(${index})" class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity z-10 hover:bg-red-600 focus:outline-none">
                            <i data-lucide="x" class="w-3 h-3"></i>
                        </button>
                    </div>`;
                lucide.createIcons();
            }
        });
    }

    // ── Order table ──────────────────────────────────────────────────────────
    function formatMoney(value) {
        return `\u20b1${Number(value || 0).toFixed(2)}`;
    }

    function recalculateOrders() {
        let grandTotal = 0;
        document.querySelectorAll('.order-row').forEach((row) => {
            const quantity = Number(row.querySelector('.quantity').value || 0);
            const price    = Number(row.querySelector('.price').value    || 0);
            const total    = quantity * price;
            grandTotal    += total;
            row.querySelector('.row-total').textContent = formatMoney(total);
        });
        document.getElementById('grandTotal').textContent = formatMoney(grandTotal);
        updatePaymentSummary(grandTotal);
        updateRemoveButtons();
    }

    function updatePaymentSummary(grandTotal) {
        const total = grandTotal ?? 0;
        const paid = Number(document.getElementById('payment_amount').value || 0);
        const balance = Math.max(total - paid, 0);

        document.getElementById('summaryTotal').textContent = formatMoney(total);
        document.getElementById('summaryPaid').textContent = formatMoney(paid);
        document.getElementById('summaryBalance').textContent = formatMoney(balance);
    }

    function updateOrderIndexes() {
        document.querySelectorAll('.order-row').forEach((row, index) => {
            row.querySelector('.job-order').name = `items[${index}][job_order]`;
            row.querySelector('.quantity').name  = `items[${index}][quantity]`;
            row.querySelector('.price').name     = `items[${index}][price]`;
        });
    }

    function updateRemoveButtons() {
        const rows = document.querySelectorAll('.order-row');
        rows.forEach((row) => {
            const button = row.querySelector('.remove-row');
            button.disabled = rows.length === 1;
            button.classList.toggle('opacity-40',       rows.length === 1);
            button.classList.toggle('cursor-not-allowed', rows.length === 1);
        });
    }

    function attachOrderListeners(row) {
        row.querySelectorAll('.quantity, .price').forEach((input) => {
            input.addEventListener('input', recalculateOrders);
        });
    }

    function addOrderRow() {
        const tbody  = document.getElementById('orderRows');
        const newRow = tbody.querySelector('.order-row').cloneNode(true);
        newRow.querySelector('.job-order').value  = '';
        newRow.querySelector('.quantity').value   = 1;
        newRow.querySelector('.price').value      = '';
        newRow.querySelector('.row-total').textContent = formatMoney(0);
        tbody.appendChild(newRow);
        updateOrderIndexes();
        attachOrderListeners(newRow);
        recalculateOrders();
        lucide.createIcons();
    }

    function removeOrderRow(button) {
        if (document.querySelectorAll('.order-row').length === 1) return;
        button.closest('.order-row').remove();
        updateOrderIndexes();
        recalculateOrders();
    }

    document.getElementById('payment_amount').addEventListener('input', recalculateOrders);

    document.querySelectorAll('.order-row').forEach(attachOrderListeners);
    recalculateOrders();
    lucide.createIcons();
</script>
@endsection
