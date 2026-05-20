@extends('layouts.app')

@section('title', 'Settings')
@section('page-title', 'Application Settings')
@section('page-subtitle', 'Manage your application configuration')

@section('content')
<div class="space-y-6">
    <!-- Company Information -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Company Information</h2>
        </div>

        <form action="{{ route('settings.company') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="company_name" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Company Name</label>
                    <input type="text" id="company_name" name="company_name" value="{{ $companyName }}" class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                </div>

                <div>
                    <label for="company_email" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Email Address</label>
                    <input type="email" id="company_email" name="company_email" value="{{ $companyEmail }}" class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                </div>

                <div>
                    <label for="company_phone" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Phone Number</label>
                    <input type="text" id="company_phone" name="company_phone" value="{{ $companyPhone }}" class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                </div>

                <div>
                    <label for="company_address" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Address</label>
                    <input type="text" id="company_address" name="company_address" value="{{ $companyAddress }}" class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                </div>
            </div>

            <div>
                <label for="company_logo" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Company Logo</label>
                <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center cursor-pointer hover:border-yellow-500 transition-colors" onclick="document.getElementById('company_logo').click()">
                    <i data-lucide="image" class="w-12 h-12 text-gray-400 mx-auto mb-3"></i>
                    <p class="text-gray-600 dark:text-gray-400 mb-1">Click to upload or drag and drop</p>
                    <p class="text-sm text-gray-500 dark:text-gray-500">PNG, JPG up to 5MB</p>
                    <input type="file" id="company_logo" name="company_logo" accept="image/*" class="hidden">
                </div>
            </div>

            <div class="flex gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="submit" class="px-6 py-2 bg-yellow-500 hover:bg-yellow-600 text-black font-semibold rounded-lg transition-colors flex items-center gap-2">
                    <i data-lucide="save" class="w-5 h-5"></i>
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    <!-- Receipt Settings -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Receipt Settings</h2>
        </div>

        <form action="{{ route('settings.receipt') }}" method="POST" class="p-6 space-y-6">
            @csrf

            <div>
                <label for="receipt_footer" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Receipt Footer Message</label>
                <textarea id="receipt_footer" name="receipt_footer" rows="3" class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">{{ $receiptFooter }}</textarea>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">This message will appear at the bottom of all receipts</p>
            </div>

            <div class="flex gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="submit" class="px-6 py-2 bg-yellow-500 hover:bg-yellow-600 text-black font-semibold rounded-lg transition-colors flex items-center gap-2">
                    <i data-lucide="save" class="w-5 h-5"></i>
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    <!-- Printer Settings -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Printer Settings</h2>
        </div>

        <form action="{{ route('settings.printer') }}" method="POST" class="p-6 space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="printer_name" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Printer Name</label>
                    <input type="text" id="printer_name" name="printer_name" value="{{ $printerName }}" placeholder="Default Printer" class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                </div>

                <div>
                    <label for="paper_width" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Paper Width</label>
                    <select id="paper_width" name="paper_width" class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                        <option value="80">80mm (Standard)</option>
                        <option value="58">58mm (Compact)</option>
                    </select>
                </div>
            </div>

            <div class="bg-blue-500/10 border border-blue-500 rounded-lg p-4">
                <p class="text-sm text-blue-700 dark:text-blue-400">
                    <strong>Note:</strong> Configure your thermal printer through Windows Devices & Printers before using this application.
                </p>
            </div>

            <div class="flex gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="submit" class="px-6 py-2 bg-yellow-500 hover:bg-yellow-600 text-black font-semibold rounded-lg transition-colors flex items-center gap-2">
                    <i data-lucide="save" class="w-5 h-5"></i>
                    Save Changes
                </button>
                <button type="button" class="px-6 py-2 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-lg transition-colors flex items-center gap-2">
                    <i data-lucide="printer" class="w-5 h-5"></i>
                    Test Print
                </button>
            </div>
        </form>
    </div>

    <!-- Theme Settings -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Theme Settings</h2>
        </div>

        <form action="{{ route('settings.theme') }}" method="POST" class="p-6 space-y-6">
            @csrf

            <div class="flex items-center gap-4">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="dark_mode" value="1" {{ $darkMode === 'true' ? 'checked' : '' }} class="w-4 h-4 accent-yellow-500">
                    <span class="text-gray-900 dark:text-white">Enable Dark Mode</span>
                </label>
            </div>

            <div class="flex gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="submit" class="px-6 py-2 bg-yellow-500 hover:bg-yellow-600 text-black font-semibold rounded-lg transition-colors flex items-center gap-2">
                    <i data-lucide="save" class="w-5 h-5"></i>
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    <!-- Backup & Restore -->
    @if(auth()->user()->isAdmin())
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Database Management</h2>
        </div>

        <div class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <form action="{{ route('settings.backup') }}" method="POST" class="flex gap-4">
                    @csrf
                    <button type="submit" class="flex-1 px-6 py-2 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-lg transition-colors flex items-center justify-center gap-2">
                        <i data-lucide="database" class="w-5 h-5"></i>
                        Backup Database
                    </button>
                </form>

                <form action="{{ route('settings.restore') }}" method="POST" enctype="multipart/form-data" class="flex gap-4">
                    @csrf
                    <input type="file" name="backup_file" accept=".zip,.sql" class="flex-1" style="display: none" id="backupFile">
                    <button type="button" onclick="document.getElementById('backupFile').click()" class="flex-1 px-6 py-2 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-lg transition-colors flex items-center justify-center gap-2">
                        <i data-lucide="upload" class="w-5 h-5"></i>
                        Restore Database
                    </button>
                </form>
            </div>

            <div class="bg-yellow-500/10 border border-yellow-500 rounded-lg p-4">
                <p class="text-sm text-yellow-700 dark:text-yellow-400">
                    <strong>Warning:</strong> Always backup your database before making major changes. Restoring will overwrite current data.
                </p>
            </div>
        </div>
    </div>

    <!-- User Management -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">User Management</h2>
            <a href="{{ route('settings.create-user') }}" class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-black font-semibold text-sm rounded-lg transition-colors flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i>
                New User
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Name</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Email</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Role</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($users ?? [] as $user)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $user->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $user->email }}</td>
                            <td class="px-6 py-4 text-sm">
                                <span class="inline-block px-3 py-1 bg-blue-500/10 text-blue-600 dark:text-blue-400 rounded-full text-xs font-medium">{{ $user->role }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="inline-block px-3 py-1 {{ $user->is_active ? 'bg-green-500/10 text-green-600 dark:text-green-400' : 'bg-red-500/10 text-red-600 dark:text-red-400' }} rounded-full text-xs font-medium">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('settings.edit-user', $user) }}" class="p-2 text-yellow-600 dark:text-yellow-400 hover:bg-yellow-500/10 rounded transition-colors">
                                        <i data-lucide="edit" class="w-4 h-4"></i>
                                    </a>
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('settings.delete-user', $user) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-red-600 dark:text-red-400 hover:bg-red-500/10 rounded transition-colors">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

@endsection

@section('scripts')
<script>
    lucide.createIcons();
</script>
@endsection
