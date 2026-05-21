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

    <!-- Change Password -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Change Password</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Update the password for your account ({{ auth()->user()->email }})</p>
        </div>

        <form action="{{ route('settings.password') }}" method="POST" class="p-6 space-y-5">
            @csrf

            <div>
                <label for="current_password" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Current password</label>
                <input type="password" id="current_password" name="current_password" required autocomplete="current-password"
                    class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 text-gray-900 dark:text-white @error('current_password') border-red-500 @enderror">
                @error('current_password')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">New password</label>
                    <input type="password" id="password" name="password" required autocomplete="new-password" minlength="8"
                        class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 text-gray-900 dark:text-white @error('password') border-red-500 @enderror">
                    @error('password')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">At least 8 characters</p>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Confirm new password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password" minlength="8"
                        class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 text-gray-900 dark:text-white">
                </div>
            </div>

            <div class="flex gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="submit" class="px-6 py-2 bg-yellow-500 hover:bg-yellow-600 text-black font-semibold rounded-lg transition-colors flex items-center gap-2">
                    <i data-lucide="key-round" class="w-5 h-5"></i>
                    Update Password
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

                <form action="{{ route('settings.restore') }}" method="POST" enctype="multipart/form-data" id="restoreForm" class="flex gap-4">
                    @csrf
                    <input type="file" name="backup_file" accept=".sqlite,.db" class="hidden" id="backupFile" onchange="if(this.files.length) this.form.submit()">
                    <button type="button" onclick="document.getElementById('backupFile').click()" class="flex-1 px-6 py-2 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-lg transition-colors flex items-center justify-center gap-2">
                        <i data-lucide="upload" class="w-5 h-5"></i>
                        Restore Database
                    </button>
                </form>
            </div>

            <p class="text-sm text-gray-500 dark:text-gray-400">
                <strong>Backup</strong> downloads a <code class="text-xs bg-gray-100 dark:bg-gray-700 px-1 rounded">.sqlite</code> file to your computer and also keeps a copy in <code class="text-xs bg-gray-100 dark:bg-gray-700 px-1 rounded">storage/app/backups/</code> on the server.
            </p>

            <div class="bg-yellow-500/10 border border-yellow-500 rounded-lg p-4">
                <p class="text-sm text-yellow-700 dark:text-yellow-400">
                    <strong>Warning:</strong> Always backup your database before making major changes. Restoring will overwrite current data.
                </p>
            </div>
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
